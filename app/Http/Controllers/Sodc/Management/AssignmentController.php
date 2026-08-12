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
        $bins = [];
        $teams = OpnameTeam::where('is_active', true)->get();
        $assignmentsMap = [];

        if ($session) {
            // Get unique bins for this session
            $bins = DB::table('opname_reference_details')
                ->where('reference_id', $session->reference_id)
                ->select('bin_code', DB::raw('count(id) as total_sku'))
                ->groupBy('bin_code')
                ->get();

            // Get current assignments
            $assignments = OpnameAssignment::where('session_id', $session->id)->get();
            foreach ($assignments as $asg) {
                // To group by bin, we need to know the bin code of the reference detail
                // Since an assignment is tied to a reference detail, we group by detail's bin
                // But for simplicity in UI, if a team is assigned to ONE detail in a bin, we assume they are assigned to the whole BIN.
                // It's better to store bin_code directly in assignment if we assign per bin. 
                // But schema has reference_detail_id. So we just map the team to the bin.
            }
        }

        // Wait, if assignment is per reference_detail_id, assigning a whole bin means creating assignments for EVERY sku in that bin.
        // Let's pass the bins to the view
        
        return view('sodc.opname_management.assignments.index', compact('session', 'bins', 'teams'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:opname_sessions,id',
            'bin_code' => 'required|string',
            'team_id' => 'required|exists:opname_teams,id'
        ]);

        $session = OpnameSession::findOrFail($request->session_id);
        
        // Find all reference details for this bin
        $details = OpnameReferenceDetail::where('reference_id', $session->reference_id)
            ->where('bin_code', $request->bin_code)
            ->get();

        if ($details->isEmpty()) {
            return redirect()->back()->with('error', 'Bin tidak ditemukan di WMS Referensi.');
        }

        // Assign this team to all SKUs in this bin
        foreach ($details as $detail) {
            // Check if already assigned to avoid duplicates
            $exists = OpnameAssignment::where('session_id', $session->id)
                ->where('team_id', $request->team_id)
                ->where('reference_detail_id', $detail->id)
                ->exists();

            if (!$exists) {
                OpnameAssignment::create([
                    'assignment_uuid' => Str::uuid(),
                    'session_id' => $session->id,
                    'team_id' => $request->team_id,
                    'reference_detail_id' => $detail->id,
                    'status' => 'ASSIGNED',
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now()
                ]);
            }
        }

        return redirect()->route('sodc.assignments.index')->with('success', 'Bin ' . $request->bin_code . ' berhasil ditugaskan ke tim.');
    }
}