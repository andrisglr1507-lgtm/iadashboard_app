<?php

namespace App\Http\Controllers\Sodc\Result;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpnameSession;
use App\Models\OpnameCount;
use App\Models\OpnameResult;
use App\Models\OpnameReferenceDetail;
use Illuminate\Support\Str;

class CountResultController extends Controller
{
    public function index(Request $request)
    {
        // 1. Dapatkan active session
        if ($request->has('session_id') && $request->session_id != '') {
            session(['active_opname_session_id' => $request->session_id]);
        }
        
        $activeSessionId = session('active_opname_session_id');
        $activeSessions = OpnameSession::where('status', 'ACTIVE')->orderBy('id', 'desc')->get();
        
        if (!$activeSessionId && $activeSessions->count() > 0) {
            $activeSessionId = $activeSessions->first()->id;
            session(['active_opname_session_id' => $activeSessionId]);
        }

        if (!$activeSessionId) {
            return view('sodc.opname_results.index', [
                'error' => 'Belum ada sesi opname yang aktif.',
                'activeSessions' => $activeSessions,
                'activeSessionId' => null
            ]);
        }

        // 2. Jalankan Auto-Rekonsiliasi (Sinkronisasi dari opname_counts ke opname_results)
        $this->runReconciliation($activeSessionId);

        // 3. Ambil data opname_results untuk di tampilkan
        $query = OpnameResult::with(['referenceDetail.product', 'referenceDetail.bin'])
                ->where('session_id', $activeSessionId);

        if ($request->has('status') && $request->status != '') {
            if ($request->status == 'RECOUNT_1') {
                $query->where('result_status', 'RECOUNT')->whereNull('recount1_qty');
            } elseif ($request->status == 'RECOUNT_2') {
                $query->where('result_status', 'RECOUNT')->whereNotNull('recount1_qty')->whereNull('recount2_qty');
            } else {
                $query->where('result_status', $request->status);
            }
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('referenceDetail', function($q) use ($search) {
                $q->where('sku_code', 'like', "%{$search}%")
                  ->orWhere('bin_code', 'like', "%{$search}%")
                  ->orWhereHas('product', function($q2) use ($search) {
                      $q2->where('sku_name', 'like', "%{$search}%");
                  });
            });
        }

        $hasR3 = \Illuminate\Support\Facades\DB::table('opname_recount_assignments')
            ->where('session_id', $activeSessionId)
            ->where('round_number', 3)
            ->exists();
            
        // Cek apakah SEMUA penghitungan sudah selesai (MATCH, FINAL, APPROVED)
        // Jika ada yang masih UNCOUNTED, WAITING, RECOUNT, maka isAllFinal = false
        $isAllFinal = !OpnameResult::where('session_id', $activeSessionId)
            ->whereNotIn('result_status', ['MATCH', 'FINAL', 'APPROVED'])
            ->exists();

        $results = $query->orderBy('id', 'asc')->paginate(50);

        return view('sodc.opname_results.index', compact('results', 'activeSessionId', 'activeSessions', 'hasR3', 'isAllFinal'));
    }

