<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OpnameSession;
use App\Models\OpnameReferenceDetail;
use App\Models\OpnameResult;
use App\Models\OpnameRecountAssignment;
use App\Models\OpnameCount;

class DashboardController extends Controller
{
    public function index()
    {
        $activeSessionId = session('active_opname_session_id');
        if (!$activeSessionId) {
            $activeSession = OpnameSession::where('status', 'ACTIVE')->orderBy('id', 'desc')->first();
            if ($activeSession) {
                $activeSessionId = $activeSession->id;
                session(['active_opname_session_id' => $activeSessionId]);
            }
        }

        $session = OpnameSession::find($activeSessionId);

        $totalSku = 0;
        $skuCounted = 0;
        $skuVariance = 0;
        $teamAProgress = 0;
        $teamBProgress = 0;
        $recountProgress = 0;
        $topVariances = collect();

        if ($session) {
            $totalSku = OpnameReferenceDetail::where('reference_id', $session->reference_id)->count();
            
            $skuCounted = OpnameResult::where('session_id', $session->id)
                ->where('result_status', '!=', 'UNCOUNTED')
                ->count();
                
            $skuVariance = OpnameResult::where('session_id', $session->id)
                ->where('result_status', 'RECOUNT')
                ->count();

            // Progress Tim A & Tim B
            $teamACount = OpnameResult::where('session_id', $session->id)->whereNotNull('team_a_qty')->count();
            $teamBCount = OpnameResult::where('session_id', $session->id)->whereNotNull('team_b_qty')->count();
            
            $teamAProgress = $totalSku > 0 ? ($teamACount / $totalSku) * 100 : 0;
            $teamBProgress = $totalSku > 0 ? ($teamBCount / $totalSku) * 100 : 0;

            // Progress Recount
            $totalRecountAssignments = OpnameRecountAssignment::where('session_id', $session->id)->count();
            $doneRecountAssignments = OpnameRecountAssignment::where('session_id', $session->id)
                ->where('status', '!=', 'PENDING')
                ->count();
            $recountProgress = $totalRecountAssignments > 0 ? ($doneRecountAssignments / $totalRecountAssignments) * 100 : 0;

            // Top Variances (Selisih Terbesar antara System dan Team A)
            // Pakai Raw query sederhana untuk sqlite/mysql compatibility
            $topVariances = OpnameResult::with(['referenceDetail.product'])
                ->where('session_id', $session->id)
                ->whereNotNull('team_a_qty')
                ->get()
                ->map(function ($item) {
                    $item->diff = abs((float)$item->system_qty - (float)$item->team_a_qty);
                    return $item;
                })
                ->sortByDesc('diff')
                ->take(10);
        }

        return view('dashboard.index', compact(
            'totalSku', 'skuCounted', 'skuVariance',
            'teamAProgress', 'teamBProgress', 'recountProgress',
            'topVariances', 'session'
        ));
    }
    
    public function analytics()
    {
        return view('dashboard.analytics');
    }
    
    public function traffic()
    {
        return view('dashboard.traffic');
    }
    
    public function salesReport()
    {
        return view('dashboard.sales-report');
    }
    
    // Tambahkan method lain sesuai kebutuhan
}