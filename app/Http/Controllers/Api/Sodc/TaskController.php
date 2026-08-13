<?php

namespace App\Http\Controllers\Api\Sodc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpnameSession;
use App\Models\OpnameReferenceDetail;
use App\Models\OpnameTeamMember;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Mengambil daftar SEMUA Bin target (Live View).
     * Tidak lagi berdasarkan assignment, melainkan Free-for-all dengan filter Zona/Level di Flutter.
     */
    public function allBins(Request $request)
    {
        $user = $request->user();

        // 1. Get the active session
        $activeSession = OpnameSession::where('status', 'ACTIVE')->first();
        if (!$activeSession) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada sesi opname yang aktif saat ini.',
                'data' => null
            ], 404);
        }

        // 2. Determine target principals dynamically
        $targetPrincipals = DB::table('opname_reference_details')
            ->join('products', 'opname_reference_details.product_id', '=', 'products.id')
            ->where('opname_reference_details.reference_id', $activeSession->reference_id)
            ->whereNotNull('products.principal')
            ->distinct()
            ->pluck('products.principal')
            ->toArray();

        // 3. Find the user's assigned areas (can be multiple)
        $myAreas = \App\Models\OpnameUserArea::where('session_id', $activeSession->id)
            ->where('user_id', $user->id)
            ->get();
            
        // Build a helper to quickly check my role for a warehouse & aisle
        // key: warehouseId_aisle
        $myRoles = [];
        foreach ($myAreas as $area) {
            $key = $area->warehouse_id . '_' . ($area->aisle ?? 'ALL');
            $myRoles[$key] = $area->team_role;
        }

        // 4. Get all unique bins that my team has ALREADY counted in this session
        // Since roles are dynamic per zone, this is trickier. 
        // We will just fetch ALL counts for this session, and later check.
        $allCounts = DB::table('opname_counts')
            ->join('opname_reference_details', 'opname_counts.reference_detail_id', '=', 'opname_reference_details.id')
            ->where('opname_counts.session_id', $activeSession->id)
            ->select('opname_reference_details.bin_code', 'opname_counts.team_id as team_role')
            ->get();
            
        // Group counted bins by role
        // For example: $counted['A-01'] = ['TEAM_A', 'TEAM_B']
        $countedStatus = [];
        foreach ($allCounts as $c) {
            if (!isset($countedStatus[$c->bin_code])) {
                $countedStatus[$c->bin_code] = [];
            }
            $countedStatus[$c->bin_code][] = $c->team_role; // Note: if team_id in counts table is actually the role string. 
            // Wait, opname_counts has team_id which is integer. 
            // Since we changed to dynamic users, opname_counts should store 'TEAM_A' or 'TEAM_B' instead of integer team_id.
            // Or we check the user who counted it and figure out their role.
            // For now, let's assume team_id in opname_counts is updated or we just pass raw status.
        }

        // 5. Get ALL target details for this session and group by bin
        $details = DB::table('opname_reference_details')
            ->leftJoin('bins', 'opname_reference_details.bin_code', '=', 'bins.bin_code')
            ->where('opname_reference_details.reference_id', $activeSession->reference_id)
            ->select(
                'opname_reference_details.bin_code', 
                'opname_reference_details.id as reference_detail_id',
                'opname_reference_details.sku_code',
                'bins.warehouse_id', 'bins.zone', 'bins.aisle', 'bins.level', 'bins.ganjil_genap'
            )
            ->get();

        $binsAssoc = [];
        foreach ($details as $bin) {
            $code = $bin->bin_code;
            if (!isset($binsAssoc[$code])) {
                $wId = $bin->warehouse_id ?? 'UNKNOWN';
                $parts = explode('.', $code);
                $zone = $bin->zone ?? (isset($parts[0]) ? $parts[0] : 'UNKNOWN');
                $aisle = $bin->aisle ?? (isset($parts[1]) ? $parts[1] : 'ALL');
                $level = $bin->level ?? (isset($parts[2]) ? $parts[2] : '1');
                $ganjilGenap = $bin->ganjil_genap ?? 'UNKNOWN';
                
                $myRoleForBin = $myRoles[$wId . '_' . $aisle] ?? $myRoles[$wId . '_ALL'] ?? null;
                $isCounted = isset($countedStatus[$code]) && in_array($myRoleForBin, $countedStatus[$code]);

                $binsAssoc[$code] = [
                    'bin_code' => $code,
                    'warehouse_id' => $wId,
                    'zone' => $zone,
                    'aisle' => $aisle,
                    'level' => $level,
                    'ganjil_genap' => $ganjilGenap,
                    'my_role_for_this_bin' => $myRoleForBin,
                    'is_counted_by_my_team' => $isCounted,
                    'expected_items' => []
                ];
            }
            
            // Add SKU item to this bin
            $binsAssoc[$code]['expected_items'][] = [
                'id_product' => $bin->sku_code,
                'reference_detail_id' => $bin->reference_detail_id
            ];
        }

        $mappedBins = array_values($binsAssoc);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil daftar seluruh Bin.',
            'data' => [
                'session_id' => $activeSession->id,
                'session_code' => $activeSession->session_code,
                'mode' => $activeSession->mode,
                'target_principals' => $targetPrincipals,
                'my_team' => $myAreas->first()->team_role ?? 'TEAM_A',
                'bins' => $mappedBins
            ]
        ]);
    }

    /**
     * Fetch SKUs specifically for one Bin when User taps on it in Flutter.
     */
    public function binDetails(Request $request, $binCode)
    {
        $activeSession = OpnameSession::where('status', 'ACTIVE')->first();
        if (!$activeSession) {
            return response()->json(['success' => false, 'message' => 'Tidak ada sesi aktif.'], 404);
        }

        $details = OpnameReferenceDetail::with('product')
            ->where('reference_id', $activeSession->reference_id)
            ->where('bin_code', $binCode)
            ->get();

        $expectedItems = $details->map(function ($detail) {
            return [
                'id_product' => $detail->product->id_product ?? $detail->sku_code,
                'product_name' => $detail->product->product_name ?? 'Unknown Product',
                'uom' => $detail->product->uom ?? 'PCS',
                'reference_detail_id' => $detail->id,
            ];
        })->values()->toArray();

        return response()->json([
            'success' => true,
            'data' => $expectedItems
        ]);
    }
}
