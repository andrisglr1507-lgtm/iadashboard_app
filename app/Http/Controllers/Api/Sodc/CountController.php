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
        
        $activeSessionQuery = OpnameSession::where('status', 'ACTIVE');
        if ($sessionId) {
            $activeSessionQuery->where('id', $sessionId);
        }
        
        $session = $activeSessionQuery->first();
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Tidak ada sesi opname aktif.'], 400);
        }

        // Find Team Role for this Bin (from UserArea)
        $binCode = $request->bin_code;
        $bin = Bin::where('bin_code', $binCode)->first();
        
        $teamId = null;
        if ($bin) {
            $area = \App\Models\OpnameUserArea::where('session_id', $session->id)
                ->where('user_id', $user->id)
                ->where('warehouse_id', $bin->warehouse_id)
                ->where(function($q) use ($bin) {
                    $q->where('aisle', $bin->aisle)->orWhereNull('aisle');
                })
                ->first();
                
            if ($area && $area->team_role) {
                // Gunakan string team_role secara langsung karena kolom di DB sudah diubah jadi string
                $teamId = $area->team_role;
            } else {
                // Jika user tidak di-assign ke gudang/lorong ini, tolak submit
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki assignment (penugasan) di Gudang/Lorong ini!'
                ], 403);
            }
        }

        $binCode = $request->bin_code;
        $bin = Bin::where('bin_code', $binCode)->first();

        // 🔹 CEK KONFLIK (OFFLINE-FIRST CONFLICT RESOLUTION)
        // Cek apakah Bin ini sudah pernah dihitung oleh anggota tim lain
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
                    // Buat Referensi WMS Palsu/Nol untuk memfasilitasi barang nyasar
                    $newRef = OpnameReferenceDetail::create([
                        'reference_id' => $session->reference_id,
                        'warehouse_id' => $bin ? $bin->warehouse_id : null,
                        'bin_id' => $bin ? $bin->id : null,
                        'product_id' => $product->id,
                        'sku_code' => $product->sku_code,
                        'bin_code' => $binCode,
                        'system_qty' => 0, // KUNCI UTAMA: Sistem menganggap 0
                        'uom' => $product->uom,
                        'stock_status' => 'GOOD',
                    ]);
                    $refDetailId = $newRef->id;
                }
            }
        }

        if ($refDetailId) {
            // Simpan Hitungan ke opname_counts
            // Cek apakah tim ini sudah submit untuk ref ini sebelumnya (Mencegah double insert jika mereka resubmit)
            $existingCount = OpnameCount::where('session_id', $session->id)
                ->where('team_id', $teamId)
                ->where('reference_detail_id', $refDetailId)
                ->where('count_sequence', 1) // Ini hitungan reguler (bukan R1/R2)
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
                    'assignment_id' => 0, // Bypass field yang tidak nullable
                    'reference_detail_id' => $refDetailId,
                    'team_id' => $teamId,
                    'count_qty' => $qty,
                    'input_karton' => $qtyKarton,
                    'input_pcs' => $qtyPcs,
                    'count_status' => 'SUBMITTED',
                    'count_sequence' => 1,
                    'counted_by' => $user->id,
                    'counted_at' => now(),
                    'client_created_at' => now(),
                ]);
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
                                'variance_pct' => null,
                            ]);
                        }
                    }
                }

                return response()->json(['success' => true, 'message' => 'Hitungan berhasil dihapus dari server.']);
            }
        }

        return response()->json(['success' => true, 'message' => 'Data tidak ditemukan, namun dianggap sudah terhapus.']);
    }
}
