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

        $results = $query->orderBy('id', 'asc')->paginate(50);

        return view('sodc.opname_results.index', compact('results', 'activeSessionId', 'activeSessions'));
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
                continue;
            }

            // Pisahkan berdasarkan sequence
            // Sequence 1 = Hitungan awal (bisa Team A & Team B jika double mode)
            $seq1 = $counts->where('count_sequence', 1)->values();
            $seq2 = $counts->where('count_sequence', 2)->values(); // Recount 1
            $seq3 = $counts->where('count_sequence', 3)->values(); // Recount 2

            $teamA = $seq1->get(0);
            $teamB = $seq1->get(1); // null jika mode SINGLE atau tim B belum hitung
            $r1 = $seq2->first();
            $r2 = $seq3->first();

            $systemQty = (float) $ref->system_qty;
            $aQty = $teamA ? (float) $teamA->count_qty : null;
            $bQty = $teamB ? (float) $teamB->count_qty : null;
            $r1Qty = $r1 ? (float) $r1->count_qty : null;
            $r2Qty = $r2 ? (float) $r2->count_qty : null;

            $status = 'UNCOUNTED';
            $finalQty = 0;

            // Logika A vs B (Double Mode)
            if ($session->mode == 'DOUBLE') {
                if ($aQty !== null && $bQty !== null) {
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
                    'final_qty' => in_array($status, ['MATCH', 'FINAL', 'APPROVED']) ? $finalQty : null,
                    'variance_qty' => $varianceQty,
                    'variance_percentage' => $variancePct,
                    'result_status' => $status
                ]
            );
        }
    }

    public function inputRecount(Request $request)
    {
        $request->validate([
            'result_id' => 'required|exists:opname_results,id',
            'recount_qty' => 'required|numeric',
            'level' => 'required|in:1,2'
        ]);

        $result = OpnameResult::findOrFail($request->result_id);
        
        // Simpan manual ke opname_counts atas nama auditor/admin
        OpnameCount::create([
            'count_uuid' => Str::uuid(),
            'session_id' => $result->session_id,
            'reference_detail_id' => $result->reference_detail_id,
            'count_qty' => $request->recount_qty,
            'count_sequence' => $request->level == 1 ? 2 : 3,
            'count_status' => 'SUBMITTED',
            'counted_by' => auth()->id(),
            'client_created_at' => now(),
            'counted_at' => now()
        ]);

        return redirect()->back()->with('success', 'Hasil Recount berhasil diinput!');
    }
}
