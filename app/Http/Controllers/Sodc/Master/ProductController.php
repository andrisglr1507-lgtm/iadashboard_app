<?php

namespace App\Http\Controllers\Sodc\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('sku_code', 'like', "%{$search}%")
                  ->orWhere('sku_name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
        }
        
        $data = $query->orderBy('id', 'desc')->paginate(15);
        return view('sodc.master.products.index', compact('data'));
    }

    public function create()
    {
        return view('sodc.master.products.create');
    }

    public function store(Request $request)
    {
        Product::create($request->all());
        return redirect()->route('sodc.products.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        $item = Product::findOrFail($id);
        return view('sodc.master.products.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Product::findOrFail($id);
        $item->update($request->all());
        return redirect()->route('sodc.products.index')->with('success', 'Data berhasil diupdate');
    }

    public function showImport()
    {
        return view('sodc.master.products.import');
    }

    public function downloadTemplate()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=template_import_products.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = array('sku_code', 'sku_name', 'barcode', 'carton_code', 'packname', 'uom', 'principal');
        
        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, array('PRD001', 'Kopi Saset ABC', '8991234567890', 'C-8991234567890', '1 CTN = 24 PCS', 24, 'ABC Group'));
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
        if($file->getClientOriginalExtension() != 'csv') {
            return redirect()->back()->with('error', 'Hanya format CSV yang didukung saat ini.');
        }

        $handle = fopen($file->getRealPath(), 'r');
        
        // Deteksi delimiter (, atau ;) karena Excel Indonesia sering pakai ;
        $firstLine = fgets($handle);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        rewind($handle);
        
        $header = fgetcsv($handle, 1000, $delimiter); 
        
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            if (count($row) < 2) continue;
            
            // Konversi karakter aneh (non-UTF8) dari Excel (Windows-1252/ISO-8859-1) ke UTF-8 yang valid
            $row = array_map(function($val) {
                return mb_convert_encoding(trim($val), 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
            }, $row);
            
            Product::updateOrCreate(
                ['sku_code' => $row[0]],
                [
                    'sku_name' => $row[1] ?? '',
                    'barcode' => $row[2] ?: null,
                    'carton_code' => $row[3] ?: null,
                    'packname' => $row[4] ?: null,
                    'uom' => isset($row[5]) && is_numeric($row[5]) ? (int)$row[5] : 1,
                    'principal' => $row[6] ?: null,
                ]
            );
        }
        fclose($handle);

        return redirect()->route('sodc.products.index')->with('success', 'Data Products berhasil diimport dari CSV!');
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return redirect()->route('sodc.products.index')->with('success', 'Data berhasil dihapus');
    }

    public function syncApi(\Illuminate\Http\Request $request)
    {
        set_time_limit(300);
        $token = '75|tuN21XD303rLCfLQhRkAEXixfWHwwDoZIrXYzDdd';
        
        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ])->timeout(60)->get('https://wms.edrs.co.id/api/mileapp/products');

            if ($response->successful()) {
                $json = $response->json();
                $products = $json['data'] ?? [];
                
                $count = 0;
                foreach ($products as $p) {
                    $dataId = $p['dataId'] ?? null;
                    if (!$dataId) continue;

                    $uom = isset($p['uom']) && is_numeric($p['uom']) ? (int)$p['uom'] : 1;
                    if ($uom == 0) $uom = 1;

                    Product::updateOrCreate(
                        ['sku_code' => $dataId],
                        [
                            'sku_name' => $p['productName'] ?? '',
                            'barcode' => $p['itemBarcode'] ?: null,
                            'carton_code' => $p['cartonBarcode'] ?? null,
                            'packname' => $p['productCategoryCode'] ?: null,
                            'uom' => $uom,
                            'principal' => $p['principal'] ?: null,
                            'is_active' => 1,
                        ]
                    );
                    $count++;
                }

                return redirect()->route('sodc.products.index')->with('success', "Berhasil sinkronisasi $count produk dari WMS API.");
            } else {
                return redirect()->route('sodc.products.index')->with('error', "Gagal sinkronisasi API WMS. Status: " . $response->status());
            }
        } catch (\Exception $e) {
            return redirect()->route('sodc.products.index')->with('error', "Terjadi kesalahan sinkronisasi: " . $e->getMessage());
        }
    }

    /**
     * Membandingkan produk dari WMS API dengan produk yang sudah ada di database.
     * Mengembalikan daftar SKU baru yang belum terdaftar di tabel products.
     */
    public function syncCheck()
    {
        set_time_limit(300);
        $token = '75|tuN21XD303rLCfLQhRkAEXixfWHwwDoZIrXYzDdd';
        
        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ])->timeout(60)->get('https://wms.edrs.co.id/api/mileapp/products');

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghubungi API WMS. Status: ' . $response->status()
                ], 500);
            }

            $json = $response->json();
            $products = $json['data'] ?? [];

            // Ambil semua sku_code yang sudah ada di database untuk lookup cepat O(1)
            $existingSkus = Product::pluck('sku_code')
                ->filter()
                ->map(fn($item) => strtoupper(trim($item)))
                ->flip();

            $newProducts = [];
            $existingCount = 0;

            foreach ($products as $p) {
                $sku = trim($p['dataId'] ?? '');
                if (!$sku) continue;

                $skuUpper = strtoupper($sku);
                if (!isset($existingSkus[$skuUpper])) {
                    $uom = isset($p['uom']) && is_numeric($p['uom']) ? (int)$p['uom'] : 1;
                    if ($uom <= 0) $uom = 1;

                    $newProducts[] = [
                        'sku_code' => $sku,
                        'sku_name' => $p['productName'] ?? '',
                        'barcode' => $p['itemBarcode'] ?: null,
                        'carton_code' => $p['cartonBarcode'] ?? null,
                        'packname' => $p['productCategoryCode'] ?: null,
                        'uom' => $uom,
                        'principal' => $p['principal'] ?: null,
                    ];
                } else {
                    $existingCount++;
                }
            }

            return response()->json([
                'success' => true,
                'total_wms' => count($products),
                'total_db' => $existingSkus->count(),
                'existing_count' => $existingCount,
                'new_count' => count($newProducts),
                'new_products' => $newProducts,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memeriksa data WMS: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan hanya produk/SKU baru yang belum ada di database.
     */
    public function syncSave(\Illuminate\Http\Request $request)
    {
        set_time_limit(300);
        $items = $request->input('products', []);

        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada produk baru yang dipilih untuk disimpan.'
            ], 400);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $now = now();
            $insertData = [];
            $count = 0;

            // Pastikan double check agar tidak duplikat jika ada simultaneous request
            $currentExisting = Product::whereIn('sku_code', array_column($items, 'sku_code'))
                ->pluck('sku_code')
                ->map(fn($item) => strtoupper(trim($item)))
                ->flip();

            foreach ($items as $item) {
                $sku = trim($item['sku_code'] ?? '');
                if (!$sku || isset($currentExisting[strtoupper($sku)])) {
                    continue;
                }

                $insertData[] = [
                    'sku_code' => $sku,
                    'sku_name' => $item['sku_name'] ?? '',
                    'barcode' => $item['barcode'] ?? null,
                    'carton_code' => $item['carton_code'] ?? null,
                    'packname' => $item['packname'] ?? null,
                    'uom' => (int)($item['uom'] ?? 1) ?: 1,
                    'principal' => $item['principal'] ?? null,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $count++;
            }

            foreach (array_chunk($insertData, 500) as $chunk) {
                Product::insert($chunk);
            }

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil mendownload dan menyimpan {$count} produk baru ke database.",
                'inserted_count' => $count
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan produk: ' . $e->getMessage()
            ], 500);
        }
    }
}