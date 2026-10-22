<?php

namespace App\Http\Controllers\Sob;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SobTagResult;
use App\Models\SobOpnameSession;
use Illuminate\Support\Facades\Auth;

class TagResultController extends Controller
{
    public function index(Request $request)
    {
        $activeSessionId = $request->get('session_id');
        $filterStatus = $request->get('status'); // MATCH, MISMATCH
        $sessions = SobOpnameSession::orderBy('id', 'desc')->get();

        if (!$activeSessionId && $sessions->isNotEmpty()) {
            $activeSessionId = $sessions->first()->id;
        }

        $results = SobTagResult::with(['session', 'header.zone', 'product', 'recountedByUser'])
            ->when($activeSessionId, function ($q) use ($activeSessionId) {
                $q->where('session_id', $activeSessionId);
            })
            ->when($filterStatus == 'MATCH', function ($q) {
                $q->where('is_match', true);
            })
            ->when($filterStatus == 'MISMATCH', function ($q) {
                $q->where('is_match', false);
            })
            ->orderBy('tag_number')
            ->paginate(30);

        return view('sob.tag_results.index', compact('results', 'sessions', 'activeSessionId', 'filterStatus'));
    }

    public function inputRecount(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:sob_tag_results,id',
            'recount_qty' => 'required|numeric|min:0',
            'approval_notes' => 'nullable|string|max:255',
        ]);

        $res = SobTagResult::findOrFail($request->id);
        $res->update([
            'recount_qty' => $request->recount_qty,
            'final_approved_qty' => $request->recount_qty,
            'recounted_by' => Auth::id() ?? 1,
            'approval_notes' => $request->approval_notes,
            'is_match' => true, // disahkan lewat recount
        ]);

        // Update header comparison status jika semua tag result di header ini sudah beres
        $hasMismatch = SobTagResult::where('header_id', $res->header_id)->where('is_match', false)->exists();
        if (!$hasMismatch) {
            $res->header->update(['comparison_status' => 'MATCH']);
        }

        return redirect()->back()->with('success', "Nilai Recount untuk Tag {$res->tag_number} ({$res->product_id}) berhasil disahkan.");
    }
}
