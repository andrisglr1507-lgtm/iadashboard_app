<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BaPemeriksaanDetail;
use App\Models\BaPemeriksaanHeader;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BaPemeriksaanController extends Controller
{
    /**
     * Display a listing of BA Pemeriksaan Headers.
     */
    public function index()
    {
        $headers = BaPemeriksaanHeader::orderBy('id_ba', 'desc')->get();
        return view('ba_pemeriksaan.index', compact('headers'));
    }

    /**
     * Show the form for creating a new BA Pemeriksaan Header.
     */
    public function create()
    {
        return view('ba_pemeriksaan.create');
    }

    /**
     * Store a new BA Pemeriksaan Header (Ticket).
     */
    public function storeHeader(Request $request)
    {
        $request->validate([
            'periode' => 'required|string|max:255',
            'pic_pemeriksaan' => 'required|string|max:255',
        ]);

        $now = Carbon::now();
        $datePrefix = $now->format('Ymd');
        
        // Find the maximum id_ba generated today
        $maxIdBa = BaPemeriksaanHeader::whereRaw("CAST(id_ba AS CHAR) LIKE ?", ["{$datePrefix}%"])
                    ->max('id_ba');
        
        $currentSequence = 0;
        if ($maxIdBa) {
            $maxIdBaStr = (string)$maxIdBa;
            $sequenceStr = substr($maxIdBaStr, 8);
            $currentSequence = (int)$sequenceStr;
        }

        $currentSequence++;
        $seqPadded = str_pad($currentSequence, 2, '0', STR_PAD_LEFT);
        $idBaStr = $datePrefix . $seqPadded;
        $idBa = (int)$idBaStr;

        BaPemeriksaanHeader::create([
            'id_ba' => $idBa,
            'periode' => $request->periode,
            'pic_pemeriksaan' => $request->pic_pemeriksaan,
            'status' => 'draft',
        ]);

        return redirect()->route('ba_pemeriksaan.index')->with('success', 'Berhasil membuat Sesi BA Pemeriksaan baru.');
    }

    /**
     * Show upload page for a specific BA.
     */
    public function uploadPage($id_ba)
    {
        $header = BaPemeriksaanHeader::where('id_ba', $id_ba)->firstOrFail();

        if ($header->status === 'done') {
            return redirect()->route('ba_pemeriksaan.detail', $id_ba)
                ->with('error', 'Sesi ini sudah diunggah datanya.');
        }

        return view('ba_pemeriksaan.upload', compact('header'));
    }

    /**
     * Import BA Pemeriksaan details from JSON array (via SheetJS).
     */
    public function import(Request $request, $id_ba)
    {
        $header = BaPemeriksaanHeader::where('id_ba', $id_ba)->firstOrFail();

        if ($header->status === 'done') {
            return response()->json(['success' => false, 'message' => 'Data sudah pernah diunggah untuk sesi ini.']);
        }

        $request->validate([
            'records' => 'required|array',
        ]);

        $records = $request->input('records');
        if (empty($records)) {
            return response()->json(['success' => false, 'message' => 'No records to import.']);
        }

        $now = Carbon::now();
        $dataToInsert = [];
        foreach ($records as $row) {
            $dataToInsert[] = [
                'id_ba' => $header->id_ba,
                'cabang' => $row['cabang'] ?? null,
                'invoice' => $row['invoice'] ?? null,
                'tgl_faktur' => $this->parseDate($row['tgl_faktur'] ?? null),
                'tgl_jatuh_tempo' => $this->parseDate($row['tgl_jatuh_tempo'] ?? null),
                'top_hari' => isset($row['top_hari']) && is_numeric($row['top_hari']) ? (int)$row['top_hari'] : null,
                'bulan' => isset($row['bulan']) && is_numeric($row['bulan']) ? (int)$row['bulan'] : null,
                'tahun' => isset($row['tahun']) && is_numeric($row['tahun']) ? (int)$row['tahun'] : null,
                'kode_sales' => $row['kode_sales'] ?? null,
                'channel' => $row['channel'] ?? null,
                'principal' => $row['principal'] ?? null,
                'kode_customer' => $row['kode_customer'] ?? null,
                'pelanggan' => $row['pelanggan'] ?? null,
                'nilai_invoice' => isset($row['nilai_invoice']) ? (float)$row['nilai_invoice'] : 0.00,
                'pembayaran' => isset($row['pembayaran']) ? (float)$row['pembayaran'] : 0.00,
                'ar_cut_off_pemeriksaan' => isset($row['ar_cut_off_pemeriksaan']) ? (float)$row['ar_cut_off_pemeriksaan'] : 0.00,
                'update_ar' => isset($row['update_ar']) ? (float)$row['update_ar'] : 0.00,
                'persen_ar' => isset($row['persen_ar']) ? (float)$row['persen_ar'] : 0.00,
                'status_ar' => $row['status_ar'] ?? null,
                'od' => isset($row['od']) && is_numeric($row['od']) ? (int)$row['od'] : 0,
                'klasifikasi_od' => $row['klasifikasi_od'] ?? null,
                'keterangan_kategori' => $row['keterangan_kategori'] ?? null,
                'keterangan' => $row['keterangan'] ?? null,
                'ket_1' => $row['ket_1'] ?? null,
                'pic' => $row['pic'] ?? null, // Wait, there's pic_pemeriksaan in header, this is individual pic if any
                'status_karyawan' => $row['status_karyawan'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::beginTransaction();
        try {
            // Bulk insert in chunks of 500
            $chunks = array_chunk($dataToInsert, 500);
            foreach ($chunks as $chunk) {
                BaPemeriksaanDetail::insert($chunk);
            }

            $header->update(['status' => 'done']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($dataToInsert) . ' data detail berhasil di-import.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show details of a specific BA.
     */
    public function detail($id_ba)
    {
        $header = BaPemeriksaanHeader::with('details')->where('id_ba', $id_ba)->firstOrFail();
        return view('ba_pemeriksaan.detail', compact('header'));
    }

    /**
     * Helper to parse Excel dates or standard dates to Y-m-d.
     */
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            $unixDate = ($value - 25569) * 86400;
            return gmdate('Y-m-d', $unixDate);
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
