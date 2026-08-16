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
     * Get all active sessions for the user to choose from.
     */
    public function activeSessions(Request $request)
    {
        $sessions = OpnameSession::where('status', 'ACTIVE')->orderBy('id', 'desc')->get();
        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil daftar sesi aktif.',
            'data' => $sessions
        ]);
    }

    /**
     * Mengambil daftar SEMUA Bin target (Live View).
     * Tidak lagi berdasarkan assignment, melainkan Free-for-all dengan filter Zona/Level di Flutter.
     */
    public function allBins(Request $request)
    {
        $user = $request->user();

        // 1. Get the active session by ID if provided, otherwise fallback to the first one (for backward compatibility)
        $sessionId = $request->input('session_id') ?? $request->header('X-Session-Id');
        
        $activeSessionQuery = OpnameSession::where('status', 'ACTIVE');
        if ($sessionId) {
            $activeSessionQuery->where('id', $sessionId);
        }
        
        $activeSession = $activeSessionQuery->first();
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

        // Fetch my recount assignments for this session
        $myRecountsRaw = collect([]);
        if (\Illuminate\Support\Facades\Schema::hasTable('opname_recount_assignments')) {
            $myRecountsRaw = \Illuminate\Support\Facades\DB::table('opname_recount_assignments')
                ->where('session_id', $activeSession->id)
                ->where('assigned_to', $user->id) // Fix: use user->id instead of user_id
                ->whereIn('status', ['PENDING', 'ASSIGNED', 'IN_PROGRESS'])
                ->get();
        }
            
        // Map recount tasks by bin_code and sku
        $recountBins = [];
        $recountItems = []; // bin_code_sku => round_number
        foreach ($myRecountsRaw as $r) {
            $rType = 'R' . ($r->round_number ?? 1);
            $recountBins[$r->location_code] = $rType;
            $recountItems[$r->location_code . '_' . $r->id_product] = $rType;
        }

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

        $myGlobalTeamRole = $myAreas->isNotEmpty() ? $myAreas->first()->team_role : 'UNASSIGNED';

        // 4. Get all unique bins that my team has ALREADY counted in this session
        // Since roles are dynamic per zone, this is trickier. 
        // We will just fetch ALL counts for this session, and later check.
        $allCounts = DB::table('opname_counts')
            ->join('opname_reference_details', 'opname_counts.reference_detail_id', '=', 'opname_reference_details.id')
            ->leftJoin('products', 'opname_reference_details.sku_code', '=', 'products.sku_code')
            ->where('opname_counts.session_id', $activeSession->id)
            ->select('opname_reference_details.bin_code', 'opname_counts.team_id as team_role', 'products.principal')
            ->get();
            
        // Group counted bins by role and gather actual principals
        $countedStatus = [];
        $countedPrincipals = [];
        foreach ($allCounts as $c) {
            if (!isset($countedStatus[$c->bin_code])) {
                $countedStatus[$c->bin_code] = [];
            }
            if (!isset($countedPrincipals[$c->bin_code])) {
                $countedPrincipals[$c->bin_code] = [];
            }
            $countedStatus[$c->bin_code][] = $c->team_role;
            
            $pr = $c->principal ? trim($c->principal) : '';
            if (!empty($pr)) {
                $countedPrincipals[$c->bin_code][] = strtoupper($pr);
            } else {
                $countedPrincipals[$c->bin_code][] = 'LAINNYA';
            }
        }

        // 5. Get ALL target details for this session and group by bin
        $details = DB::table('opname_reference_details')
            ->leftJoin('bins', 'opname_reference_details.bin_code', '=', 'bins.bin_code')
            ->where('opname_reference_details.reference_id', $activeSession->reference_id)
            ->select(
                'opname_reference_details.bin_code', 
                'opname_reference_details.id as reference_detail_id',
                'opname_reference_details.sku_code',
                'opname_reference_details.system_qty',
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
                $isCounted = isset($countedStatus[$code]) && in_array($myGlobalTeamRole, $countedStatus[$code]);

                $binsAssoc[$code] = [
                    'bin_code' => $code,
                    'warehouse_id' => $wId,
                    'zone' => $zone,
                    'aisle' => $aisle,
                    'level' => $level,
                    'ganjil_genap' => $ganjilGenap,
                    'my_role_for_this_bin' => $myRoleForBin,
                    'is_counted_by_my_team' => $isCounted,
                    'counted_by_teams' => isset($countedStatus[$code]) ? array_values(array_unique($countedStatus[$code])) : [],
                    'actual_principals' => isset($countedPrincipals[$code]) ? array_values(array_unique($countedPrincipals[$code])) : [],
                    'is_ad_hoc' => true, // Will be set to false if any item has system_qty > 0
                    'taskType' => $recountBins[$code] ?? 'NORMAL', // Attach recount type
                    'expected_items' => []
                ];
            }
            
            // If at least one item in this bin has system_qty > 0, it means the bin itself was part of the WMS target
            if ($bin->system_qty > 0) {
                $binsAssoc[$code]['is_ad_hoc'] = false;
            }
            
            // Add SKU item to this bin
            $binsAssoc[$code]['expected_items'][] = [
                'id_product' => $bin->sku_code,
                'reference_detail_id' => $bin->reference_detail_id
            ];
        }

        $mappedBins = array_values($binsAssoc);

        if ($myGlobalTeamRole === 'TEAM_RECOUNT') {
            $mappedBins = array_values(array_filter($mappedBins, function($bin) use ($recountBins) {
                return isset($recountBins[$bin['bin_code']]);
            }));
        }

        // Fetch ALL master bins for the involved warehouses
        $warehouseIds = $details->pluck('warehouse_id')->unique()->filter()->toArray();
        $allWarehouseBins = [];
        if (!empty($warehouseIds)) {
            $allWarehouseBins = DB::table('bins')
                ->whereIn('warehouse_id', $warehouseIds)
                ->select('bin_code', 'zone', 'ganjil_genap')
                ->get()
                ->toArray();
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil daftar seluruh Bin.',
            'data' => [
                'session_id' => $activeSession->id,
                'session_code' => $activeSession->session_code,
                'mode' => $activeSession->mode,
                'target_principals' => $targetPrincipals,
                'my_team' => $myAreas->isNotEmpty() ? $myAreas->first()->team_role : 'UNASSIGNED',
                'bins' => $mappedBins,
                'all_warehouse_bins' => $allWarehouseBins
            ]
        ]);
    }

    /**
     * Fetch SKUs specifically for one Bin when User taps on it in Flutter.
     */
    public function binDetails(Request $request, $binCode)
    {
        $sessionId = $request->input('session_id') ?? $request->header('X-Session-Id');
        
        $activeSessionQuery = OpnameSession::where('status', 'ACTIVE');
        if ($sessionId) {
            $activeSessionQuery->where('id', $sessionId);
        }
        
        $activeSession = $activeSessionQuery->first();
        if (!$activeSession) {
            return response()->json(['success' => false, 'message' => 'Tidak ada sesi aktif.'], 404);
        }

        $details = OpnameReferenceDetail::with('product')
            ->where('reference_id', $activeSession->reference_id)
            ->where('bin_code', $binCode)
            ->get();

        $user = $request->user();
        $myRecountsRaw = DB::table('opname_recount_assignments')
            ->where('session_id', $activeSession->id)
            ->where('assigned_to', $user->id)
            ->where('location_code', $binCode)
            ->whereIn('status', ['PENDING', 'ASSIGNED', 'IN_PROGRESS'])
            ->get()
            ->keyBy('id_product');

        // Ambil semua counts untuk reference details di bin ini
        $allCounts = \App\Models\OpnameCount::where('session_id', $activeSession->id)
            ->whereIn('reference_detail_id', $details->pluck('id'))
            ->get();
            
        // Ambil opname_results untuk referensi juri
        $allResults = \App\Models\OpnameResult::where('session_id', $activeSession->id)
            ->whereIn('reference_detail_id', $details->pluck('id'))
            ->get()
            ->keyBy('reference_detail_id');

        $expectedItems = $details->map(function ($detail) use ($allCounts, $myRecountsRaw, $allResults, $user) {
            $productId = $detail->product->id_product ?? $detail->sku_code;
            $taskType = 'NORMAL';
            $expectedSequence = 1;
            
            if ($myRecountsRaw->has($productId)) {
                $roundNumber = $myRecountsRaw->get($productId)->round_number ?? 1;
                $taskType = 'R' . $roundNumber;
                $expectedSequence = $roundNumber + 1; // R1 -> seq 2, R2 -> seq 3
            }

            // Cari count spesifik milik user/team ini sesuai sequence tugasnya
            $myCount = null;
            if ($taskType == 'NORMAL') {
                $myCount = $allCounts->where('reference_detail_id', $detail->id)
                                     ->where('counted_by', $user->id)
                                     ->where('count_sequence', 1)
                                     ->first();
                if (!$myCount) {
                    $myCount = $allCounts->where('reference_detail_id', $detail->id)->first();
                }
            } else {
                // Untuk Recount, hanya terhitung jika ada count dengan sequence yang diminta
                $myCount = $allCounts->where('reference_detail_id', $detail->id)
                                     ->where('team_id', 'RECOUNT')
                                     ->where('count_sequence', $expectedSequence)
                                     ->first();
            }

            $resultRef = $allResults->get($detail->id);
            // Kita coba extract team_a_qty (misal asumsikan jika pakai input karton dll belum tersimpan rinci di opname_results, minimal totalnya)
            // Karena opname_counts punya rincian, kita bisa cari hitungan asli team A dan B
            $countA = $allCounts->where('reference_detail_id', $detail->id)->where('team_id', 'TEAM_A')->where('count_sequence', 1)->first();
            $countB = $allCounts->where('reference_detail_id', $detail->id)->where('team_id', 'TEAM_B')->where('count_sequence', 1)->first();

            return [
                'id_product' => $productId,
                'product_name' => $detail->product->product_name ?? 'Unknown Product',
                'uom' => $detail->product->uom ?? 'PCS',
                'reference_detail_id' => $detail->id,
                'is_counted' => $myCount ? true : false,
                'counted_qty_karton' => $myCount ? $myCount->input_karton : 0,
                'counted_qty_pcs' => $myCount ? $myCount->input_pcs : 0,
                'counted_final_qty' => $myCount ? $myCount->count_qty : 0,
                'taskType' => $taskType,
                // Referensi Tim A
                'team_a_qty' => $resultRef ? $resultRef->team_a_qty : ($countA ? $countA->count_qty : null),
                'team_a_karton' => $countA ? $countA->input_karton : 0,
                'team_a_pcs' => $countA ? $countA->input_pcs : 0,
                // Referensi Tim B
                'team_b_qty' => $resultRef ? $resultRef->team_b_qty : ($countB ? $countB->count_qty : null),
                'team_b_karton' => $countB ? $countB->input_karton : 0,
                'team_b_pcs' => $countB ? $countB->input_pcs : 0,
            ];
        })->values()->toArray();

        return response()->json([
            'success' => true,
            'data' => $expectedItems
        ]);
    }
}


