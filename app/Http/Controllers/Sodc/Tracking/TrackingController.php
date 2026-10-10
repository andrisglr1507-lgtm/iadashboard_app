<?php

namespace App\Http\Controllers\Sodc\Tracking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpnameSession;
use App\Models\OpnameReferenceDetail;

class TrackingController extends Controller
{
    public function index(Request $request)
    {
        // 1. Dapatkan active session
        $activeSessionId = session('active_opname_session_id');
        if (!$activeSessionId) {
            $activeSession = OpnameSession::where('status', 'ACTIVE')->orderBy('id', 'desc')->first();
            if ($activeSession) {
                $activeSessionId = $activeSession->id;
                session(['active_opname_session_id' => $activeSessionId]);
            }
        }

        $session = null;
        if ($activeSessionId) {
            $session = OpnameSession::find($activeSessionId);
        }

        if (!$session) {
            return view('sodc.tracking.index', ['data' => null, 'session' => null, 'summary' => null]);
        }

        // 2. Query dari opname_reference_details (WMS reference)
        $query = OpnameReferenceDetail::where('opname_reference_details.reference_id', $session->reference_id)
            ->leftJoin('opname_results', function ($join) {
                $join->on('opname_reference_details.id', '=', 'opname_results.reference_detail_id');
            })
            ->leftJoin('products', 'opname_reference_details.sku_code', '=', 'products.sku_code')
            ->select(
                'opname_reference_details.*',
                'opname_results.team_a_qty',
                'opname_results.team_b_qty',
                'opname_results.recount1_qty',
                'opname_results.recount2_qty',
                'opname_results.recount3_qty',
                'opname_results.final_qty',
                'opname_results.result_status',
                'products.sku_name',
                'products.uom',
                'products.packname',
                'products.barcode',
                'products.principal'
            );

        // Filter search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('opname_reference_details.bin_code', 'like', "%{$search}%")
                  ->orWhere('opname_reference_details.sku_code', 'like', "%{$search}%")
                  ->orWhere('products.sku_name', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($request->has('status') && $request->status != '') {
            $status = $request->status;
            if ($status == 'BELUM') {
                $query->whereNull('opname_results.id');
            } elseif ($status == 'TEAM_A_ONLY') {
                $query->whereNotNull('opname_results.team_a_qty')
                      ->whereNull('opname_results.team_b_qty');
            } elseif ($status == 'TEAM_B_ONLY') {
                $query->whereNotNull('opname_results.team_b_qty')
                      ->whereNull('opname_results.team_a_qty');
            } elseif ($status == 'MISMATCH') {
                $query->whereNotNull('opname_results.team_a_qty')
                      ->whereNotNull('opname_results.team_b_qty')
                      ->whereNotIn('opname_results.result_status', ['MATCH', 'FINAL']);
            } elseif ($status == 'MATCH_FINAL') {
                $query->whereIn('opname_results.result_status', ['MATCH', 'FINAL']);
            }
        }

        $data = $query->orderBy('opname_reference_details.bin_code', 'asc')->paginate(15);

        // Hitung total untuk summary
        $summary = [
            'total' => OpnameReferenceDetail::where('reference_id', $session->reference_id)->count(),
            'belum' => OpnameReferenceDetail::where('opname_reference_details.reference_id', $session->reference_id)
                            ->leftJoin('opname_results', 'opname_reference_details.id', '=', 'opname_results.reference_detail_id')
                            ->whereNull('opname_results.id')
                            ->count(),
            'team_a_only' => \App\Models\OpnameResult::where('session_id', $session->id)
                            ->whereNotNull('team_a_qty')->whereNull('team_b_qty')->count(),
            'match_final' => \App\Models\OpnameResult::where('session_id', $session->id)
                            ->whereIn('result_status', ['MATCH', 'FINAL'])->count(),
        ];

        return view('sodc.tracking.index', compact('data', 'session', 'summary'));
    }
}
