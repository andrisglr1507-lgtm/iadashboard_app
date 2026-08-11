<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GlobalContextController extends Controller
{
    /**
     * Set the active session ID in Laravel's HTTP session.
     */
    public function setActiveSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        // Save the chosen session_id to the global session context
        session(['active_opname_session_id' => $request->session_id]);

        return redirect()->back()->with('success', 'Sesi aktif berhasil diubah.');
    }
}
