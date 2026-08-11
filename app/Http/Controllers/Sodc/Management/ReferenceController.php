<?php

namespace App\Http\Controllers\Sodc\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\OpnameReference;
use App\Models\OpnameReferenceDetail;
use App\Models\Warehouse;
use App\Models\Bin;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ReferenceController extends Controller
{
    public function index(Request $request)
    {
        $query = OpnameReference::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('reference_code', 'like', "%{$search}%");
        }
        
        $data = $query->orderBy('id', 'desc')->paginate(15);
        return view('sodc.opname_management.references.index', compact('data'));
    }

    public function showImport()
    {
        return view('sodc.opname_management.references.import');
    }

    public function downloadTemplate()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=template_opname_wms.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['bin_code', 'sku_code', 'system_qty', 'uom', 'batch_number', 'expired_date'];
        
        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, ['AA.001.10', 'PRD001', '120', 'PCS', 'BATCH-123', '2027-12-31']);
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required'
        ]);

        $file = $request->file('file');
        if ($file->getClientOriginalExtension() != 'csv') {
            return redirect()->back()->with('error', 'Hanya format CSV yang didukung.');
        }

        $handle = fopen($file->getRealPath(), 'r');
        $firstLine = fgets($handle);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        rewind($handle);
        
        fgetcsv($handle, 1000, $delimiter); // skip header
        
        $binCache = [];
        $skuCache = [];
        $detailsToInsert = [];
        $totalQty = 0;
        $now = now();
        
        DB::beginTransaction();
        try {
            // 1. Create Header Document (Global, no warehouse_id)
            $reference = OpnameReference::create([
                'reference_uuid' => Str::uuid(),
                'reference_code' => 'WMS-REF-' . date('Ymd-His'),
                'source_system' => 'WMS',
                'reference_datetime' => $now,
                'status' => 'ACTIVE',
                'imported_at' => $now,
                'total_sku' => 0,
                'total_bin' => 0,
                'total_qty' => 0
            ]);

            $uniqueSkus = [];
            $uniqueBins = [];

            // 2. Parse CSV
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (count($row) < 3) continue;
                
                // Sanitize string
                $row = array_map(function($val) {
                    return mb_convert_encoding(trim($val), 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
                }, $row);
                
                $bin_code = $row[0];
                $sku_code = $row[1];
                $sys_qty = (float)$row[2];
                $uom = $row[3] ?? 'PCS';
                $batch = $row[4] ?? null;
                $expired = $row[5] ?? null;
                
                // Cache Bin ID and detect Warehouse
                if (!array_key_exists($bin_code, $binCache)) {
                    $bin = Bin::where('bin_code', $bin_code)->first();
                    if (!$bin) {
                        throw new \Exception("Bin Code '{$bin_code}' tidak ditemukan di Master Data.");
                    }
                    $binCache[$bin_code] = [
                        'id' => $bin->id,
                        'warehouse_id' => $bin->warehouse_id
                    ];
                }
                
                // Cache Product ID
                if (!array_key_exists($sku_code, $skuCache)) {
                    $prod = Product::where('sku_code', $sku_code)->first();
                    if (!$prod) {
                        throw new \Exception("SKU Code '{$sku_code}' tidak ditemukan di Master Product.");
                    }
                    $skuCache[$sku_code] = $prod->id;
                }
                
                $uniqueSkus[$sku_code] = true;
                $uniqueBins[$bin_code] = true;
                $totalQty += $sys_qty;

                // 3. Build details chunk
                $detailsToInsert[] = [
                    'reference_id' => $reference->id,
                    'warehouse_id' => $binCache[$bin_code]['warehouse_id'], // Dari Bin Master
                    'bin_id' => $binCache[$bin_code]['id'],
                    'product_id' => $skuCache[$sku_code],
                    'sku_code' => $sku_code,
                    'bin_code' => $bin_code,
                    'system_qty' => $sys_qty,
                    'uom' => $uom,
                    'batch_number' => $batch,
                    'expiry_date' => $expired ? date('Y-m-d', strtotime($expired)) : null,
                    'stock_status' => 'AVAILABLE',
                    'created_at' => $now
                ];

                if (count($detailsToInsert) >= 500) {
                    OpnameReferenceDetail::insert($detailsToInsert);
                    $detailsToInsert = [];
                }
            }
            fclose($handle);
            
            if (count($detailsToInsert) > 0) {
                OpnameReferenceDetail::insert($detailsToInsert);
            }
            
            // 4. Update Header Totals
            $reference->update([
                'total_sku' => count($uniqueSkus),
                'total_bin' => count($uniqueBins),
                'total_qty' => $totalQty
            ]);
            
            DB::commit();
            return redirect()->route('sodc.references.index')->with('success', 'Data Referensi WMS berhasil diimport (Multi-Warehouse)!');
            
        } catch (\Exception $e) {
            DB::rollback();
            if (isset($handle) && is_resource($handle)) fclose($handle);
            return redirect()->back()->with('error', 'Gagal Import: ' . $e->getMessage());
        }
    }
    
    public function show($id)
    {
        $reference = OpnameReference::findOrFail($id);
        $details = OpnameReferenceDetail::with('product')->where('reference_id', $id)
                        ->orderBy('bin_code')->paginate(50);
                        
        return view('sodc.opname_management.references.show', compact('reference', 'details'));
    }
    
    // Non-aktifkan standard update/edit/destroy untuk menghindari manipulasi
    public function destroy($id)
    {
        OpnameReferenceDetail::where('reference_id', $id)->delete();
        OpnameReference::findOrFail($id)->delete();
        return redirect()->route('sodc.references.index')->with('success', 'Dokumen Referensi dan semua datanya berhasil dihapus');
    }
}