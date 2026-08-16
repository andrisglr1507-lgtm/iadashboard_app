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
            
            // 1. Session Info
            $sessionNo = $activeSession->session_name ?? 'Session ' . $sessionId;
            $wmsRef = "WMS-" . $sessionId; // Placeholder
            
            // Get Active Principals dari wms reference atau tabel produk jika ada
            $activePrincipals = DB::table('opname_reference_details')
                ->join('master_products', 'opname_reference_details.id_product', '=', 'master_products.id_product')
                ->where('opname_reference_details.session_id', $sessionId)
                ->whereNotNull('master_products.principal')
                ->distinct()
                ->pluck('master_products.principal')
                ->toArray();
                
            // 2. Bin Stats
            $totalBins = DB::table('opname_reference_details')
                ->where('session_id', $sessionId)
                ->distinct('bin_code')
                ->count('bin_code');
                
            $binsCompletedByA = DB::table('opname_counts')
                ->join('opname_reference_details', 'opname_counts.reference_detail_id', '=', 'opname_reference_details.id')
                ->where('opname_counts.session_id', $sessionId)
                ->where('opname_counts.team_id', 'like', 'A%')
                ->distinct('opname_reference_details.bin_code')
                ->count('opname_reference_details.bin_code');
                
            $binsCompletedByB = DB::table('opname_counts')
                ->join('opname_reference_details', 'opname_counts.reference_detail_id', '=', 'opname_reference_details.id')
                ->where('opname_counts.session_id', $sessionId)
                ->where('opname_counts.team_id', 'like', 'B%')
                ->distinct('opname_reference_details.bin_code')
                ->count('opname_reference_details.bin_code');
                
            $binsCompletedBoth = DB::table('opname_results')
                ->join('opname_reference_details', 'opname_results.reference_detail_id', '=', 'opname_reference_details.id')
                ->where('opname_results.session_id', $sessionId)
                ->whereNotNull('opname_results.team_a_qty')
                ->whereNotNull('opname_results.team_b_qty')
                ->distinct('opname_reference_details.bin_code')
                ->count('opname_reference_details.bin_code');
                
            $binsOnlyA = max(0, $binsCompletedByA - $binsCompletedBoth);
            $binsOnlyB = max(0, $binsCompletedByB - $binsCompletedBoth);
            
            // 3. Item Stats
            $totalWmsSkus = DB::table('opname_reference_details')
                ->where('session_id', $sessionId)
                ->count();
                
            $addItems = DB::table('opname_counts')
                ->where('session_id', $sessionId)
                ->whereNull('reference_detail_id') // Item yang tidak ada di wms reference
                ->count();
                
            $itemsCountedByA = DB::table('opname_counts')
                ->where('session_id', $sessionId)
                ->where('team_id', 'like', 'A%')
                ->distinct('reference_detail_id')
                ->count('reference_detail_id');
                
            $itemsCountedByB = DB::table('opname_counts')
                ->where('session_id', $sessionId)
                ->where('team_id', 'like', 'B%')
                ->distinct('reference_detail_id')
                ->count('reference_detail_id');
                
            $gapA = max(0, $totalWmsSkus - $itemsCountedByA);
            $gapB = max(0, $totalWmsSkus - $itemsCountedByB);
            
            // 4. Recount Stats
            $recountAssigned = DB::table('opname_recount_assignments')
                ->where('session_id', $sessionId)
                ->count();
                
            $recountDone = DB::table('opname_recount_assignments')
                ->where('session_id', $sessionId)
                ->where('status', 'COMPLETED')
                ->count();
                
            $recountPending = max(0, $recountAssigned - $recountDone);
            
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
                        'completed_both' => $binsCompletedBoth,
                        'completed_a' => $binsCompletedByA,
                        'completed_b' => $binsCompletedByB,
                        'only_a' => $binsOnlyA,
                        'only_b' => $binsOnlyB,
                    ],
                    'items' => [
                        'total_wms_sku' => $totalWmsSkus,
                        'add_items' => $addItems,
                        'gap_a' => $gapA,
                        'gap_b' => $gapB,
                    ],
                    'recount' => [
                        'assigned' => $recountAssigned,
                        'done' => $recountDone,
                        'pending' => $recountPending,
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
