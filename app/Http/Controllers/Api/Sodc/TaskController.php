<?php

namespace App\Http\Controllers\Api\Sodc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpnameSession;
use App\Models\OpnameAssignment;
use App\Models\OpnameReferenceDetail;
use App\Models\OpnameTeamMember;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Get tasks (assignments) for the currently authenticated user.
     * Includes list of Target Principals for the active session.
     */
    public function myTasks(Request $request)
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

        // 2. Determine target principals dynamically from the WMS reference upload
        $targetPrincipals = DB::table('opname_reference_details')
            ->join('products', 'opname_reference_details.product_id', '=', 'products.id')
            ->where('opname_reference_details.reference_id', $activeSession->reference_id)
            ->whereNotNull('products.principal')
            ->distinct()
            ->pluck('products.principal')
            ->toArray();

        // 3. Find the user's team in the active session context
        // Assuming a user can belong to a team via opname_team_members
        $teamMember = OpnameTeamMember::where('user_id', $user->id)->first();
        
        $assignedBins = [];

        if ($teamMember) {
            $teamId = $teamMember->team_id;

            // Get assignments for this team in this session
            $assignments = OpnameAssignment::with(['referenceDetail.product', 'referenceDetail.bin'])
                ->where('session_id', $activeSession->id)
                ->where('team_id', $teamId)
                ->get();

            // Group assignments by bin_code
            $groupedByBin = $assignments->groupBy(function ($item) {
                return $item->referenceDetail->bin_code ?? 'UNKNOWN';
            });

            foreach ($groupedByBin as $binCode => $items) {
                $expectedItems = $items->map(function ($assignment) {
                    $detail = $assignment->referenceDetail;
                    return [
                        'id_product' => $detail->product->id_product ?? $detail->sku_code,
                        'product_name' => $detail->product->product_name ?? 'Unknown Product',
                        'uom' => $detail->product->uom ?? 'PCS',
                        'assignment_id' => $assignment->id,
                        'reference_detail_id' => $detail->id,
                    ];
                })->values()->toArray();

                $assignedBins[] = [
                    'bin_code' => $binCode,
                    'status' => 'PENDING', // You can compute this based on opname_counts table if needed
                    'expected_items' => $expectedItems
                ];
            }
        } else {
            // For testing: If user has no team, we can either return empty or return all bins (if admin)
            // Let's just return empty for strict logic
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil tugas.',
            'data' => [
                'session_id' => $activeSession->id,
                'session_code' => $activeSession->session_code,
                'mode' => $activeSession->mode,
                'target_principals' => $targetPrincipals,
                'assigned_bins' => $assignedBins
            ]
        ]);
    }
}