    private function runReconciliation($sessionId)
    {
        $session = OpnameSession::find($sessionId);
        if (!$session) return;

        // Ambil semua reference details yang masuk dalam sesi ini
        $refDetails = OpnameReferenceDetail::where('reference_id', $session->reference_id)->get();
        
        // Ambil semua counts yang sudah di submit untuk sesi ini
        $allCounts = OpnameCount::where('session_id', $sessionId)
            ->whereIn('count_status', ['SUBMITTED', 'LOCKED'])
            ->orderBy('count_sequence')
            ->orderBy('id')
            ->get()
            ->groupBy('reference_detail_id');

        foreach ($refDetails as $ref) {
            $counts = $allCounts->get($ref->id, collect());
            
            // Jika belum ada yang menghitung sama sekali, skip atau update UNCOUNTED
            if ($counts->isEmpty()) {
                // Fix: Bersihkan data yang nyangkut (jika sebelumnya pernah dihitung lalu dihapus)
                $existingResult = OpnameResult::where('session_id', $sessionId)
                    ->where('reference_detail_id', $ref->id)
                    ->first();
                    
                if ($existingResult && $existingResult->result_status != 'UNCOUNTED') {
                    if ((float)$ref->system_qty == 0) {
                        // Barang nyasar -> Delete karena tidak ada yang hitung lagi
                        $existingResult->delete();
                        $ref->delete();
                    } else {
                        // Barang WMS -> Reset jadi UNCOUNTED
                        $existingResult->update([
                            'team_a_qty' => null,
                            'team_b_qty' => null,
                            'recount1_qty' => null,
                            'recount2_qty' => null,
                            'final_qty' => null,
                            'result_status' => 'UNCOUNTED',
                            'variance_qty' => null,
                            'variance_percentage' => null,
                        ]);
                    }
                }
                continue;
            }

            // Pisahkan berdasarkan sequence
            // Sequence 1 = Hitungan awal (bisa Team A & Team B jika double mode)
            $seq1 = $counts->where('count_sequence', 1)->values();
            $seq2 = $counts->where('count_sequence', 2)->values(); // Recount 1
            $seq3 = $counts->where('count_sequence', 3)->values(); // Recount 2
            $seq4 = $counts->where('count_sequence', 4)->values(); // Recount 3

            $teamA = $seq1->get(0);
            $teamB = $seq1->get(1); // null jika mode SINGLE atau tim B belum hitung
            $r1 = $seq2->first();
            $r2 = $seq3->first();
            $r3 = $seq4->first();

            $systemQty = (float) $ref->system_qty;
            $aQty = $teamA ? (float) $teamA->count_qty : null;
            $bQty = $teamB ? (float) $teamB->count_qty : null;
            $r1Qty = $r1 ? (float) $r1->count_qty : null;
            $r2Qty = $r2 ? (float) $r2->count_qty : null;
            $r3Qty = $r3 ? (float) $r3->count_qty : null;

            $status = 'UNCOUNTED';
            $finalQty = 0;

            $hasPendingR3 = \Illuminate\Support\Facades\DB::table('opname_recount_assignments')
                ->where('session_id', $sessionId)
                ->where('location_code', $ref->bin_code)
                ->where('id_product', $ref->sku_code)
                ->where('round_number', 3)
                ->where('status', 'PENDING')
                ->exists();

            // Logika A vs B (Double Mode)
            if ($session->mode == 'DOUBLE') {
                if ($r3Qty !== null) {
                    $status = 'FINAL';
                    $finalQty = $r3Qty;
                } else if ($hasPendingR3) {
                    $status = 'RECOUNT';
                } else if ($aQty !== null && $bQty !== null) {
                    if ($aQty == $bQty) {
                        $status = 'MATCH';
                        $finalQty = $aQty;
                    } else {
                        // Tidak sama -> Butuh Recount 1
                        $status = 'RECOUNT';
                        
                        // Cek apakah R1 sudah masuk
                        if ($r1Qty !== null) {
                            if ($r1Qty == $aQty || $r1Qty == $bQty || $r1Qty == $systemQty) {
                                $status = 'FINAL'; // FINAL R1
                                $finalQty = $r1Qty;
                            } else {
                                // R1 beda dari ketiganya -> Butuh Recount 2
                                $status = 'RECOUNT'; 
                                
                                // Cek apakah R2 sudah masuk
                                if ($r2Qty !== null) {
                                    $status = 'FINAL'; // FINAL R2
                                    $finalQty = $r2Qty;
                                }
                            }
                        }
                    }
                } else if ($aQty !== null && $bQty === null) {
                    $status = 'WAITING B'; // Menunggu tim B
                } else if ($aQty === null && $bQty !== null) {
                    $status = 'WAITING A'; // Menunggu tim A
                }
            } else if ($session->mode == 'RECORD_ONLY') {
                // Mode RECORD ONLY
                if ($aQty !== null) {
                    $status = 'FINAL';
                    $finalQty = $aQty;
                }
            } else {
                // Mode SINGLE
                if ($aQty !== null) {
                    if ($aQty == $systemQty) {
                        $status = 'MATCH';
                        $finalQty = $aQty;
                    } else {
                        $status = 'RECOUNT';
                        if ($r1Qty !== null) {
                            $status = 'FINAL';
                            $finalQty = $r1Qty;
                        }
                    }
                }
            }

            // Hitung Variance
            $varianceQty = 0;
            $variancePct = 0;
            if (in_array($status, ['MATCH', 'FINAL', 'APPROVED'])) {
                $varianceQty = $finalQty - $systemQty;
                if ($systemQty > 0) {
                    $variancePct = ($varianceQty / $systemQty) * 100;
                } else {
                    $variancePct = $varianceQty > 0 ? 100 : 0;
                }
            }

            // Simpan ke opname_results
            OpnameResult::updateOrCreate(
                [
                    'session_id' => $sessionId,
                    'reference_detail_id' => $ref->id
                ],
                [
                    'result_uuid' => Str::uuid(),
                    'system_qty' => $systemQty,
                    'team_a_qty' => $aQty,
                    'team_b_qty' => $bQty,
                    'recount1_qty' => $r1Qty,
                    'recount2_qty' => $r2Qty,
                    'recount3_qty' => $r3Qty,
                    'final_qty' => in_array($status, ['MATCH', 'FINAL', 'APPROVED']) ? $finalQty : null,
                    'variance_qty' => $varianceQty,
                    'variance_percentage' => $variancePct,
                    'result_status' => $status
                ]
            );
        }
    }

