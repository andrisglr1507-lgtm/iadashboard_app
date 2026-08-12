<?php

namespace App\Http\Controllers\Api\Sodc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpnameSession;
use App\Models\OpnameReferenceDetail;
use App\Models\OpnameTeamMember;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Mengambil daftar SEMUA Bin target (Live View).
     * Tidak lagi berdasarkan assignment, melainkan Free-for-all dengan filter Zona/Level di Flutter.
     */
    public function allBins(Request $request)
    {
        $user = $request->user();

        // 1. Get the active session
        $activeSession = OpnameSession::where('status', 'ACTIVE')->first();
        if (!$activeSession) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada sesi opname yang aktif saat ini.',
                'data' => null
            ], 404);
        }

        // 2. Determine target principals dynamically
        $targetPrincipals = DB::table('opname_reference_details')
            ->join('products', 'opname_reference_details.product_id', '=', 'products.id')
            ->where('opname_reference_details.reference_id', $activeSession->reference_id)
            ->whereNotNull('products.principal')
            ->distinct()
            ->pluck('products.principal')
            ->toArray();

        // 3. Find the user's team
        $teamMember = OpnameTeamMember::where('user_id', $user->id)->first();
        $myTeamId = $teamMember ? $teamMember->team_id : null;

        // 4. Get all unique bins that my team has ALREADY counted in this session
        $countedBins = [];
        if ($myTeamId) {
            $countedBins = DB::table('opname_counts')
                ->join('opname_reference_details', 'opname_counts.reference_detail_id', '=', 'opname_reference_details.id')
                ->where('opname_counts.session_id', $activeSession->id)
                ->where('opname_counts.team_id', $myTeamId)
                ->pluck('opname_reference_details.bin_code')
                ->unique()
                ->toArray();
        }

        // 5. Get ALL target bins for this session
        $bins = DB::table('opname_reference_details')
            ->leftJoin('bins', 'opname_reference_details.bin_code', '=', 'bins.bin_code')
            ->where('opname_reference_details.reference_id', $activeSession->reference_id)
            ->select('opname_reference_details.bin_code', 'bins.zone', 'bins.level')
            ->distinct()
            ->get();

        // 6. Map status
        $mappedBins = $bins->map(function($bin) use ($countedBins) {
            return [
                'bin_code' => $bin->bin_code,
                'zone' => $bin->zone ?? 'UNKNOWN',
                'level' => $bin->level ?? '1',
                'is_counted_by_my_team' => in_array($bin->bin_code, $countedBins)
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil daftar seluruh Bin.',
            'data' => [
                'session_id' => $activeSession->id,
                'session_code' => $activeSession->session_code,
                'mode' => $activeSession->mode,
                'target_principals' => $targetPrincipals,
                'my_team_id' => $myTeamId,
                'bins' => $mappedBins
            ]
        ]);
    }

    /**
     * Fetch SKUs specifically for one Bin when User taps on it in Flutter.
     */
    public function binDetails(Request $request, $binCode)
    {
        $activeSession = OpnameSession::where('status', 'ACTIVE')->first();
        if (!$activeSession) {
            return response()->json(['success' => false, 'message' => 'Tidak ada sesi aktif.'], 404);
        }

        $details = OpnameReferenceDetail::with('product')
            ->where('reference_id', $activeSession->reference_id)
            ->where('bin_code', $binCode)
            ->get();

        $expectedItems = $details->map(function ($detail) {
            return [
                'id_product' => $detail->product->id_product ?? $detail->sku_code,
                'product_name' => $detail->product->product_name ?? 'Unknown Product',
                'uom' => $detail->product->uom ?? 'PCS',
                'reference_detail_id' => $detail->id,
            ];
        })->values()->toArray();

        return response()->json([
            'success' => true,
            'data' => $expectedItems
        ]);
    }
}
