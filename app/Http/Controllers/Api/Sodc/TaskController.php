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

        // Cek apakah user ini adalah anggota Tim Recount Global yang dipilih di Web (opname_user_areas)
        $isRecountUser = $myAreas->contains('team_role', 'TEAM_RECOUNT');
        $myGlobalTeamRole = $isRecountUser ? 'TEAM_RECOUNT' : ($myAreas->isNotEmpty() ? $myAreas->first()->team_role : 'UNASSIGNED');

        // 4. Fetch all recount tasks for this session:
        // R1 & R2 are formed automatically in opname_results
        $recountResults = DB::table('opname_results')
            ->join('opname_reference_details', 'opname_results.reference_detail_id', '=', 'opname_reference_details.id')
            ->where('opname_results.session_id', $activeSession->id)
            ->where('opname_results.result_status', 'RECOUNT')
            ->select(
                'opname_reference_details.bin_code',
                'opname_reference_details.sku_code',
                'opname_reference_details.id as reference_detail_id',
                'opname_results.recount1_qty',
                'opname_results.recount2_qty'
            )
            ->get();

        // R3 is an assignment created from the web dashboard
        $r3Query = DB::table('opname_recount_assignments')
            ->where('session_id', $activeSession->id)
            ->where('round_number', 3)
            ->where('status', '!=', 'CANCELLED');

        if (!in_array($user->role, ['SUPER_ADMIN', 'ADMIN']) && !$isRecountUser) {
            $r3Query->where('assigned_to', $user->id);
        }
        $r3Assignments = $r3Query->get();

        // 5. Get all unique counts in this session to check is_counted
        $allCounts = DB::table('opname_counts')
            ->join('opname_reference_details', 'opname_counts.reference_detail_id', '=', 'opname_reference_details.id')
            ->leftJoin('products', 'opname_reference_details.sku_code', '=', 'products.sku_code')
            ->where('opname_counts.session_id', $activeSession->id)
            ->select(
                'opname_reference_details.bin_code',
                'opname_reference_details.sku_code',
                'opname_counts.reference_detail_id',
                'opname_counts.team_id as team_role',
                'opname_counts.count_sequence',
                'products.principal'
            )
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

        // Map Recount bins by bin_code AND round (R1, R2, R3)
        $recountBins = [];

        // R1 & R2
        foreach ($recountResults as $res) {
            $binCode = $res->bin_code;
            if (is_null($res->recount1_qty)) {
                $rType = 'R1';
                $targetSeq = 2;
            } elseif (is_null($res->recount2_qty)) {
                $rType = 'R2';
                $targetSeq = 3;
            } else {
                continue;
            }

            if (!isset($recountBins[$binCode])) {
                $recountBins[$binCode] = [];
            }
            if (!isset($recountBins[$binCode][$rType])) {
                $recountBins[$binCode][$rType] = [
                    'all_counted' => true,
                    'items' => []
                ];
            }

            $isItemCounted = $allCounts->contains(function($c) use ($res, $targetSeq) {
                return $c->reference_detail_id == $res->reference_detail_id 
                    && $c->count_sequence == $targetSeq 
                    && $c->team_role == 'RECOUNT';
            });

            if (!$isItemCounted) {
                $recountBins[$binCode][$rType]['all_counted'] = false;
            }

            $recountBins[$binCode][$rType]['items'][] = [
                'id_product' => $res->sku_code,
                'reference_detail_id' => $res->reference_detail_id
            ];
        }

        // R3
        foreach ($r3Assignments as $asg) {
            $binCode = $asg->location_code;
            $rType = 'R3';
            $targetSeq = 4;

            if (!isset($recountBins[$binCode])) {
                $recountBins[$binCode] = [];
            }
            if (!isset($recountBins[$binCode][$rType])) {
                $recountBins[$binCode][$rType] = [
                    'all_counted' => true,
                    'items' => []
                ];
            }

            $isItemCounted = ($asg->status === 'COMPLETED') || $allCounts->contains(function($c) use ($asg, $targetSeq) {
                return $c->bin_code == $asg->location_code 
                    && $c->sku_code == $asg->id_product 
                    && $c->count_sequence == $targetSeq 
                    && $c->team_role == 'RECOUNT';
            });

            if (!$isItemCounted) {
                $recountBins[$binCode][$rType]['all_counted'] = false;
            }

            $recountBins[$binCode][$rType]['items'][] = [
                'id_product' => $asg->id_product,
                'reference_detail_id' => null
            ];
        }

        // 6. Get ALL target details for this session and group by bin
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
                         ?? ($isRecountUser ? 'TEAM_RECOUNT' : null);
            $countedByTeams = isset($countedStatus[$code]) ? array_values(array_unique($countedStatus[$code])) : [];
            $actualPrincipals = isset($countedPrincipals[$code]) ? array_values(array_unique($countedPrincipals[$code])) : [];
            $isAdHoc = (bool)$bin->is_ad_hoc;
            
            if ($isRecountUser) {
                if (isset($recountBins[$code])) {
                    foreach ($recountBins[$code] as $rType => $data) {
                        $uniqueCode = $code . '_' . $rType;
                        if (!isset($binsAssoc[$uniqueCode])) {
                            $binsAssoc[$uniqueCode] = [
                                'bin_code' => $code, // Keep original bin_code for UI rendering
                                'warehouse_id' => $wId,
                                'warehouse_name' => $bin->warehouse_name ?? ($wId !== 'UNKNOWN' ? 'Gudang ' . $wId : null),
                                'warehouse_code' => $bin->warehouse_code ?? null,
                                'zone' => $zone,
                                'aisle' => $aisle,
                                'level' => $level,
                                'ganjil_genap' => $ganjilGenap,
                                'my_role_for_this_bin' => 'TEAM_RECOUNT',
                                'is_counted_by_my_team' => $data['all_counted'],
                                'counted_by_teams' => $countedByTeams,
                                'actual_principals' => $actualPrincipals,
                                'is_ad_hoc' => $isAdHoc,
                                'taskType' => $rType,
                                'expected_items' => $data['items']
                            ];
                        }
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

                // Append R3 task if this regular user was assigned R3 for this bin
                if (isset($recountBins[$code]['R3'])) {
                    $uniqueCode = $code . '_R3';
                    if (!isset($binsAssoc[$uniqueCode])) {
                        $binsAssoc[$uniqueCode] = [
                            'bin_code' => $code,
                            'warehouse_id' => $wId,
                            'warehouse_name' => $bin->warehouse_name ?? ($wId !== 'UNKNOWN' ? 'Gudang ' . $wId : null),
                            'warehouse_code' => $bin->warehouse_code ?? null,
                            'zone' => $zone,
                            'aisle' => $aisle,
                            'level' => $level,
                            'ganjil_genap' => $ganjilGenap,
                            'my_role_for_this_bin' => 'RECOUNT',
                            'is_counted_by_my_team' => $recountBins[$code]['R3']['all_counted'],
                            'counted_by_teams' => $countedByTeams,
                            'actual_principals' => $actualPrincipals,
                            'is_ad_hoc' => $isAdHoc,
                            'taskType' => 'R3',
                            'expected_items' => $recountBins[$code]['R3']['items']
                        ];
                    }
                }
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
        // Cek apakah user ini adalah anggota Tim Recount Global yang dipilih di Web (opname_user_areas)
        $isRecountUser = $myAreas->contains('team_role', 'TEAM_RECOUNT');
        $myGlobalTeamRole = $isRecountUser ? 'TEAM_RECOUNT' : ($myAreas->isNotEmpty() ? $myAreas->first()->team_role : 'UNASSIGNED');

        $requestedTaskType = $request->input('taskType') ?? $request->input('task_type');

        // Fetch ALL recount assignments for this bin (including COMPLETED, for round 3)
        $recountQuery = DB::table('opname_recount_assignments')
            ->where('session_id', $activeSession->id)
            ->where('location_code', $binCode)
            ->where('status', '!=', 'CANCELLED');
            
        if (!in_array($user->role, ['SUPER_ADMIN', 'ADMIN']) && !$isRecountUser) {
            $recountQuery->where('assigned_to', $user->id);
        }
        $myRecountsRaw = $recountQuery->get()->keyBy('id_product');

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
            $r3 = $myRecountsRaw->get($productId);
            
            $countA = $allCounts->where('reference_detail_id', $detail->id)->where('team_id', 'TEAM_A')->where('count_sequence', 1)->first();
            $countB = $allCounts->where('reference_detail_id', $detail->id)->where('team_id', 'TEAM_B')->where('count_sequence', 1)->first();
            $countR1 = $allCounts->where('reference_detail_id', $detail->id)->where('team_id', 'RECOUNT')->where('count_sequence', 2)->first();
            $countR2 = $allCounts->where('reference_detail_id', $detail->id)->where('team_id', 'RECOUNT')->where('count_sequence', 3)->first();
            $countR3 = $allCounts->where('reference_detail_id', $detail->id)->where('team_id', 'RECOUNT')->where('count_sequence', 4)->first();

            // Determine taskType for this item
            $taskType = 'NORMAL';
            $expectedSequence = 1;
            $myCount = null;
            $isCounted = false;

            if ($requestedTaskType === 'R3' || ($r3 && ($requestedTaskType == 'R3' || ($isRecountUser && $resultRef && !is_null($resultRef->recount2_qty))))) {
                $taskType = 'R3';
                $expectedSequence = 4;
                $myCount = $countR3;
                $isCounted = ($r3 && $r3->status === 'COMPLETED') || ($myCount !== null);
            } elseif ($requestedTaskType === 'R2' || ($isRecountUser && $resultRef && $resultRef->result_status === 'RECOUNT' && !is_null($resultRef->recount1_qty) && is_null($resultRef->recount2_qty))) {
                $taskType = 'R2';
                $expectedSequence = 3;
                $myCount = $countR2;
                $isCounted = ($myCount !== null);
            } elseif ($requestedTaskType === 'R1' || ($isRecountUser && $resultRef && $resultRef->result_status === 'RECOUNT' && is_null($resultRef->recount1_qty))) {
                $taskType = 'R1';
                $expectedSequence = 2;
                $myCount = $countR1;
                $isCounted = ($myCount !== null);
            } elseif ($isRecountUser) {
                // Item in this bin that does not need recount (e.g. matched or final)
                $taskType = $requestedTaskType ?: 'NORMAL';
                $myCount = null;
                $isCounted = true;
            } else {
                // NORMAL Task for Team A or Team B
                $taskType = 'NORMAL';
                $expectedSequence = 1;
                $myCount = $allCounts->where('reference_detail_id', $detail->id)
                                     ->where('team_id', $myGlobalTeamRole)
                                     ->where('count_sequence', 1)
                                     ->first();
                $isCounted = ($myCount !== null);
            }

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

        return response()->json([
            'success' => true,
            'data' => $expectedItems->values()->toArray()
        ]);
    }
}


