<?php

namespace App\Http\Controllers\Sodc\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpnameSession;
use App\Models\OpnameAssignment;
use App\Models\OpnameReferenceDetail;
use App\Models\OpnameTeam;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    public function index()
    {
        // Get the latest session that is either DRAFT or ACTIVE
        $session = OpnameSession::whereIn('status', ['DRAFT', 'ACTIVE'])->orderBy('id', 'desc')->first();
        $warehouses = [];
        $users = \App\Models\User::where('is_active', true)->get();
        $assignmentsMap = [];

        if ($session) {
            // Get unique warehouses and aisles for this session
            $warehouses = DB::table('opname_reference_details')
                ->leftJoin('bins', 'opname_reference_details.bin_code', '=', 'bins.bin_code')
                ->where('opname_reference_details.reference_id', $session->reference_id)
                ->select(
                    DB::raw('COALESCE(bins.warehouse_id, "UNKNOWN") as warehouse_id'), 
                    DB::raw('COALESCE(bins.zone, "UNKNOWN") as zone'), 
                    DB::raw('COALESCE(bins.level, "UNKNOWN") as level'), 
                    DB::raw('COALESCE(bins.ganjil_genap, "UNKNOWN") as ganjil_genap'), 
                    DB::raw('COALESCE(bins.aisle, "ALL") as aisle'),
                    DB::raw('COUNT(DISTINCT opname_reference_details.bin_code) as total_bins'), 
                    DB::raw('COUNT(opname_reference_details.id) as total_sku')
                )
                ->groupBy('warehouse_id', 'zone', 'level', 'ganjil_genap', 'aisle')
                ->get();
                
            $currentAssignments = \App\Models\OpnameUserArea::where('session_id', $session->id)->with('user')->get();
            foreach ($currentAssignments as $asg) {
                $key = $asg->warehouse_id . '_' . ($asg->aisle ?? 'ALL');
                if (!isset($assignmentsMap[$key])) {
                    $assignmentsMap[$key] = ['TEAM_A' => [], 'TEAM_B' => []];
                }
                $assignmentsMap[$key][$asg->team_role][] = $asg;
            }
        }
        
        return view('sodc.opname_management.assignments.index', compact('session', 'warehouses', 'users', 'assignmentsMap'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:opname_sessions,id',
            'warehouse_id' => 'required|string',
            'aisle' => 'required|string',
            'team_a_users' => 'nullable|array',
            'team_b_users' => 'nullable|array'
        ]);

        $sessionId = $request->session_id;
        $warehouseId = $request->warehouse_id;
        $aisle = $request->aisle == 'ALL' ? null : $request->aisle;

        // Delete existing assignments for this area in this session to replace them
        \App\Models\OpnameUserArea::where('session_id', $sessionId)
            ->where('warehouse_id', $warehouseId)
            ->where('aisle', $aisle)
            ->delete();

        $inserts = [];

        if ($request->team_a_users) {
            foreach ($request->team_a_users as $userId) {
                // Jangan hapus penugasan di lorong lain, karena 1 user bisa pegang banyak lorong
                
                $inserts[] = [
                    'session_id' => $sessionId,
                    'warehouse_id' => $warehouseId,
                    'aisle' => $aisle,
                    'user_id' => $userId,
                    'team_role' => 'TEAM_A',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        if ($request->team_b_users) {
            foreach ($request->team_b_users as $userId) {
                // Jangan hapus penugasan di lorong lain, karena 1 user bisa pegang banyak lorong
                
                $inserts[] = [
                    'session_id' => $sessionId,
                    'warehouse_id' => $warehouseId,
                    'aisle' => $aisle,
                    'user_id' => $userId,
                    'team_role' => 'TEAM_B',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        if (!empty($inserts)) {
            \App\Models\OpnameUserArea::insert($inserts);
        }

        return redirect()->route('sodc.assignments.index')->with('success', 'Assignment berhasil disimpan untuk Gudang ' . $warehouseId . ($aisle ? ' Lorong ' . $aisle : ''));
    }
}