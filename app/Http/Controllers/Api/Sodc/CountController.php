<?php

namespace App\Http\Controllers\Api\Sodc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpnameSession;
use App\Models\OpnameCount;
use App\Models\OpnameReferenceDetail;
use App\Models\Product;
use App\Models\Bin;
use App\Models\OpnameTeamMember;
use Illuminate\Support\Str;

class CountController extends Controller
{
    /**
     * Submit counts for a specific bin from Flutter App.
     */
    public function submitCount(Request $request)
    {
        $request->validate([
            'bin_code' => 'required|string',
            'id_product' => 'required|string',
            'final_qty' => 'required|numeric|min:0',
            'qty_karton' => 'nullable|numeric|min:0',
            'qty_pcs' => 'nullable|numeric|min:0',
            'reference_detail_id' => 'nullable|integer'
        ]);

        $user = $request->user();
        
        // 1. Dapatkan Sesi Aktif dari ID atau fallback
        $sessionId = $request->input('session_id') ?? $request->header('X-Session-Id');
        
        $session = null;
        if ($sessionId) {
            $session = OpnameSession::find($sessionId);
        } else {
            $session = OpnameSession::where('status', 'ACTIVE')->first();
        }
        
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Tidak ada sesi opname aktif.'], 400);
        }
        
        if ($session->status === 'CLOSED') {
            return response()->json(['success' => false, 'message' => 'Session ini sudah close.'], 400);
        }

        // Find Team Role for this Bin (from UserArea)
        $binCode = $request->bin_code;
        $bin = Bin::where('bin_code', $binCode)->first();
        
