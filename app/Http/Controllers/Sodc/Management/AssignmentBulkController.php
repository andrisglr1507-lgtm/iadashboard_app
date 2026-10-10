<?php

namespace App\Http\Controllers\Sodc\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpnameSession;
use Illuminate\Support\Facades\DB;

class AssignmentBulkController extends Controller
{
    public function index()
    {
        $session = OpnameSession::whereIn('status', ['DRAFT', 'ACTIVE'])->orderBy('id', 'desc')->first();
        $aisles = [];
        $users = \App\Models\User::where('is_active', true)->get();
        $assignmentsMap = []; 

        if ($session) {
            $rawDetails = DB::table('opname_reference_details')
                ->leftJoin('bins', 'opname_reference_details.bin_code', '=', 'bins.bin_code')
                ->leftJoin('warehouses', 'bins.warehouse_id', '=', 'warehouses.id')
                ->where('opname_reference_details.reference_id', $session->reference_id)
                ->select(
                    'opname_reference_details.id as sku_id',
                    'opname_reference_details.bin_code as detail_bin_code',
                    'bins.warehouse_id', 
                    'warehouses.warehouse_code',
                    'bins.ganjil_genap'
                )
                ->get();

            $grouped = [];
            $breakdown = request('breakdown', 'warehouse'); // warehouse, zone, ganjil_genap

            foreach ($rawDetails as $detail) {
                $whId = $detail->warehouse_id ?? 'UNKNOWN';
                $whCode = $detail->warehouse_code ?? 'UNKNOWN';
                
                $parts = explode('.', $detail->detail_bin_code);
                $zone = isset($parts[0]) ? $parts[0] : 'UNKNOWN';
                $ganjilGenap = $detail->ganjil_genap ?: 'UNKNOWN';

                if ($breakdown === 'ganjil_genap') {
                    $assignKey = 'ZONE_' . $zone . '_' . $ganjilGenap;
                    $displayLevel = 'Zone: ' . $zone . ' (' . $ganjilGenap . ')';
                } elseif ($breakdown === 'zone') {
                    $assignKey = 'ZONE_' . $zone;
                    $displayLevel = 'Zone: ' . $zone;
                } else {
                    $assignKey = 'ALL';
                    $displayLevel = 'ALL ZONES';
                }

                $groupKey = $whId . '|' . $assignKey;

                if (!isset($grouped[$groupKey])) {
                    $grouped[$groupKey] = (object)[
                        'warehouse_id' => $whId,
                        'warehouse_code' => $whCode,
                        'assign_key' => $assignKey,
                        'display_level' => $displayLevel,
                        'unique_bins' => [],
                        'total_bins' => 0,
                        'total_sku' => 0
                    ];
                }

                $grouped[$groupKey]->unique_bins[$detail->detail_bin_code] = true;
                $grouped[$groupKey]->total_sku += 1;
            }

            foreach ($grouped as $key => $g) {
                $g->total_bins = count($g->unique_bins);
                unset($g->unique_bins); 
                $aisles[] = $g;
            }

            usort($aisles, function($a, $b) {
                if ($a->warehouse_code != $b->warehouse_code) return strcmp($a->warehouse_code, $b->warehouse_code);
                return strcmp($a->assign_key, $b->assign_key);
            });
                
            $currentAssignments = \App\Models\OpnameUserArea::where('session_id', $session->id)->with('user')->get();
            foreach ($currentAssignments as $asg) {
                if ($asg->team_role === 'TEAM_RECOUNT') continue;
                $key = $asg->warehouse_id . '|' . $asg->aisle;
                if (!isset($assignmentsMap[$key])) {
                    $assignmentsMap[$key] = ['TEAM_A' => [], 'TEAM_B' => []];
                }
                $assignmentsMap[$key][$asg->team_role][] = $asg;
            }
        }
        
        return view('sodc.opname_management.assignments_bulk.index', compact('session', 'aisles', 'users', 'assignmentsMap'));
    }

    public function store(Request $request)
    {
            $request->validate([
            'session_id' => 'required|exists:opname_sessions,id',
            'selected_aisles' => 'required|array',
            'team_a_users' => 'nullable|array',
            'team_b_users' => 'nullable|array'
        ]);

        $sessionId = $request->session_id;
        $inserts = [];

        foreach ($request->selected_aisles as $key) {
            list($warehouseId, $assignKey) = explode('|', $key);

            \App\Models\OpnameUserArea::where('session_id', $sessionId)
                ->where('warehouse_id', $warehouseId)
                ->where('aisle', $assignKey)
                ->delete();

            if ($request->team_a_users) {
                foreach ($request->team_a_users as $userId) {
                    $inserts[] = [
                        'session_id' => $sessionId,
                        'warehouse_id' => $warehouseId,
                        'aisle' => $assignKey,
                        'user_id' => $userId,
                        'team_role' => 'TEAM_A',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }

            if ($request->team_b_users) {
                foreach ($request->team_b_users as $userId) {
                    $inserts[] = [
                        'session_id' => $sessionId,
                        'warehouse_id' => $warehouseId,
                        'aisle' => $assignKey,
                        'user_id' => $userId,
                        'team_role' => 'TEAM_B',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }
        }

        if (!empty($inserts)) {
            \App\Models\OpnameUserArea::insert($inserts);
        }

        return redirect()->route('sodc.assignments_bulk.index')->with('success', 'Berhasil melakukan bulk assignment ke Lorong terpilih!');
    }
}