    /**
     * Run reconciliation for a SINGLE bin reference detail (Called by API on submit)
     */
    public static function syncSingleBin($sessionId, $refDetailId)
    {
        $session = OpnameSession::find($sessionId);
        if (!$session) return;

        $ref = OpnameReferenceDetail::find($refDetailId);
        if (!$ref || $ref->reference_id != $session->reference_id) return;

        $counts = OpnameCount::where('session_id', $sessionId)
            ->where('reference_detail_id', $refDetailId)
            ->whereIn('count_status', ['SUBMITTED', 'LOCKED'])
            ->orderBy('count_sequence')
            ->orderBy('id')
            ->get();

        if ($counts->isEmpty()) {
            $existingResult = OpnameResult::where('session_id', $sessionId)
                ->where('reference_detail_id', $refDetailId)
                ->first();
            if ($existingResult && $existingResult->result_status != 'UNCOUNTED') {
                if ((float)$ref->system_qty == 0) {
                    $existingResult->delete();
                    $ref->delete();
                } else {
                    $existingResult->update([
                        'team_a_qty' => null, 'team_b_qty' => null,
                        'recount1_qty' => null, 'recount2_qty' => null,
                        'final_qty' => null, 'result_status' => 'UNCOUNTED',
                        'variance_qty' => null, 'variance_percentage' => null,
                    ]);
                }
            }
            return;
        }

        $seq1 = $counts->where('count_sequence', 1)->values();
        $seq2 = $counts->where('count_sequence', 2)->values();
        $seq3 = $counts->where('count_sequence', 3)->values();
        $seq4 = $counts->where('count_sequence', 4)->values();

        $teamA = $seq1->get(0);
        $teamB = $seq1->get(1);
        $r1 = $seq2->first();
        $r2 = $seq3->first();
        $r3 = $seq4->first();

        $systemQty = (float) $ref->system_qty;
        $aQty = $teamA ? (float) $teamA->count_qty : null;
        $bQty = $teamB ? (float) $teamB->count_qty : null;
        $r1Qty = $r1 ? (float) $r1->count_qty : null;
        $r2Qty = $r2 ? (float) $r2->count_qty : null;
        $r3Qty = $r3 ? (float) $r3->count_qty : null;

        $status = 'UNCOUNTED';
        $finalQty = 0;

        $hasPendingR3 = \Illuminate\Support\Facades\DB::table('opname_recount_assignments')
            ->where('session_id', $sessionId)
            ->where('location_code', $ref->bin_code)
            ->where('id_product', $ref->sku_code)
            ->where('round_number', 3)
            ->where('status', 'PENDING')
            ->exists();

        if ($session->mode == 'DOUBLE') {
            if ($r3Qty !== null) {
                $status = 'FINAL'; $finalQty = $r3Qty;
            } else if ($hasPendingR3) {
                $status = 'RECOUNT';
            } else if ($aQty !== null && $bQty !== null) {
                if ($aQty == $bQty) {
                    $status = 'MATCH'; $finalQty = $aQty;
                } else {
                    $status = 'RECOUNT';
                    if ($r1Qty !== null) {
                        if ($r1Qty == $aQty || $r1Qty == $bQty || $r1Qty == $systemQty) {
                            $status = 'FINAL'; $finalQty = $r1Qty;
                        } else {
                            $status = 'RECOUNT';
                            if ($r2Qty !== null) {
                                $status = 'FINAL'; $finalQty = $r2Qty;
                            }
                        }
                    }
                }
            } else if ($aQty !== null && $bQty === null) {
                $status = 'WAITING B';
            } else if ($aQty === null && $bQty !== null) {
                $status = 'WAITING A';
            }
        } else if ($session->mode == 'SINGLE') {
            if ($aQty !== null) {
                if ($aQty == $systemQty) {
                    $status = 'MATCH'; $finalQty = $aQty;
                } else {
                    $status = 'RECOUNT';
                    if ($r1Qty !== null) {
                        $status = 'FINAL'; $finalQty = $r1Qty;
                    }
                }
            }
        } else if ($session->mode == 'RECORD_ONLY') {
            if ($aQty !== null) {
                $status = 'FINAL'; $finalQty = $aQty;
            }
        }

        $varianceQty = 0; $variancePct = 0;
        if (in_array($status, ['MATCH', 'FINAL', 'APPROVED'])) {
            $varianceQty = $finalQty - $systemQty;
            if ($systemQty > 0) {
                $variancePct = ($varianceQty / $systemQty) * 100;
            } else {
                $variancePct = $varianceQty > 0 ? 100 : 0;
            }
        }

        OpnameResult::updateOrCreate(
            ['session_id' => $sessionId, 'reference_detail_id' => $refDetailId],
            [
                'result_uuid' => Str::uuid(),
                'system_qty' => $systemQty,
                'team_a_qty' => $aQty, 'team_b_qty' => $bQty,
                'recount1_qty' => $r1Qty, 'recount2_qty' => $r2Qty, 'recount3_qty' => $r3Qty,
                'final_qty' => in_array($status, ['MATCH', 'FINAL', 'APPROVED']) ? $finalQty : null,
                'variance_qty' => $varianceQty, 'variance_percentage' => $variancePct,
                'result_status' => $status
            ]
        );
    }