        $teamId = null;
        if ($bin) {
            // "Free-for-all" mode: Just get the user's team role for this session
            $area = \App\Models\OpnameUserArea::where('session_id', $session->id)
                ->where('user_id', $user->id)
                ->first();
                
            if ($area && $area->team_role) {
                $teamId = $area->team_role;
            } else {
                // BLOCKER: Reject submission if user is not assigned to any team
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Anda belum diberikan tugas (Assignment) pada Sesi Opname ini. Silakan hubungi Admin untuk mendapatkan tim.'
                ], 403);
            }
            
            // Override with RECOUNT team if it is a recount task
            if (\Illuminate\Support\Facades\Schema::hasTable('opname_recount_assignments')) {
                $recount = \Illuminate\Support\Facades\DB::table('opname_recount_assignments')
                    ->where('session_id', $session->id)
                    ->where('assigned_to', $user->id)
                    ->where('location_code', $bin->bin_code)
                    ->whereIn('status', ['PENDING', 'ASSIGNED', 'IN_PROGRESS'])
                    ->orderBy('round_number', 'desc')
                    ->first();
                if ($recount) {
                    $teamId = 'RECOUNT';
                }
            }
        }

        $binCode = $request->bin_code;
        $bin = Bin::where('bin_code', $binCode)->first();

        // 🔹 CEK KONFLIK (OFFLINE-FIRST CONFLICT RESOLUTION)
        // Cek apakah Bin ini sudah pernah dihitung oleh anggota tim lain (untuk mode reguler)
        if ($teamId !== 'RECOUNT') {
            $hasConflict = OpnameCount::join('opname_reference_details', 'opname_counts.reference_detail_id', '=', 'opname_reference_details.id')
                ->where('opname_counts.session_id', $session->id)
                ->where('opname_counts.team_id', $teamId)
                ->where('opname_reference_details.bin_code', $binCode)
                ->where('opname_counts.counted_by', '!=', $user->id)
                ->exists();

            if ($hasConflict) {
                return response()->json([
                    'success' => false,
                    'message' => "Bin $binCode sudah diselesaikan oleh rekan tim Anda."
                ], 409); // HTTP 409 Conflict
            }
        }

        $idProduct = $request->id_product;

        // 🔹 SABUK PENGAMAN KHUSUS RECOUNT
        // Pastikan tugas recount-nya masih ada (belum keduluan dihapus oleh submit teman lain)
        $currentRecountAssignment = null;
        if ($teamId === 'RECOUNT') {
            $currentRecountAssignment = \Illuminate\Support\Facades\DB::table('opname_recount_assignments')
                ->where('session_id', $session->id)
                ->where('location_code', $binCode)
                ->where('id_product', $idProduct)
                ->where('assigned_to', $user->id)
                ->whereIn('status', ['PENDING', 'ASSIGNED', 'IN_PROGRESS'])
                ->orderBy('round_number', 'desc')
                ->first();

            if (!$currentRecountAssignment) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'CONFLICT_ALREADY_DONE',
                    'message' => 'Maaf, tugas untuk Bin/SKU ini baru saja diselesaikan oleh petugas lain!'
                ], 409);
            }
        }

        $refDetailId = $request->reference_detail_id ?? null;
        $idProduct = $request->id_product;
        $qty = $request->final_qty;
        $qtyKarton = $request->qty_karton;
        $qtyPcs = $request->qty_pcs;

        // AD-HOC HANDLING: Jika tidak ada reference_detail_id (Barang Nyasar / Sapu Lorong)
        if (!$refDetailId) {
            $product = Product::where('sku_code', $idProduct)->first();
            if ($product) {
                // Cek apakah sudah terlanjur dibuatkan reference detail sebelumnya (oleh user/tim lain)
                $existingRef = OpnameReferenceDetail::where('reference_id', $session->reference_id)
                    ->where('bin_code', $binCode)
                    ->where('sku_code', $idProduct)
                    ->first();

                if ($existingRef) {
                    $refDetailId = $existingRef->id;
                } else {
                    try {
                        // Buat Referensi WMS Palsu/Nol untuk memfasilitasi barang nyasar
                        $newRef = OpnameReferenceDetail::create([
                            'reference_id' => $session->reference_id,
                            'warehouse_id' => $bin ? $bin->warehouse_id : null,
                            'bin_id' => $bin ? $bin->id : null,
                            'product_id' => $product->id,
                            'sku_code' => $product->sku_code,
                            'bin_code' => $binCode,
                            'system_qty' => 0, // KUNCI UTAMA: Sistem menganggap 0
                            'is_ad_hoc' => true,
                            'uom' => $product->uom,
                            'stock_status' => 'AVAILABLE',
                        ]);
                        $refDetailId = $newRef->id;
                        
                        // BUAT RESULT UNTUK BARANG NYASAR AGAR MUNCUL DI DASHBOARD
                        \App\Models\OpnameResult::create([
                            'result_uuid' => Str::uuid(),
                            'session_id' => $session->id,
                            'reference_detail_id' => $refDetailId,
                            'system_qty' => 0,
                            'team_a_qty' => null,
                            'team_b_qty' => null,
                            'recount1_qty' => null,
                            'recount2_qty' => null,
                            'final_qty' => null,
                            'variance_qty' => null,
                            'variance_percentage' => null,
                            'result_status' => 'UNCOUNTED',
                        ]);
                    } catch (\Exception $e) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal membuat referensi barang baru: ' . $e->getMessage()
                        ], 500);
                    }
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk dengan SKU ' . $idProduct . ' tidak ditemukan di server.'
                ], 404);
            }
        }

        if ($refDetailId) {
            // Tentukan count_sequence berdasarkan apakah ini recount atau reguler
            $targetSequence = 1;
            if ($teamId === 'RECOUNT' && isset($recount)) {
                if ($recount->round_number == 1) {
                    $targetSequence = 2;
                } else if ($recount->round_number == 2) {
                    $targetSequence = 3;
                } else if ($recount->round_number == 3) {
                    $targetSequence = 4;
                }
            }

            // Simpan Hitungan ke opname_counts
            // Cek apakah tim ini sudah submit untuk ref ini sebelumnya (Mencegah double insert jika mereka resubmit)
            $existingCount = OpnameCount::where('session_id', $session->id)
                ->where('team_id', $teamId)
                ->where('reference_detail_id', $refDetailId)
                ->where('count_sequence', $targetSequence) // Dinamis sesuai round
                ->first();

            if ($existingCount) {
                // Update
                $existingCount->update([
                    'count_qty' => $qty,
                    'input_karton' => $qtyKarton,
                    'input_pcs' => $qtyPcs,
                    'counted_by' => $user->id,
                    'counted_at' => now(),
                ]);
            } else {
                // Insert Baru
                OpnameCount::create([
                    'count_uuid' => Str::uuid(),
                    'session_id' => $session->id,
                    'assignment_id' => null, // Kolom ini sekarang nullable, bypass foreign key lama
                    'reference_detail_id' => $refDetailId,
                    'team_id' => $teamId,
                    'count_qty' => $qty,
                    'input_karton' => $qtyKarton,
                    'input_pcs' => $qtyPcs,
                    'count_status' => 'SUBMITTED',
                    'count_sequence' => $targetSequence,
                    'counted_by' => $user->id,
                    'counted_at' => now(),
                    'client_created_at' => now(),
                ]);
            }
            
            // Trigger realtime sync for SINGLE/RECORD_ONLY mode immediately!
            if ($session->mode == 'SINGLE' || $session->mode == 'RECORD_ONLY' || $targetSequence == 1) {
                \App\Http\Controllers\Sodc\Result\CountResultController::syncSingleBin($session->id, $refDetailId);
            }

            // Langsung update opname_results jika ini RECOUNT agar laravel web tidak menunjukkan 0
            if ($targetSequence >= 2 && $targetSequence <= 4) {
                // (Ini sudah di-handle oleh syncSingleBin di atas, tapi kita pertahankan logika dispatch Recount di bawahnya)
                
                // 🔹 LOGIKA PEMBERSIHAN TUGAS RECOUNT (OPSI 1)
                if ($currentRecountAssignment) {
                    // 1. Resmikan hitungan milik user ini
                    \Illuminate\Support\Facades\DB::table('opname_recount_assignments')
                        ->where('assignment_id', $currentRecountAssignment->assignment_id)
                        ->update([
                            'status' => 'COMPLETED',
                            'submitted_at' => now(),
                            'is_final' => true
                        ]);

                    // 2. Hapus sisa tugas untuk orang lain di round yang sama
                    \Illuminate\Support\Facades\DB::table('opname_recount_assignments')
                        ->where('session_id', $session->id)
                        ->where('location_code', $binCode)
                        ->where('id_product', $idProduct)
                        ->where('round_number', $currentRecountAssignment->round_number)
                        ->where('assigned_to', '!=', $user->id)
                        ->delete();
                }
            }
        }


        return response()->json([
            'success' => true,
            'message' => 'Hitungan untuk Bin ' . $binCode . ' berhasil disubmit.'
        ]);
    }

    /**
     * Delete count for a specific product in a bin
     */
    public function deleteCount(Request $request)
    {
        $request->validate([
            'bin_code' => 'required|string',
            'id_product' => 'required|string',
        ]);

        $user = $request->user();
        $sessionId = $request->input('session_id') ?? $request->header('X-Session-Id');
        
        $session = OpnameSession::where('status', 'ACTIVE');
        if ($sessionId) {
            $session->where('id', $sessionId);
        }
        $session = $session->first();

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Tidak ada sesi aktif.'], 400);
        }

        $bin = Bin::where('bin_code', $request->bin_code)->first();
        $teamId = null;
        if ($bin) {
            $area = \App\Models\OpnameUserArea::where('session_id', $session->id)
                ->where('user_id', $user->id)
                ->where('warehouse_id', $bin->warehouse_id)
                ->where(function($q) use ($bin) {
                    $q->where('aisle', $bin->aisle)->orWhereNull('aisle');
                })
                ->first();
            if ($area) {
                $teamId = $area->team_role;
            }
        }

        // Find the reference detail ID
        $refDetail = OpnameReferenceDetail::where('reference_id', $session->reference_id)
            ->where('bin_code', $request->bin_code)
            ->where('sku_code', $request->id_product)
            ->first();

        if ($refDetail) {
            // Delete the count
            $count = OpnameCount::where('session_id', $session->id)
                ->where('team_id', $teamId)
                ->where('reference_detail_id', $refDetail->id)
                ->where('count_sequence', 1)
                ->first();

            if ($count) {
                // Hapus count
                $count->delete();
                
                // Cek apakah masih ada hitungan dari tim lain untuk barang ini
                $remainingCounts = OpnameCount::where('session_id', $session->id)
                    ->where('reference_detail_id', $refDetail->id)
                    ->count();
                    
                if ($remainingCounts == 0) {
                    // Jika tidak ada tim manapun yang menghitung barang ini lagi, bersihkan tabel Result
                    $result = \App\Models\OpnameResult::where('session_id', $session->id)
                        ->where('reference_detail_id', $refDetail->id)
                        ->first();
                        
                    if ($result) {
                        if ((float)$refDetail->system_qty == 0) {
                            // Ini adalah barang nyasar (tidak ada di WMS), maka hapus secara total
                            $result->delete();
                            $refDetail->delete();
                        } else {
                            // Ini barang WMS, kembalikan statusnya jadi UNCOUNTED
                            $result->update([
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
                }

                return response()->json(['success' => true, 'message' => 'Hitungan berhasil dihapus dari server.']);
            }
        }

        return response()->json(['success' => true, 'message' => 'Data tidak ditemukan, namun dianggap sudah terhapus.']);
    }
    public function completeBin(Request $request)
    {
        $request->validate([
            'bin_code' => 'required|string',
        ]);

        $user = $request->user();
        $sessionId = $request->input('session_id') ?? $request->header('X-Session-Id');
        
        $activeSessionQuery = \App\Models\OpnameSession::where('status', 'ACTIVE');
        if ($sessionId) {
            $activeSessionQuery->where('id', $sessionId);
        }
        
        $session = $activeSessionQuery->first();
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Tidak ada sesi opname aktif.'], 400);
        }

        $binCode = $request->bin_code;
        $bin = \App\Models\Bin::where('bin_code', $binCode)->first();
        
        $teamId = null;
        if ($bin) {
            $area = \App\Models\OpnameUserArea::where('session_id', $session->id)
                ->where('user_id', $user->id)
                ->first();
                
            if ($area && $area->team_role) {
                $teamId = $area->team_role;
            }
            
            // Override if it is a recount assignment
            if (\Illuminate\Support\Facades\Schema::hasTable('opname_recount_assignments')) {
                $recount = \Illuminate\Support\Facades\DB::table('opname_recount_assignments')
                    ->where('session_id', $session->id)
                    ->where('assigned_to', $user->id)
                    ->where('location_code', $bin->bin_code)
                    ->whereIn('status', ['PENDING', 'ASSIGNED', 'IN_PROGRESS'])
                    ->orderBy('round_number', 'desc')
                    ->first();
                if ($recount) {
                    $teamId = 'RECOUNT';
                }
            }

            if (!$teamId || $teamId === 'UNKNOWN') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Anda belum diberikan tugas.'
                ], 403);
            }
        }

        // Ambil semua barang (termasuk ad-hoc dari tim lain) di rak ini
        $refDetails = \App\Models\OpnameReferenceDetail::where('reference_id', $session->reference_id)
            ->where('bin_code', $binCode)
            ->get();

        $insertedCount = 0;
        foreach ($refDetails as $ref) {
            // Cek apakah tim ini sudah menghitung barang ini
            $existingCount = \App\Models\OpnameCount::where('session_id', $session->id)
                ->where('team_id', $teamId)
                ->where('reference_detail_id', $ref->id)
                ->first();

            // Jika belum hitung sama sekali, paksa nilai 0
            if (!$existingCount) {
                \App\Models\OpnameCount::create([
                    'count_uuid' => \Illuminate\Support\Str::uuid(),
                    'session_id' => $session->id,
                    'assignment_id' => null,
                    'reference_detail_id' => $ref->id,
                    'team_id' => $teamId,
                    'count_qty' => 0,
                    'input_karton' => 0,
                    'input_pcs' => 0,
                    'count_status' => 'SUBMITTED',
                    'count_sequence' => 1,
                    'counted_by' => $user->id,
                    'counted_at' => now(),
                    'client_created_at' => now(),
                ]);
                $insertedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Rak $binCode berhasil ditutup. $insertedCount barang kosong (qty 0) telah ter-generate otomatis.",
            'inserted_zero_qty' => $insertedCount
        ]);
    }
}




