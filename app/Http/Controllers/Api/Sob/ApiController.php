<?php

namespace App\Http\Controllers\Api\Sob;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SobOpnameSession;
use App\Models\SobZone;
use App\Models\SobAssignment;
use App\Models\SobSweepHeader;
use App\Models\SobSweepCount;
use App\Models\SobTagResult;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public function activeSessions(Request $request)
    {
        $sessions = SobOpnameSession::where('status', 'ACTIVE')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }

    public function zones(Request $request)
    {
        $zones = SobZone::where('is_active', true)->get();
        return response()->json([
            'success' => true,
            'data' => $zones
        ]);
    }

    public function myAssignments(Request $request)
    {
        $user = $request->user();
        $assignments = SobAssignment::with(['zone', 'session'])
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $assignments
        ]);
    }

    public function submitCount(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:sob_opname_sessions,id',
            'tag_number' => 'required|string',
            'zone_code' => 'required|string',
            'team_role' => 'required|in:SINGLE,TEAM_A,TEAM_B,RECOUNT',
            'product_id' => 'required|string',
            'final_qty' => 'required|numeric|min:0',
        ]);

        $user = $request->user();
        $sessionId = $request->session_id;
        $tagNumber = strtoupper(trim($request->tag_number));

        $session = SobOpnameSession::findOrFail($sessionId);
        $zone = SobZone::where('zone_code', $request->zone_code)->first();
        $zoneId = $zone ? $zone->id : 1;

        // 1. Cari atau buat Header Titik Sisir
        $header = SobSweepHeader::firstOrCreate(
            [
                'session_id' => $sessionId,
                'tag_number' => $tagNumber,
            ],
            [
                'zone_id' => $zoneId,
                'location_marker' => $request->location_marker,
                'status_team_a' => 'PENDING',
                'status_team_b' => 'PENDING',
                'comparison_status' => 'PENDING',
            ]
        );

        // 2. Simpan Transaksi Hitung
        $sweepCount = SobSweepCount::create([
            'session_id' => $sessionId,
            'header_id' => $header->id,
            'tag_number' => $tagNumber,
            'team_role' => $request->team_role,
            'counted_by' => $user->id,
            'product_id' => $request->product_id,
            'scanned_barcode' => $request->scanned_barcode,
            'stock_condition' => $request->stock_condition ?? 'GOOD',
            'qty_karton' => $request->qty_karton ?? 0,
            'qty_pcs' => $request->qty_pcs ?? 0,
            'uom' => $request->uom ?? 1,
            'final_qty' => $request->final_qty,
            'round_number' => $request->round_number ?? 1,
            'notes' => $request->notes,
            'offline_created_at' => $request->offline_created_at,
        ]);

        // 3. Update Status Header berdasarkan Tim
        if ($request->team_role == 'TEAM_A' || $request->team_role == 'SINGLE') {
            $header->update([
                'status_team_a' => 'COMPLETED',
                'team_a_user_id' => $user->id,
                'team_a_at' => now(),
            ]);
        } elseif ($request->team_role == 'TEAM_B') {
            $header->update([
                'status_team_b' => 'COMPLETED',
                'team_b_user_id' => $user->id,
                'team_b_at' => now(),
            ]);
        }

        // 4. Update / Komparasi di SobTagResult
        $condition = $request->stock_condition ?? 'GOOD';
        $tagResult = SobTagResult::firstOrNew([
            'session_id' => $sessionId,
            'header_id' => $header->id,
            'tag_number' => $tagNumber,
            'product_id' => $request->product_id,
            'stock_condition' => $condition,
        ]);

        if ($session->count_mode == 'SINGLE' || $request->team_role == 'SINGLE') {
            $tagResult->single_qty = $request->final_qty;
            $tagResult->final_approved_qty = $request->final_qty;
            $tagResult->is_match = true;
            $header->update(['comparison_status' => 'MATCH']);
        } else {
            // Mode DOUBLE:
            if ($request->team_role == 'TEAM_A') {
                $tagResult->team_a_qty = $request->final_qty;
            } elseif ($request->team_role == 'TEAM_B') {
                $tagResult->team_b_qty = $request->final_qty;
            }

            // Jika kedua tim sudah input, bandingkan
            if (!is_null($tagResult->team_a_qty) && !is_null($tagResult->team_b_qty)) {
                $diff = $tagResult->team_a_qty - $tagResult->team_b_qty;
                $tagResult->diff_qty = $diff;
                $isMatch = ($diff == 0);
                $tagResult->is_match = $isMatch;
                if ($isMatch) {
                    $tagResult->final_approved_qty = $tagResult->team_a_qty;
                    $header->update(['comparison_status' => 'MATCH']);
                } else {
                    $header->update(['comparison_status' => 'MISMATCH']);
                }
            }
        }
        $tagResult->save();

        return response()->json([
            'success' => true,
            'message' => "Hitungan Tag $tagNumber berhasil disimpan.",
            'data' => [
                'tag_number' => $tagNumber,
                'comparison_status' => $header->comparison_status,
                'is_match' => $tagResult->is_match,
            ]
        ]);
    }
}
