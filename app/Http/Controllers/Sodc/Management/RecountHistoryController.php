<?php

namespace App\Http\Controllers\Sodc\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpnameSession;
use Illuminate\Support\Facades\DB;

class RecountHistoryController extends Controller
{
    public function index(Request $request)
    {
        // 1. Dapatkan active session
        if ($request->has('session_id') && $request->session_id != '') {
            session(['active_opname_session_id' => $request->session_id]);
        }
        
        $activeSessionId = session('active_opname_session_id');
        $activeSessions = OpnameSession::where('status', 'ACTIVE')->orderBy('id', 'desc')->get();
        
        if (!$activeSessionId && $activeSessions->count() > 0) {
            $activeSessionId = $activeSessions->first()->id;
            session(['active_opname_session_id' => $activeSessionId]);
        }

        if (!$activeSessionId) {
            return view('sodc.recount_history.index', [
                'error' => 'Belum ada sesi opname yang aktif.',
                'activeSessions' => $activeSessions,
                'activeSessionId' => null,
                'data' => null
            ]);
        }

        // Query dari opname_recount_assignments
        $query = DB::table('opname_recount_assignments')
            ->where('session_id', $activeSessionId)
            ->leftJoin('users as u_to', 'opname_recount_assignments.assigned_to', '=', 'u_to.id')
            ->leftJoin('users as u_by', 'opname_recount_assignments.assigned_by', '=', 'u_by.id')
            ->leftJoin('products', 'opname_recount_assignments.id_product', '=', 'products.sku_code')
            ->select(
                'opname_recount_assignments.*',
                'u_to.name as assigned_to_name',
                'u_by.name as assigned_by_name',
                'products.sku_name as product_name'
            );

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('opname_recount_assignments.location_code', 'like', "%{$search}%")
                  ->orWhere('opname_recount_assignments.id_product', 'like', "%{$search}%")
                  ->orWhere('products.sku_name', 'like', "%{$search}%")
                  ->orWhere('u_to.name', 'like', "%{$search}%");
            });
        }

        $data = $query->orderBy('opname_recount_assignments.assignment_id', 'desc')->paginate(15);

        return view('sodc.recount_history.index', compact('data', 'activeSessionId', 'activeSessions'));
    }
}
