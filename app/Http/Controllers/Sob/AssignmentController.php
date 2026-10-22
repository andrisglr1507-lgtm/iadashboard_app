<?php

namespace App\Http\Controllers\Sob;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SobAssignment;
use App\Models\SobOpnameSession;
use App\Models\SobZone;
use App\Models\User;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $activeSessionId = $request->get('session_id');
        $sessions = SobOpnameSession::orderBy('id', 'desc')->get();

        if (!$activeSessionId && $sessions->isNotEmpty()) {
            $activeSessionId = $sessions->first()->id;
        }

        $assignments = SobAssignment::with(['session', 'zone', 'user'])
            ->when($activeSessionId, function ($q) use ($activeSessionId) {
                $q->where('session_id', $activeSessionId);
            })
            ->orderBy('id', 'desc')
            ->paginate(20);

        $zones = SobZone::where('is_active', true)->get();
        $users = User::orderBy('name')->get();

        return view('sob.assignments.index', compact('assignments', 'sessions', 'activeSessionId', 'zones', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:sob_opname_sessions,id',
            'zone_id' => 'required|exists:sob_zones,id',
            'user_id' => 'required|exists:users,id',
            'team_role' => 'required|in:SINGLE,TEAM_A,TEAM_B,RECOUNT',
        ]);

        SobAssignment::create([
            'session_id' => $request->session_id,
            'zone_id' => $request->zone_id,
            'user_id' => $request->user_id,
            'team_role' => $request->team_role,
            'status' => 'ASSIGNED',
        ]);

        return redirect()->back()->with('success', 'Petugas berhasil ditugaskan ke area.');
    }

    public function destroy($id)
    {
        $assignment = SobAssignment::findOrFail($id);
        $assignment->delete();
        return redirect()->back()->with('success', 'Penugasan petugas berhasil dihapus.');
    }
}
