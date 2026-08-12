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
        $zones = [];
        $teams = OpnameTeam::where('is_active', true)->get();

        if ($session) {
            // Group by Zone instead of Bin
            $zones = DB::table('opname_reference_details')
                ->leftJoin('bins', 'opname_reference_details.bin_code', '=', 'bins.bin_code')
                ->where('opname_reference_details.reference_id', $session->reference_id)
                ->select(
                    DB::raw('COALESCE(bins.zone, "UNKNOWN") as zone'), 
                    DB::raw('COUNT(DISTINCT opname_reference_details.bin_code) as total_bins'), 
                    DB::raw('COUNT(opname_reference_details.id) as total_sku')
                )
                ->groupBy('zone')
                ->get();
        }
        
        return view('sodc.opname_management.assignments.index', compact('session', 'zones', 'teams'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:opname_sessions,id',
            'zone' => 'required|string',
            'team_id' => 'required|exists:opname_teams,id'
        ]);

        $session = OpnameSession::findOrFail($request->session_id);
        
        // Find all reference details for this zone
        $details = DB::table('opname_reference_details')
            ->leftJoin('bins', 'opname_reference_details.bin_code', '=', 'bins.bin_code')
            ->where('opname_reference_details.reference_id', $session->reference_id)
            ->where(function ($query) use ($request) {
                if ($request->zone === 'UNKNOWN') {
                    $query->whereNull('bins.zone');
                } else {
                    $query->where('bins.zone', $request->zone);
                }
            })
            ->select('opname_reference_details.id')
            ->get();

        if ($details->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data referensi di Zona ini.');
        }

        // Assign this team to all SKUs in this zone
        $inserts = [];
        foreach ($details as $detail) {
            // Check if already assigned to avoid duplicates
            $exists = OpnameAssignment::where('session_id', $session->id)
                ->where('team_id', $request->team_id)
                ->where('reference_detail_id', $detail->id)
                ->exists();

            if (!$exists) {
                $inserts[] = [
                    'assignment_uuid' => Str::uuid()->toString(),
                    'session_id' => $session->id,
                    'team_id' => $request->team_id,
                    'reference_detail_id' => $detail->id,
                    'status' => 'ASSIGNED',
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        // Bulk insert for performance
        if (!empty($inserts)) {
            OpnameAssignment::insert($inserts);
        }

        return redirect()->route('sodc.assignments.index')->with('success', 'Zona ' . $request->zone . ' berhasil ditugaskan ke tim.');
    }
}