    // --- AUTO DISPATCH RECOUNT LOGIC ---
    public function syncResults(Request $request)
    {
        $activeSessionId = session('active_opname_session_id');
        if (!$activeSessionId) return redirect()->back()->with('error', 'Pilih Sesi Opname terlebih dahulu!');

        $this->runReconciliation($activeSessionId);

        // Auto Dispatch Recount Task
        $sessionId = $activeSessionId;
        $teamRecount = \App\Models\OpnameUserArea::where('session_id', $sessionId)
            ->where('team_role', 'TEAM_RECOUNT')
            ->pluck('user_id')
            ->toArray();

        if (!empty($teamRecount)) {
            // Ambil semua hasil rekonsiliasi yang butuh RECOUNT
            $recountItems = OpnameResult::with('referenceDetail')
                ->where('session_id', $sessionId)
                ->where('result_status', 'RECOUNT')
                ->get();
                
            // Ambil assignment yang sudah ada agar tidak duplikat
            $existingAssignments = \App\Models\OpnameRecountAssignment::where('session_id', $sessionId)->get();

            $insertData = [];
            $now = now();

            foreach ($recountItems as $res) {
                $ref = $res->referenceDetail;
                if (!$ref) continue;
                
                // Tentukan level Recount
                $round = 1;
                if ($res->recount1_qty !== null && $res->recount2_qty === null) {
                    $round = 2;
                } elseif ($res->recount1_qty !== null && $res->recount2_qty !== null) {
                    continue; // Skip, tidak butuh dispatch lagi
                }

                // Cek apakah tugas ini sudah pernah di-dispatch (untuk round ini)
                $alreadyAssigned = $existingAssignments
                    ->where('location_code', $ref->bin_code)
                    ->where('id_product', $ref->sku_code)
                    ->where('round_number', $round)
                    ->isNotEmpty();

                if (!$alreadyAssigned) {
                    foreach ($teamRecount as $userId) {
                        $insertData[] = [
                            'session_id' => $sessionId,
                            'location_code' => $ref->bin_code,
                            'id_product' => $ref->sku_code,
                            'round_number' => $round,
                            'assigned_to' => $userId,
                            'assigned_by' => auth()->id() ?? 1, // Sistem otomatis
                            'status' => 'PENDING',
                            'assigned_at' => $now,
                            'is_final' => false
                        ];
                    }
                }
            }

            // Lakukan Insert massal jika ada tugas baru
            if (!empty($insertData)) {
                foreach (array_chunk($insertData, 500) as $chunk) {
                    \App\Models\OpnameRecountAssignment::insert($chunk);
                }
            }
        }
    }

