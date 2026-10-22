<?php

namespace App\Http\Controllers\Sob;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SobSweepHeader;
use App\Models\SobOpnameSession;

class SweepHeaderController extends Controller
{
    public function index(Request $request)
    {
        $activeSessionId = $request->get('session_id');
        $sessions = SobOpnameSession::orderBy('id', 'desc')->get();

        if (!$activeSessionId && $sessions->isNotEmpty()) {
            $activeSessionId = $sessions->first()->id;
        }

        $headers = SobSweepHeader::with(['session', 'zone', 'teamAUser', 'teamBUser'])
            ->withCount('counts')
            ->when($activeSessionId, function ($q) use ($activeSessionId) {
                $q->where('session_id', $activeSessionId);
            })
            ->orderBy('id', 'desc')
            ->paginate(25);

        return view('sob.sweep_headers.index', compact('headers', 'sessions', 'activeSessionId'));
    }

    public function show($id)
    {
        $header = SobSweepHeader::with(['session', 'zone', 'teamAUser', 'teamBUser', 'counts.counter', 'counts.product', 'tagResults.product'])
            ->findOrFail($id);

        return view('sob.sweep_headers.show', compact('header'));
    }
}
