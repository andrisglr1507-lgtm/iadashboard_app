<?php

namespace App\Http\Controllers\Api\Sodc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OpnameSession;

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        try {
            $user = $request->user();
            
            // Cari session yang aktif
            $activeSession = OpnameSession::where('status', 'ACTIVE')->first();
            
            if (!$activeSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada sesi aktif',
                    'data' => null
                ], 404);
            }
            
            $sessionId = $activeSession->id;
            $referenceId = $activeSession->reference_id;
            
            // 1. Session Info
            $sessionNo = $activeSession->session_name ?? 'Session ' . $sessionId;
            $wmsRef = "WMS-" . $sessionId; // Placeholder
            
            $activePrincipals = DB::table('opname_reference_details')
                ->join('products', 'opname_reference_details.product_id', '=', 'products.id')
                ->where('opname_reference_details.reference_id', $referenceId)
                ->whereNotNull('products.principal')
                ->distinct()
                ->pluck('products.principal')
                ->toArray();
                
            // Resolve Team IDs
            $teamAIds = DB::table('opname_teams')->where('team_code', 'like', 'A%')->pluck('id')->toArray();
            $teamBIds = DB::table('opname_teams')->where('team_code', 'like', 'B%')->pluck('id')->toArray();
            $teamR1Ids = DB::table('opname_teams')->where('team_code', 'like', 'R1%')->pluck('id')->toArray();
            $teamR2Ids = DB::table('opname_teams')->where('team_code', 'like', 'R2%')->pluck('id')->toArray();

            // 2. Bin Stats
            $totalBins = DB::table('opname_reference_details')
                ->where('reference_id', $referenceId)
                ->distinct('bin_code')
                ->count('bin_code');
                
            $binsCompletedByA = 0;
            if (count($teamAIds) > 0) {
                $binsCompletedByA = DB::table('opname_counts')
                    ->join('opname_reference_details', 'opname_counts.reference_detail_id', '=', 'opname_reference_details.id')
                    ->where('opname_counts.session_id', $sessionId)
                    ->whereIn('opname_counts.team_id', $teamAIds)
                    ->distinct('opname_reference_details.bin_code')
                    ->count('opname_reference_details.bin_code');
            }

            $binsCompletedByB = 0;
            if (count($teamBIds) > 0) {
                $binsCompletedByB = DB::table('opname_counts')
                    ->join('opname_reference_details', 'opname_counts.reference_detail_id', '=', 'opname_reference_details.id')
                    ->where('opname_counts.session_id', $sessionId)
                    ->whereIn('opname_counts.team_id', $teamBIds)
                    ->distinct('opname_reference_details.bin_code')
                    ->count('opname_reference_details.bin_code');
            }
                
            $gapA = max(0, $totalBins - $binsCompletedByA);
            $gapB = max(0, $totalBins - $binsCompletedByB);

            // Match vs Recount (Bin Level)
            $binsStatus = DB::table('opname_results')
                ->join('opname_reference_details', 'opname_results.reference_detail_id', '=', 'opname_reference_details.id')
                ->where('opname_results.session_id', $sessionId)
                ->select('opname_reference_details.bin_code', 'opname_results.result_status')
                ->get();
                
            $binsGrouping = [];
            foreach($binsStatus as $row) {
                $binsGrouping[$row->bin_code][] = $row->result_status;
            }
            
            $binsMatch = 0;
            $binsRecount = 0;
            
            foreach($binsGrouping as $binCode => $statuses) {
                if (in_array('RECOUNT', $statuses) || in_array('UNCOUNTED', $statuses)) {
                    $binsRecount++;
                } else {
                    $binsMatch++;
                }
            }

            // 3. Item Stats
            $totalWmsSkus = DB::table('opname_reference_details')
                ->where('reference_id', $referenceId)
                ->count();
                
            $addItems = DB::table('opname_reference_details')
                ->where('reference_id', $referenceId)
                ->where('system_qty', 0)
                ->count();
            
            // 4. Recount Stats (Assignments)
            $r1Assigned = 0;
            $r1Done = 0;
            if (count($teamR1Ids) > 0) {
                $r1Assigned = DB::table('opname_assignments')
                    ->where('session_id', $sessionId)
                    ->whereIn('team_id', $teamR1Ids)
                    ->distinct('reference_detail_id')
                    ->count('reference_detail_id');

                $r1Done = DB::table('opname_assignments')
                    ->where('session_id', $sessionId)
                    ->whereIn('team_id', $teamR1Ids)
                    ->where('status', 'COMPLETED')
                    ->distinct('reference_detail_id')
                    ->count('reference_detail_id');
            }

            $r2Assigned = 0;
            $r2Done = 0;
            if (count($teamR2Ids) > 0) {
                $r2Assigned = DB::table('opname_assignments')
                    ->where('session_id', $sessionId)
                    ->whereIn('team_id', $teamR2Ids)
                    ->distinct('reference_detail_id')
                    ->count('reference_detail_id');

                $r2Done = DB::table('opname_assignments')
                    ->where('session_id', $sessionId)
                    ->whereIn('team_id', $teamR2Ids)
                    ->where('status', 'COMPLETED')
                    ->distinct('reference_detail_id')
                    ->count('reference_detail_id');
            }
            
            // 5. My Performance (Individual)
            $myCountedItems = DB::table('opname_counts')
                ->where('session_id', $sessionId)
                ->where('counted_by', $user->id)
                ->count();
                
            $myCountedBins = DB::table('opname_counts')
                ->join('opname_reference_details', 'opname_counts.reference_detail_id', '=', 'opname_reference_details.id')
                ->where('opname_counts.session_id', $sessionId)
                ->where('opname_counts.counted_by', $user->id)
                ->distinct('opname_reference_details.bin_code')
                ->count('opname_reference_details.bin_code');
                
            return response()->json([
                'success' => true,
                'data' => [
                    'session' => [
                        'id' => $sessionId,
                        'no_session' => $sessionNo,
                        'wms_ref' => $wmsRef,
                        'active_principals' => $activePrincipals,
                    ],
                    'bins' => [
                        'total' => $totalBins,
                        'completed_a' => $binsCompletedByA,
                        'gap_a' => $gapA,
                        'completed_b' => $binsCompletedByB,
                        'gap_b' => $gapB,
                        'match' => $binsMatch,
                        'recount' => $binsRecount,
                    ],
                    'items' => [
                        'total_wms_sku' => $totalWmsSkus,
                        'add_items' => $addItems,
                    ],
                    'recount' => [
                        'r1_assigned' => $r1Assigned,
                        'r1_done' => $r1Done,
                        'r2_assigned' => $r2Assigned,
                        'r2_done' => $r2Done,
                    ],
                    'my_performance' => [
                        'total_scanned_items' => $myCountedItems,
                        'total_scanned_bins' => $myCountedBins,
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