    public function inputRecount(Request $request)
    {
        $request->validate([
            'result_id' => 'required|exists:opname_results,id',
            'recount_qty' => 'required|numeric',
            'level' => 'required|in:1,2,3'
        ]);

        $result = OpnameResult::findOrFail($request->result_id);
        
        // Simpan manual ke opname_counts atas nama auditor/admin
        OpnameCount::create([
            'count_uuid' => Str::uuid(),
            'session_id' => $result->session_id,
            'reference_detail_id' => $result->reference_detail_id,
            'count_qty' => $request->recount_qty,
            'count_sequence' => $request->level == 1 ? 2 : ($request->level == 2 ? 3 : 4), // 4 untuk R3
            'count_status' => 'SUBMITTED',
            'counted_by' => auth()->id(),
            'client_created_at' => now(),
            'counted_at' => now()
        ]);

        return redirect()->back()->with('success', 'Hasil Recount berhasil diinput!');
    }
    
    public function assignR3(Request $request)
    {
        $request->validate([
            'selected_results' => 'required|array',
            'r3_assigned_to' => 'required'
        ]);
        
        $activeSessionId = session('active_opname_session_id');
        $userId = auth()->id();
        $assignedCount = 0;
        
        foreach ($request->selected_results as $resultId) {
            $result = OpnameResult::with('referenceDetail')->find($resultId);
            if ($result && $result->referenceDetail) {
                // Hapus assignment R3 sebelumnya untuk SKU ini jika ada
                \Illuminate\Support\Facades\DB::table('opname_recount_assignments')
                    ->where('session_id', $activeSessionId)
                    ->where('location_code', $result->referenceDetail->bin_code)
                    ->where('id_product', $result->referenceDetail->sku_code)
                    ->where('round_number', 3)
                    ->delete();
                    
                \Illuminate\Support\Facades\DB::table('opname_recount_assignments')->insert([
                    'session_id' => $activeSessionId,
                    'location_code' => $result->referenceDetail->bin_code,
                    'id_product' => $result->referenceDetail->sku_code, // Sesuai dengan kolom SKU kita
                    'round_number' => 3, // Kode khusus R3
                    'assigned_to' => $request->r3_assigned_to,
                    'assigned_by' => $userId ?? 1,
                    'status' => 'PENDING',
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Update result status to RECOUNT so it shows as awaiting R3 audit
                $result->update([
                    'result_status' => 'RECOUNT'
                ]);

                $assignedCount++;
            }
        }
        
        return redirect()->back()->with('success', "Berhasil memberikan tugas R3 (Final Audit) untuk {$assignedCount} item (SKU).");
    }

    public function exportSimple(Request $request)
    {
        $sessionId = session('active_opname_session_id');
        if (!$sessionId) return redirect()->back()->with('error', 'Tidak ada sesi aktif.');
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\Sodc\SimpleOpnameExport($sessionId), 'Opname_Simple_'.date('YmdHis').'.xlsx');
    }

    public function exportComplex(Request $request)
    {
        $sessionId = session('active_opname_session_id');
        if (!$sessionId) return redirect()->back()->with('error', 'Tidak ada sesi aktif.');
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\Sodc\ComplexOpnameExport($sessionId), 'Opname_Complex_'.date('YmdHis').'.xlsx');
    }

