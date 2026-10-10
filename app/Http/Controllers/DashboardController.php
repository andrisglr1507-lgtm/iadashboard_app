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
            
            $teamAProgress = $totalSku > 0 ? round(($teamACount / $totalSku) * 100) : 0;
            $teamBProgress = $totalSku > 0 ? round(($teamBCount / $totalSku) * 100) : 0;

            // Progress Recount
            $totalRecountAssignments = OpnameRecountAssignment::where('session_id', $session->id)->count();
            $doneRecountAssignments = OpnameRecountAssignment::where('session_id', $session->id)
                ->where('status', '!=', 'PENDING')
                ->count();
            $recountProgress = $totalRecountAssignments > 0 ? round(($doneRecountAssignments / $totalRecountAssignments) * 100) : 0;

            // Top Variances diganti jadi Top Counters (Tim Teraktif)
            $topCounters = OpnameCount::with('user')
                ->selectRaw('counted_by, count(*) as total_scan')
                ->where('session_id', $session->id)
                ->groupBy('counted_by')
                ->orderByDesc('total_scan')
                ->take(5)
                ->get();
                
            // Aktivitas Terkini (Realtime Recent Scans)
            $recentActivities = OpnameCount::with(['user', 'referenceDetail.product'])
                ->where('session_id', $session->id)
                ->orderBy('created_at', 'desc')
                ->take(7)
                ->get();
        } else {
            $topCounters = collect();
            $recentActivities = collect();
        }

        return view('dashboard.index', compact(
            'totalSku', 'skuCounted', 'skuVariance',
            'teamAProgress', 'teamBProgress', 'recountProgress',
            'topCounters', 'recentActivities', 'session'
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