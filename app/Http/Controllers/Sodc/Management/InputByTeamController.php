<?php

namespace App\Http\Controllers\Sodc\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OpnameSession;

class InputByTeamController extends Controller
{
    public function index(Request $request)
    {
        $activeSessionId = session('active_opname_session_id');
        $activeSession = null;
        if ($activeSessionId) {
            $activeSession = OpnameSession::find($activeSessionId);
        } else {
            $activeSession = OpnameSession::where('status', 'ACTIVE')->first();
            if ($activeSession) {
                session(['active_opname_session_id' => $activeSession->id]);
                $activeSessionId = $activeSession->id;
            }
        }

        $query = DB::table('opname_counts')
            ->leftJoin('users', 'opname_counts.counted_by', '=', 'users.id')
            ->leftJoin('opname_reference_details as rd', 'opname_counts.reference_detail_id', '=', 'rd.id')
            ->leftJoin('products', 'rd.sku_code', '=', 'products.sku_code')
            ->select(
                'opname_counts.*',
                'users.full_name as counter_name',
                'rd.bin_code',
                'rd.sku_code',
                'products.sku_name'
            );

        if ($activeSessionId) {
            $query->where('opname_counts.session_id', $activeSessionId);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('users.full_name', 'like', "%{$search}%")
                  ->orWhere('opname_counts.team_id', 'like', "%{$search}%")
                  ->orWhere('rd.bin_code', 'like', "%{$search}%")
                  ->orWhere('rd.sku_code', 'like', "%{$search}%");
            });
        }

        $data = $query->orderBy('opname_counts.counted_at', 'desc')->paginate(20);
        $data->appends($request->all());

        return view('sodc.management.input_by_team.index', compact('data', 'activeSession'));
    }
}
