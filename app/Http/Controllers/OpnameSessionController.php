<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OpnameSession;
use App\Models\OpnameProduct;
use App\Models\MasterProduct;

class OpnameSessionController extends Controller
{
    /**
     * Display a listing of all opname sessions.
     */
    public function index()
    {
        // Fetch all sessions to display in client-side DataTable
        $sessions = OpnameSession::all();
        return view('sessions.index', compact('sessions'));
    }

    public function create()
    {
        $latestSession = OpnameSession::latest('created_at')->first();
        
        $nextId = 12345;
        if ($latestSession && is_numeric($latestSession->session_id)) {
            $nextId = (int)$latestSession->session_id + 1;
        }

        return view('sessions.create', compact('nextId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string|unique:opname_sessions',
            'branch_id' => 'required|string|max:50',
            'warehouse_code' => 'required|string|max:50',
            'bin_location' => 'nullable|string|max:100',
            'periode_start' => 'required|date',
            'periode_end' => 'required|date|after_or_equal:periode_start',
            'mode' => 'required|in:S,D',
        ]);

        $validated['status'] = 'draft';
        $validated['created_by'] = 1; // Default for now

        OpnameSession::create($validated);

        return redirect()->route('sessions.index')->with('success', 'Sesi Opname baru berhasil dibuat!');
    }

    /**
     * Show the upload page for a specific draft session.
     */
    public function uploadPage($id)
    {
        $session = OpnameSession::findOrFail($id);

        // Security check: Only allow upload if status is 'draft'
        if ($session->status !== 'draft') {
            return redirect()->route('sessions.index')
                ->with('error', 'Hanya sesi dengan status Draft yang dapat diunggah datanya.');
        }

        return view('sessions.upload', compact('session'));
    }

    /**
     * Process the uploaded Excel/JSON records and update the session state.
     */
    public function handleUpload(Request $request, $id)
    {
        $session = OpnameSession::findOrFail($id);

        if ($session->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Gagal! Sesi opname ini tidak berstatus draft.',
            ], 400);
        }

        $request->validate([
            'records' => 'required|array',
            'records.*.id_product' => 'required|string',
            'records.*.no_urut' => 'nullable|integer',
            'records.*.stock_system' => 'nullable|numeric',
            'records.*.harga' => 'nullable|numeric',
        ]);

        $records = $request->input('records');

        // Validasi apakah id_product yang diunggah ada di master_products
        $uploadedProductIds = collect($records)->pluck('id_product')->unique()->toArray();
        $existingProducts = MasterProduct::whereIn('id_product', $uploadedProductIds)
                                         ->pluck('id_product')
                                         ->toArray();
        
        $missingProducts = array_diff($uploadedProductIds, $existingProducts);

        if (!empty($missingProducts)) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload! Terdapat ID Produk yang tidak ditemukan di Master Produk. Silakan tambahkan terlebih dahulu.',
                'missing_products' => array_values($missingProducts)
            ], 400);
        }

        // Hapus data lama jika user melakukan re-upload pada sesi draft ini
        OpnameProduct::where('session_id', $session->session_id)->delete();

        // Insert ke database (opname_products)
        $insertData = [];
        $now = now();
        $urutSequence = 1;

        foreach ($records as $record) {
            $insertData[] = [
                'session_id' => $session->session_id,
                'no_urut' => !empty($record['no_urut']) ? $record['no_urut'] : $urutSequence,
                'id_product' => $record['id_product'],
                'stock_system' => $record['stock_system'] ?? 0,
                'harga' => $record['harga'] ?? 0,
                'is_manual' => 0, // default
                'created_at' => $now
            ];
            $urutSequence++;
        }

        // Chunk insert to avoid too many bindings error if file is very large
        foreach (array_chunk($insertData, 500) as $chunk) {
            OpnameProduct::insert($chunk);
        }

        $session->status = 'counting';
        $session->save();

        return response()->json([
            'success' => true,
            'message' => count($records) . ' baris data opname berhasil diunggah. Status sesi diupdate menjadi "Counting".',
        ]);
    }
}