    /**
     * Tampilkan detail jejak audit hasil opname (Halaman Penuh)
     */
    public function show($id)
    {
        $result = OpnameResult::with([
            'referenceDetail.product',
            'referenceDetail.bin',
            'session'
        ])->findOrFail($id);

        $counts = OpnameCount::with('user')
            ->where('session_id', $result->session_id)
            ->where('reference_detail_id', $result->reference_detail_id)
            ->whereIn('count_status', ['SUBMITTED', 'LOCKED'])
            ->orderBy('count_sequence', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $recountAssignments = [];
        if ($result->referenceDetail) {
            $recountAssignments = \Illuminate\Support\Facades\DB::table('opname_recount_assignments')
                ->leftJoin('users as u_to', 'opname_recount_assignments.assigned_to', '=', 'u_to.id')
                ->leftJoin('users as u_by', 'opname_recount_assignments.assigned_by', '=', 'u_by.id')
                ->where('session_id', $result->session_id)
                ->where('location_code', $result->referenceDetail->bin_code)
                ->where('id_product', $result->referenceDetail->sku_code)
                ->select(
                    'opname_recount_assignments.*',
                    'u_to.full_name as assigned_to_name',
                    'u_to.username as assigned_to_username',
                    'u_by.full_name as assigned_by_name'
                )
                ->orderBy('round_number', 'asc')
                ->get();
        }

        return view('sodc.opname_results.show', compact('result', 'counts', 'recountAssignments'));
    }

    /**
     * Ambil data detail jejak audit hasil opname untuk Modal Interaktif (JSON)
     */
    public function detailJson($id)
    {
        $result = OpnameResult::with([
            'referenceDetail.product',
            'referenceDetail.bin',
            'session'
        ])->findOrFail($id);

        $counts = OpnameCount::with('user')
            ->where('session_id', $result->session_id)
            ->where('reference_detail_id', $result->reference_detail_id)
            ->whereIn('count_status', ['SUBMITTED', 'LOCKED'])
            ->orderBy('count_sequence', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $recountAssignments = [];
        if ($result->referenceDetail) {
            $recountAssignments = \Illuminate\Support\Facades\DB::table('opname_recount_assignments')
                ->leftJoin('users as u_to', 'opname_recount_assignments.assigned_to', '=', 'u_to.id')
                ->leftJoin('users as u_by', 'opname_recount_assignments.assigned_by', '=', 'u_by.id')
                ->where('session_id', $result->session_id)
                ->where('location_code', $result->referenceDetail->bin_code)
                ->where('id_product', $result->referenceDetail->sku_code)
                ->select(
                    'opname_recount_assignments.*',
                    'u_to.full_name as assigned_to_name',
                    'u_to.username as assigned_to_username',
                    'u_by.full_name as assigned_by_name'
                )
                ->orderBy('round_number', 'asc')
                ->get();
        }

        $formattedCounts = $counts->map(function($c) {
            return [
                'id' => $c->id,
                'team_id' => $c->team_id,
                'count_sequence' => $c->count_sequence,
                'count_qty' => (float)$c->count_qty,
                'input_karton' => (int)($c->input_karton ?? 0),
                'input_pcs' => (int)($c->input_pcs ?? 0),
                'counted_at' => $c->counted_at ? date('d M Y H:i:s', strtotime($c->counted_at)) : ($c->created_at ? date('d M Y H:i:s', strtotime($c->created_at)) : '-'),
                'counted_by_id' => $c->counted_by,
                'counted_by_name' => $c->user->full_name ?? ($c->user->username ?? 'User #' . $c->counted_by),
                'counted_by_role' => $c->user->role ?? '-',
                'count_status' => $c->count_status,
            ];
        });

        $product = $result->referenceDetail->product ?? null;

        return response()->json([
            'success' => true,
            'data' => [
                'result_id' => $result->id,
                'session_code' => $result->session->session_code ?? '-',
                'session_mode' => $result->session->mode ?? '-',
                'bin_code' => $result->referenceDetail->bin_code ?? '-',
                'sku_code' => $result->referenceDetail->sku_code ?? '-',
                'product_name' => $product->sku_name ?? 'Unknown Product',
                'product_category_code' => $product->product_category_code ?? '-',
                'packname' => $product->packname ?? '-',
                'uom' => $product->uom ?? 1,
                'barcode' => $product->barcode ?? '-',
                'carton_code' => $product->carton_code ?? '-',
                'principal' => $product->principal ?? '-',
                'company_code' => $product->company_code ?? '-',
                'system_qty' => (float)$result->system_qty,
                'team_a_qty' => is_null($result->team_a_qty) ? null : (float)$result->team_a_qty,
                'team_b_qty' => is_null($result->team_b_qty) ? null : (float)$result->team_b_qty,
                'recount1_qty' => is_null($result->recount1_qty) ? null : (float)$result->recount1_qty,
                'recount2_qty' => is_null($result->recount2_qty) ? null : (float)$result->recount2_qty,
                'recount3_qty' => is_null($result->recount3_qty) ? null : (float)$result->recount3_qty,
                'final_qty' => is_null($result->final_qty) ? null : (float)$result->final_qty,
                'variance_qty' => (float)$result->variance_qty,
                'variance_percentage' => (float)$result->variance_percentage,
                'result_status' => $result->result_status,
                'counts' => $formattedCounts,
                'recount_assignments' => $recountAssignments,
                'show_url' => route('sodc.results.show', $result->id)
            ]
        ]);
    }
}
