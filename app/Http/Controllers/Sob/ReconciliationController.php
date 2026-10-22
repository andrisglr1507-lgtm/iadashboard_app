<?php

namespace App\Http\Controllers\Sob;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SobReconciliation;
use App\Models\SobOpnameSession;
use App\Models\SobTagResult;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReconciliationController extends Controller
{
    public function index(Request $request)
    {
        $activeSessionId = $request->get('session_id');
        $sessions = SobOpnameSession::orderBy('id', 'desc')->get();

        if (!$activeSessionId && $sessions->isNotEmpty()) {
            $activeSessionId = $sessions->first()->id;
        }

        $session = SobOpnameSession::find($activeSessionId);

        // Agregasi fisik dari SobTagResult grouped by product_id
        $physicalSummary = [];
        if ($session) {
            $physicalSummary = SobTagResult::where('session_id', $session->id)
                ->select('product_id', DB::raw('SUM(final_approved_qty) as total_physical'))
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');
        }

        $reconciliations = SobReconciliation::with(['product', 'approver'])
            ->when($activeSessionId, function ($q) use ($activeSessionId) {
                $q->where('session_id', $activeSessionId);
            })
            ->paginate(30);

        return view('sob.reconciliations.index', compact('reconciliations', 'sessions', 'activeSessionId', 'session', 'physicalSummary'));
    }

    public function generate(Request $request, $sessionId)
    {
        $session = SobOpnameSession::findOrFail($sessionId);

        // Ambil akumulasi fisik dari seluruh tag yang sudah disetujui
        $physicalData = SobTagResult::where('session_id', $session->id)
            ->select('product_id', DB::raw('SUM(final_approved_qty) as total_physical'))
            ->groupBy('product_id')
            ->get();

        foreach ($physicalData as $p) {
            $product = Product::where('id_product', $p->product_id)->first();
            $systemQty = 0; // fallback snapshot jika ada
            $diffQty = $p->total_physical - $systemQty;

            $variance = 'MATCH';
            if ($diffQty > 0) $variance = 'OVER';
            elseif ($diffQty < 0) $variance = 'SHORT';

            $cogs = $product->cogs ?? 0;
            $diffVal = $diffQty * $cogs;

            SobReconciliation::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'product_id' => $p->product_id,
                ],
                [
                    'system_qty' => $systemQty,
                    'physical_qty' => $p->total_physical,
                    'diff_qty' => $diffQty,
                    'variance_status' => $variance,
                    'cogs_unit_price' => $cogs,
                    'total_diff_value' => $diffVal,
                    'status' => 'DRAFT',
                ]
            );
        }

        return redirect()->back()->with('success', 'Rekonsiliasi stok cabang berhasil dikompilasi.');
    }

    public function approve(Request $request, $id)
    {
        $rec = SobReconciliation::findOrFail($id);
        $rec->update([
            'status' => 'APPROVED',
            'approved_by' => Auth::id() ?? 1,
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Hasil rekonsiliasi telah disetujui.');
    }
}
