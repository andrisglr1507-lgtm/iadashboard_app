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
            'counts' => 'required|array',
            'counts.*.id_product' => 'required|string',
            'counts.*.qty' => 'required|numeric|min:0',
            // reference_detail_id is optional, will be null for Ad-Hoc / Unexpected items
            'counts.*.reference_detail_id' => 'nullable|integer'
        ]);

        $user = $request->user();
        
        // Find active session
        $session = OpnameSession::where('status', 'ACTIVE')->first();
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Tidak ada sesi opname aktif.'], 400);
        }

        // Find Team
        $teamMember = OpnameTeamMember::where('user_id', $user->id)->first();
        $teamId = $teamMember ? $teamMember->team_id : null;

        $binCode = $request->bin_code;
        $bin = Bin::where('bin_code', $binCode)->first();

        foreach ($request->counts as $c) {
            $refDetailId = $c['reference_detail_id'] ?? null;
            $idProduct = $c['id_product'];
            $qty = $c['qty'];

            // AD-HOC HANDLING: Jika tidak ada reference_detail_id (Barang Nyasar / Sapu Lorong)
            if (!$refDetailId) {
                $product = Product::where('id_product', $idProduct)->first();
                if (!$product) continue; // Skip if invalid product

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
                        'sku_code' => $product->id_product,
                        'bin_code' => $binCode,
                        'system_qty' => 0, // KUNCI UTAMA: Sistem menganggap 0
                        'uom' => $product->uom,
                        'stock_status' => 'GOOD',
                    ]);
                    $refDetailId = $newRef->id;
                }
            }

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
                    'counted_by' => $user->id,
                    'counted_at' => now(),
                ]);
            } else {
                // Insert Baru
                OpnameCount::create([
                    'count_uuid' => Str::uuid(),
                    'session_id' => $session->id,
                    'reference_detail_id' => $refDetailId,
                    'team_id' => $teamId,
                    'count_qty' => $qty,
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
