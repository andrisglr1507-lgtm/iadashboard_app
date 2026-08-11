<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OpnameRecountAssignment;
use App\Models\MasterProduct;
use App\Models\User;
use App\Models\OpnameSession;

class AssignmentController extends Controller
{
    /**
     * Display the assignment list for the active session.
     */
    public function index()
    {
        $activeSessionId = session('active_opname_session_id');
        $error = null;
        $assignments = collect();
        $stats = [
            'total' => 0,
            'assigned' => 0,
            'distributed' => 0,
            'in_progress' => 0,
            'completed' => 0,
        ];

        if (!$activeSessionId) {
            $error = "Belum ada sesi terpilih. Silakan pilih Sesi Aktif pada Header di pojok kanan atas.";
            return view('single_mode.assignments', compact('error', 'assignments', 'stats', 'activeSessionId'));
        }

        // Validate session exists and is Single mode
        $session = OpnameSession::where('session_id', $activeSessionId)->first();
        if (!$session || !in_array($session->mode, ['S', 'single'])) {
            $error = "Session tidak ditemukan atau bukan mode Single.";
            return view('single_mode.assignments', compact('error', 'assignments', 'stats', 'activeSessionId'));
        }

        // Fetch assignments with related data
        $assignments = OpnameRecountAssignment::where('opname_recount_assignments.session_id', $activeSessionId)
            ->leftJoin('user as assignee', 'opname_recount_assignments.assigned_to', '=', 'assignee.user_id')
            ->leftJoin('user as assigner', 'opname_recount_assignments.assigned_by', '=', 'assigner.id')
            ->leftJoin('master_products as p', 'opname_recount_assignments.id_product', '=', 'p.id_product')
            ->select(
                'opname_recount_assignments.*',
                'assignee.name as assignee_name',
                'assigner.name as assigner_name',
                'p.product_name',
                'p.principal',
                'p.packname'
            )
            ->orderBy('opname_recount_assignments.assigned_at', 'desc')
            ->get();

        // Build stats
        $stats = [
            'total' => $assignments->count(),
            'assigned' => $assignments->where('status', 'assigned')->count(),
            'distributed' => $assignments->where('status', 'distributed')->count(),
            'in_progress' => $assignments->where('status', 'in_progress')->count(),
            'completed' => $assignments->where('status', 'completed')->count(),
        ];

        return view('single_mode.assignments', compact('error', 'assignments', 'stats', 'activeSessionId'));
    }
}
