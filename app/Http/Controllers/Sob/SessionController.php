<?php

namespace App\Http\Controllers\Sob;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SobOpnameSession;
use App\Models\SobZone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    public function index()
    {
        $sessions = SobOpnameSession::with(['creator'])
            ->withCount(['assignments', 'sweepHeaders', 'tagResults'])
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('sob.sessions.index', compact('sessions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'session_name' => 'required|string|max:150',
            'session_date' => 'required|date',
            'branch_code' => 'required|string|max:50',
            'count_mode' => 'required|in:SINGLE,DOUBLE',
            'notes' => 'nullable|string',
        ]);

        $prefix = 'SOB-' . date('Ym', strtotime($request->session_date)) . '-';
        $lastSession = SobOpnameSession::where('session_code', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNum = 1;
        if ($lastSession) {
            $lastNum = (int) str_replace($prefix, '', $lastSession->session_code);
            $nextNum = $lastNum + 1;
        }
        $sessionCode = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        SobOpnameSession::create([
            'branch_code' => strtoupper($request->branch_code),
            'session_code' => $sessionCode,
            'session_name' => $request->session_name,
            'session_date' => $request->session_date,
            'count_mode' => $request->count_mode,
            'status' => 'DRAFT',
            'notes' => $request->notes,
            'created_by' => Auth::id() ?? 1,
        ]);

        return redirect()->route('sob.sessions.index')->with('success', "Sesi $sessionCode berhasil dibuat.");
    }

    public function start($id)
    {
        $session = SobOpnameSession::findOrFail($id);
        $session->update(['status' => 'ACTIVE']);
        return redirect()->back()->with('success', "Sesi {$session->session_code} telah diaktifkan.");
    }

    public function close($id)
    {
        $session = SobOpnameSession::findOrFail($id);
        $session->update(['status' => 'CLOSED']);
        return redirect()->back()->with('success', "Sesi {$session->session_code} telah ditutup.");
    }

    public function destroy($id)
    {
        $session = SobOpnameSession::findOrFail($id);
        $session->delete();
        return redirect()->back()->with('success', "Sesi {$session->session_code} berhasil dihapus.");
    }
}
