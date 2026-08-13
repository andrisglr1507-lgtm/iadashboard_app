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
        
        // Find active session
        $session = OpnameSession::where('status', 'ACTIVE')->first();
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
                // Konversi string 'TEAM_A' / 'TEAM_B' menjadi integer team_id 
                // dengan memastikan data tim tersebut ada di tabel opname_teams
                $team = \App\Models\OpnameTeam::firstOrCreate(
                    ['name' => $area->team_role],
                    ['description' => 'Tim ' . $area->team_role]
                );
                $teamId = $team->id;
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
}
