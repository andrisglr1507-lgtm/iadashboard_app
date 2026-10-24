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
        // Ambil sesi ACTIVE dan CLOSED agar muncul di aplikasi
        $sessions = OpnameSession::whereIn('status', ['ACTIVE', 'CLOSED'])->orderBy('id', 'desc')->get();
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
        
        $activeSession = null;
        if ($sessionId) {
            $activeSession = OpnameSession::find($sessionId);
        } else {
            $activeSession = OpnameSession::where('status', 'ACTIVE')->first();
        }
        
        if (!$activeSession) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada sesi opname yang aktif saat ini.',
                'data' => null
            ], 404);
        }
        
        if ($activeSession->status === 'CLOSED') {
            return response()->json([
                'success' => false,
                'message' => 'Session ini sudah close.',
                'data' => null
            ], 400);
        }

        // 2. Determine target principals dynamically
        $targetPrincipals = DB::table('opname_reference_details')
            ->join('products', 'opname_reference_details.product_id', '=', 'products.id')
            ->where('opname_reference_details.reference_id', $activeSession->reference_id)
            ->whereNotNull('products.principal')
            ->distinct()
            ->pluck('products.principal')
            ->toArray();

        // Fetch my recount assignments for this session (GET ALL STATUSES INCLUDING COMPLETED)
        $myRecountsRaw = collect([]);
        if (\Illuminate\Support\Facades\Schema::hasTable('opname_recount_assignments')) {
            $myRecountsRaw = \Illuminate\Support\Facades\DB::table('opname_recount_assignments')
                ->where('session_id', $activeSession->id)
                ->where('assigned_to', $user->id)
                ->get();
        }
            
        // Map recount tasks by bin_code AND round
        $recountBins = [];
        foreach ($myRecountsRaw as $r) {
            $rType = 'R' . ($r->round_number ?? 1);
            if (!isset($recountBins[$r->location_code])) {
                $recountBins[$r->location_code] = [];
            }
            $recountBins[$r->location_code][$rType] = $r->status;
        }

        // 3. Find the user's assigned areas (can be multiple)
        $myAreas = \App\Models\OpnameUserArea::where('session_id', $activeSession->id)
            ->where('user_id', $user->id)
            ->get();
            
        // Build a helper to quickly check my role for a warehouse & aisle
        $myRoles = [];
        foreach ($myAreas as $area) {
            $key = $area->warehouse_id . '_' . ($area->aisle ?? 'ALL');
            $myRoles[$key] = $area->team_role;
        }

        $myGlobalTeamRole = $myAreas->isNotEmpty() ? $myAreas->first()->team_role : 'UNASSIGNED';

        // 4. Get all unique bins that my team has ALREADY counted in this session
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
            ->leftJoin('warehouses', 'bins.warehouse_id', '=', 'warehouses.id')
            ->where('opname_reference_details.reference_id', $activeSession->reference_id)
            ->select(
                'opname_reference_details.bin_code', 
                'opname_reference_details.id as reference_detail_id',
                'opname_reference_details.sku_code',
                'opname_reference_details.system_qty',
                'opname_reference_details.is_ad_hoc',
                'bins.warehouse_id', 'bins.zone', 'bins.aisle', 'bins.level', 'bins.ganjil_genap',
                'warehouses.warehouse_name', 'warehouses.warehouse_code'
            )
            ->get();

        $binsAssoc = [];
        foreach ($details as $bin) {
            $code = $bin->bin_code;
            $wId = $bin->warehouse_id ?? 'UNKNOWN';
            $parts = explode('.', $code);
            $zone = $bin->zone ?? (isset($parts[0]) ? $parts[0] : 'UNKNOWN');
            $aisle = $bin->aisle ?? (isset($parts[1]) ? $parts[1] : 'ALL');
            $level = $bin->level ?? (isset($parts[2]) ? $parts[2] : '1');
            $ganjilGenap = $bin->ganjil_genap ?? 'UNKNOWN';
            
            $myRoleForBin = $myRoles[$wId . '_' . $aisle] 
                         ?? $myRoles[$wId . '_ZONE_' . $zone . '_' . $ganjilGenap] 
                         ?? $myRoles[$wId . '_ZONE_' . $zone] 
                         ?? $myRoles[$wId . '_ALL'] 
                         ?? null;
            $countedByTeams = isset($countedStatus[$code]) ? array_values(array_unique($countedStatus[$code])) : [];
            $actualPrincipals = isset($countedPrincipals[$code]) ? array_values(array_unique($countedPrincipals[$code])) : [];
            $isAdHoc = (bool)$bin->is_ad_hoc;
            
            if ($myGlobalTeamRole === 'TEAM_RECOUNT') {
                if (isset($recountBins[$code])) {
                    foreach ($recountBins[$code] as $rType => $status) {
                        $uniqueCode = $code . '_' . $rType;
                        if (!isset($binsAssoc[$uniqueCode])) {
                            $isCounted = ($status === 'COMPLETED');
                            $binsAssoc[$uniqueCode] = [
                                'bin_code' => $code, // Keep original bin_code for UI rendering
                                'warehouse_id' => $wId,
                                'warehouse_name' => $bin->warehouse_name ?? ($wId !== 'UNKNOWN' ? 'Gudang ' . $wId : null),
                                'warehouse_code' => $bin->warehouse_code ?? null,
                                'zone' => $zone,
                                'aisle' => $aisle,
                                'level' => $level,
                                'ganjil_genap' => $ganjilGenap,
                                'my_role_for_this_bin' => $myRoleForBin,
                                'is_counted_by_my_team' => $isCounted,
                                'counted_by_teams' => $countedByTeams,
                                'actual_principals' => $actualPrincipals,
                                'is_ad_hoc' => $isAdHoc,
                                'taskType' => $rType,
                                'expected_items' => []
                            ];
                        }
                        
                        $binsAssoc[$uniqueCode]['expected_items'][] = [
                            'id_product' => $bin->sku_code,
                            'reference_detail_id' => $bin->reference_detail_id
                        ];
                    }
                }
            } else {
                if (!isset($binsAssoc[$code])) {
                    $isCounted = isset($countedStatus[$code]) && in_array($myGlobalTeamRole, $countedStatus[$code]);
                    $binsAssoc[$code] = [
                        'bin_code' => $code,
                        'warehouse_id' => $wId,
                        'warehouse_name' => $bin->warehouse_name ?? ($wId !== 'UNKNOWN' ? 'Gudang ' . $wId : null),
                        'warehouse_code' => $bin->warehouse_code ?? null,
                        'zone' => $zone,
                        'aisle' => $aisle,
                        'level' => $level,
                        'ganjil_genap' => $ganjilGenap,
                        'my_role_for_this_bin' => $myRoleForBin,
                        'is_counted_by_my_team' => $isCounted,
                        'counted_by_teams' => $countedByTeams,
                        'actual_principals' => $actualPrincipals,
                        'is_ad_hoc' => $isAdHoc,
                        'taskType' => 'NORMAL',
                        'expected_items' => []
                    ];
                }
                
                $binsAssoc[$code]['expected_items'][] = [
                    'id_product' => $bin->sku_code,
                    'reference_detail_id' => $bin->reference_detail_id
                ];
            }
        }

        $mappedBins = array_values($binsAssoc);

        // Fetch ALL master bins for the involved warehouses
        $warehouseIds = $details->pluck('warehouse_id')->unique()->filter()->toArray();
        $allWarehouseBins = [];
        if (!empty($warehouseIds)) {
            $allWarehouseBins = DB::table('bins')
                ->leftJoin('warehouses', 'bins.warehouse_id', '=', 'warehouses.id')
                ->whereIn('bins.warehouse_id', $warehouseIds)
                ->select(
                    'bins.bin_code',
                    'bins.zone',
                    'bins.level',
                    'bins.aisle',
                    'bins.ganjil_genap',
                    'bins.warehouse_id',
                    'warehouses.warehouse_name',
                    'warehouses.warehouse_code'
                )
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
        
        $activeSession = null;
        if ($sessionId) {
            $activeSession = OpnameSession::find($sessionId);
        } else {
            $activeSession = OpnameSession::where('status', 'ACTIVE')->first();
        }
        
        if (!$activeSession) {
            return response()->json(['success' => false, 'message' => 'Tidak ada sesi aktif.'], 404);
        }
        
        if ($activeSession->status === 'CLOSED') {
            return response()->json(['success' => false, 'message' => 'Session ini sudah close.'], 400);
        }

        $details = OpnameReferenceDetail::with('product')
            ->where('reference_id', $activeSession->reference_id)
            ->where('bin_code', $binCode)
            ->get();

        $user = $request->user();
        $myAreas = \Illuminate\Support\Facades\DB::table('opname_user_areas')
            ->where('user_id', $user->id)
            ->where('session_id', $activeSession->id)
            ->get();
        $myGlobalTeamRole = $myAreas->isNotEmpty() ? $myAreas->first()->team_role : 'UNASSIGNED';

        // Fetch ALL recount assignments for this bin (including COMPLETED, for all rounds)
        $recountQuery = DB::table('opname_recount_assignments')
            ->where('session_id', $activeSession->id)
            ->where('location_code', $binCode);
            
        if ($myGlobalTeamRole === 'TEAM_RECOUNT') {
            // Team Recount can access any recount assigned for this bin
            $myRecountsRaw = $recountQuery->get();
        } else {
            $myRecountsRaw = $recountQuery->where('assigned_to', $user->id)->get();
        }

        // Ambil semua counts untuk reference details di bin ini
        $allCounts = \App\Models\OpnameCount::where('session_id', $activeSession->id)
            ->whereIn('reference_detail_id', $details->pluck('id'))
            ->get();
            
        // Ambil opname_results untuk referensi juri
        $allResults = \App\Models\OpnameResult::where('session_id', $activeSession->id)
            ->whereIn('reference_detail_id', $details->pluck('id'))
            ->get()
            ->keyBy('reference_detail_id');

        $expectedItems = collect([]);

        foreach ($details as $detail) {
            $productId = $detail->product->id_product ?? $detail->sku_code;
            $resultRef = $allResults->get($detail->id);
            
            $countA = $allCounts->where('reference_detail_id', $detail->id)->where('team_id', 'TEAM_A')->where('count_sequence', 1)->first();
            $countB = $allCounts->where('reference_detail_id', $detail->id)->where('team_id', 'TEAM_B')->where('count_sequence', 1)->first();
            $countR1 = $allCounts->where('reference_detail_id', $detail->id)->where('team_id', 'RECOUNT')->where('count_sequence', 2)->first();
            $countR2 = $allCounts->where('reference_detail_id', $detail->id)->where('team_id', 'RECOUNT')->where('count_sequence', 3)->first();

            // Check if there are recount assignments for this product
            $matchingRecounts = $myRecountsRaw->where('id_product', $productId);

            if ($matchingRecounts->isNotEmpty()) {
                foreach ($matchingRecounts as $rc) {
                    $roundNumber = $rc->round_number ?? 1;
                    $taskType = 'R' . $roundNumber;
                    $expectedSequence = $roundNumber + 1;

                    $myCount = $allCounts->where('reference_detail_id', $detail->id)
                                         ->where('team_id', 'RECOUNT')
                                         ->where('count_sequence', $expectedSequence)
                                         ->first();

                    $isCounted = ($rc->status === 'COMPLETED') || ($myCount !== null);

                    $expectedItems->push([
                        'id_product' => $productId,
                        'product_name' => $detail->product->product_name ?? 'Unknown Product',
                        'uom' => $detail->product->uom ?? 'PCS',
                        'reference_detail_id' => $detail->id,
                        'is_counted' => $isCounted,
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
                        // Referensi Tim R1
                        'team_r1_qty' => $resultRef ? $resultRef->recount1_qty : ($countR1 ? $countR1->count_qty : null),
                        'team_r1_karton' => $countR1 ? $countR1->input_karton : 0,
                        'team_r1_pcs' => $countR1 ? $countR1->input_pcs : 0,
                        // Referensi Tim R2
                        'team_r2_qty' => $resultRef ? $resultRef->recount2_qty : ($countR2 ? $countR2->count_qty : null),
                        'team_r2_karton' => $countR2 ? $countR2->input_karton : 0,
                        'team_r2_pcs' => $countR2 ? $countR2->input_pcs : 0,
                    ]);
                }
            } else {
                // NORMAL Task
                $myCount = $allCounts->where('reference_detail_id', $detail->id)
                                     ->where('team_id', $myGlobalTeamRole)
                                     ->where('count_sequence', 1)
                                     ->first();

                $expectedItems->push([
                    'id_product' => $productId,
                    'product_name' => $detail->product->product_name ?? 'Unknown Product',
                    'uom' => $detail->product->uom ?? 'PCS',
                    'reference_detail_id' => $detail->id,
                    'is_counted' => $myCount !== null,
                    'counted_qty_karton' => $myCount ? $myCount->input_karton : 0,
                    'counted_qty_pcs' => $myCount ? $myCount->input_pcs : 0,
                    'counted_final_qty' => $myCount ? $myCount->count_qty : 0,
                    'taskType' => 'NORMAL',
                    // Referensi Tim A
                    'team_a_qty' => $resultRef ? $resultRef->team_a_qty : ($countA ? $countA->count_qty : null),
                    'team_a_karton' => $countA ? $countA->input_karton : 0,
                    'team_a_pcs' => $countA ? $countA->input_pcs : 0,
                    // Referensi Tim B
                    'team_b_qty' => $resultRef ? $resultRef->team_b_qty : ($countB ? $countB->count_qty : null),
                    'team_b_karton' => $countB ? $countB->input_karton : 0,
                    'team_b_pcs' => $countB ? $countB->input_pcs : 0,
                    // Referensi Tim R1
                    'team_r1_qty' => null,
                    'team_r1_karton' => 0,
                    'team_r1_pcs' => 0,
                    // Referensi Tim R2
                    'team_r2_qty' => null,
                    'team_r2_karton' => 0,
                    'team_r2_pcs' => 0,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $expectedItems->values()->toArray()
        ]);
    }
}


