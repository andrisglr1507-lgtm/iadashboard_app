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
            $query->where(function($q) use ($search) {
                $q->where('sku_code', 'like', "%{$search}%")
                  ->orWhere('sku_name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('packname', 'like', "%{$search}%")
                  ->orWhere('product_category_code', 'like', "%{$search}%")
                  ->orWhere('principal', 'like', "%{$search}%");
            });
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
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=template_import_products.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = [
            'sku_code',
            'sku_name',
            'barcode',
            'carton_code',
            'product_category_code',
            'packname',
            'uom',
            'principal',
            'company_code',
            'is_active'
        ];
        
        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            // Tulis UTF-8 BOM agar Excel di Windows membaca karakter/encoding dengan benar
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);
            fputcsv($file, ['PRD001', 'Kopi Saset ABC 10x20g', '8991234567890', 'C-8991234567890', 'Minuman', 'BOX', 24, 'ABC Group', 'CSAN', 1]);
            fputcsv($file, ['PRD002', 'Biskuit Roma Kelapa 300g', '8991234567891', 'C-8991234567891', 'Biskuit', 'CTN', 30, 'Mayora', 'CSAN', 1]);
            fputcsv($file, ['PRD003', 'Minyak Goreng Sania 2L', '8991234567892', 'C-8991234567892', 'Minyak', 'PCS', 1, 'Wilmar', 'CSAN', 1]);
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'import_mode' => 'nullable|string|in:upsert,update_only_empty,insert_only'
        ]);

        $mode = $request->input('import_mode', 'upsert');

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['csv', 'txt'])) {
            return redirect()->back()->with('error', 'Hanya format CSV yang didukung.');
        }

        $handle = fopen($file->getRealPath(), 'r');
        
        // Deteksi delimiter (, atau ;) karena Excel versi Indonesia sering menggunakan ;
        $firstLine = fgets($handle);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        rewind($handle);
        
        $headerRaw = fgetcsv($handle, 2000, $delimiter); 
        if (!$headerRaw) {
            fclose($handle);
            return redirect()->back()->with('error', 'File CSV kosong atau header tidak valid.');
        }

        // Hapus BOM jika ada di elemen header pertama
        if (isset($headerRaw[0])) {
            $headerRaw[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headerRaw[0]);
        }

        // Petakan header kolom secara dinamis dengan dukungan berbagai alias
        $colMap = [];
        foreach ($headerRaw as $idx => $hName) {
            $key = strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', $hName)));
            if (in_array($key, ['sku_code', 'sku', 'code', 'dataid', 'sku code', 'kode_sku', 'kode sku', 'id_product'])) $colMap['sku_code'] = $idx;
            elseif (in_array($key, ['sku_name', 'name', 'product_name', 'productname', 'sku name', 'nama_produk', 'nama produk', 'nama_barang', 'nama barang', 'deskripsi'])) $colMap['sku_name'] = $idx;
            elseif (in_array($key, ['barcode', 'itembarcode', 'item_barcode', 'barcode_satuan', 'barcode satuan', 'ean', 'upc'])) $colMap['barcode'] = $idx;
            elseif (in_array($key, ['carton_code', 'cartoncode', 'carton_barcode', 'cartonbarcode', 'barcode_karton', 'barcode karton', 'carton barcode'])) $colMap['carton_code'] = $idx;
            elseif (in_array($key, ['product_category_code', 'productcategorycode', 'category', 'category_code', 'kategori', 'product category code', 'kategori_produk', 'kategori produk', 'product_category'])) $colMap['product_category_code'] = $idx;
            elseif (in_array($key, ['packname', 'pack_name', 'kemasan', 'satuan', 'pack name', 'satuan_kemasan', 'satuan kemasan', 'pack'])) $colMap['packname'] = $idx;
            elseif (in_array($key, ['uom', 'isi', 'qty_uom', 'qty', 'isi kemasan', 'isi_kemasan', 'satuan_isi', 'uom_qty'])) $colMap['uom'] = $idx;
            elseif (in_array($key, ['principal', 'principle', 'prinsipal', 'supplier', 'vendor'])) $colMap['principal'] = $idx;
            elseif (in_array($key, ['company_code', 'companycode', 'company', 'company code', 'cabang', 'kode_cabang', 'kode cabang', 'comp_code'])) $colMap['company_code'] = $idx;
            elseif (in_array($key, ['is_active', 'isactive', 'active', 'status', 'is active', 'aktif', 'status_aktif'])) $colMap['is_active'] = $idx;
        }

        // Fallback urutan kolom jika file CSV tanpa header resmi
        if (!isset($colMap['sku_code'])) {
            $colMap['sku_code'] = 0;
            $colMap['sku_name'] = 1;
            $colMap['barcode'] = 2;
            $colMap['carton_code'] = 3;
            $colMap['product_category_code'] = 4;
            $colMap['packname'] = 5;
            $colMap['uom'] = 6;
            $colMap['principal'] = 7;
            $colMap['company_code'] = 8;
            $colMap['is_active'] = 9;
        }

        $insertedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        
        while (($row = fgetcsv($handle, 2000, $delimiter)) !== FALSE) {
            if (count($row) < 1) continue;
            
            // Konversi karakter non-UTF8 dari Excel ke UTF-8 dan bersihkan BOM
            $row = array_map(function($val) {
                $cleaned = preg_replace('/^\xEF\xBB\xBF/', '', trim($val ?? ''));
                return mb_convert_encoding($cleaned, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
            }, $row);

            $sku = $row[$colMap['sku_code']] ?? '';
            if ($sku === '') continue;

            $skuName = isset($colMap['sku_name'], $row[$colMap['sku_name']]) ? $row[$colMap['sku_name']] : null;
            $barcode = isset($colMap['barcode'], $row[$colMap['barcode']]) && $row[$colMap['barcode']] !== '' ? $row[$colMap['barcode']] : null;
            $cartonCode = isset($colMap['carton_code'], $row[$colMap['carton_code']]) && $row[$colMap['carton_code']] !== '' ? $row[$colMap['carton_code']] : null;
            $packname = isset($colMap['packname'], $row[$colMap['packname']]) && $row[$colMap['packname']] !== '' ? $row[$colMap['packname']] : null;
            $uomVal = isset($colMap['uom'], $row[$colMap['uom']]) && is_numeric($row[$colMap['uom']]) ? (int)$row[$colMap['uom']] : null;
            $principal = isset($colMap['principal'], $row[$colMap['principal']]) && $row[$colMap['principal']] !== '' ? $row[$colMap['principal']] : null;
            $catCode = isset($colMap['product_category_code'], $row[$colMap['product_category_code']]) && $row[$colMap['product_category_code']] !== '' ? $row[$colMap['product_category_code']] : null;
            $compCode = isset($colMap['company_code'], $row[$colMap['company_code']]) && $row[$colMap['company_code']] !== '' ? $row[$colMap['company_code']] : null;

            $isActiveRaw = isset($colMap['is_active'], $row[$colMap['is_active']]) ? trim($row[$colMap['is_active']]) : null;
            $isActive = 1;
            if ($isActiveRaw !== null && $isActiveRaw !== '') {
                if (in_array(strtolower($isActiveRaw), ['0', 'false', 'tidak', 'nonaktif', 'inactive', 'no'])) {
                    $isActive = 0;
                } else {
                    $isActive = 1;
                }
            }

            $existing = Product::where('sku_code', $sku)->first();

            if (!$existing) {
                // Baris baru belum ada -> Simpan ke database
                Product::create([
                    'sku_code' => $sku,
                    'sku_name' => $skuName ?? '',
                    'barcode' => $barcode,
                    'carton_code' => $cartonCode,
                    'packname' => $packname,
                    'uom' => $uomVal ?: 1,
                    'principal' => $principal,
                    'product_category_code' => $catCode,
                    'company_code' => $compCode,
                    'is_active' => $isActive
                ]);
                $insertedCount++;
            } else {
                // Data sudah ada di database
                if ($mode === 'insert_only') {
                    $skippedCount++;
                    continue;
                }

                if ($mode === 'update_only_empty') {
                    // Hanya update field yang saat ini masih kosong / null di database
                    $updatePayload = [];
                    if (empty($existing->sku_name) && !empty($skuName)) $updatePayload['sku_name'] = $skuName;
                    if (empty($existing->barcode) && !empty($barcode)) $updatePayload['barcode'] = $barcode;
                    if (empty($existing->carton_code) && !empty($cartonCode)) $updatePayload['carton_code'] = $cartonCode;
                    if (empty($existing->packname) && !empty($packname)) $updatePayload['packname'] = $packname;
                    if ((empty($existing->uom) || $existing->uom <= 1) && !empty($uomVal)) $updatePayload['uom'] = $uomVal;
                    if (empty($existing->principal) && !empty($principal)) $updatePayload['principal'] = $principal;
                    if (empty($existing->product_category_code) && !empty($catCode)) $updatePayload['product_category_code'] = $catCode;
                    if (empty($existing->company_code) && !empty($compCode)) $updatePayload['company_code'] = $compCode;
                    if ($existing->is_active === null && $isActiveRaw !== null && $isActiveRaw !== '') $updatePayload['is_active'] = $isActive;

                    if (!empty($updatePayload)) {
                        $existing->update($updatePayload);
                        $updatedCount++;
                    } else {
                        $skippedCount++;
                    }
                } else {
                    // Mode 'upsert': Update dengan data dari CSV jika kolom tersebut ada di CSV
                    $updatePayload = [];
                    if ($skuName !== null && $skuName !== '') $updatePayload['sku_name'] = $skuName;
                    if ($barcode !== null) $updatePayload['barcode'] = $barcode;
                    if ($cartonCode !== null) $updatePayload['carton_code'] = $cartonCode;
                    if ($packname !== null) $updatePayload['packname'] = $packname;
                    if ($uomVal !== null) $updatePayload['uom'] = $uomVal;
                    if ($principal !== null) $updatePayload['principal'] = $principal;
                    if ($catCode !== null) $updatePayload['product_category_code'] = $catCode;
                    if ($compCode !== null) $updatePayload['company_code'] = $compCode;
                    if ($isActiveRaw !== null && $isActiveRaw !== '') $updatePayload['is_active'] = $isActive;

                    if (!empty($updatePayload)) {
                        $existing->update($updatePayload);
                        $updatedCount++;
                    } else {
                        $skippedCount++;
                    }
                }
            }
        }
        fclose($handle);

        $msg = "Proses import CSV selesai. Ditambahkan: {$insertedCount} data baru, Di-update: {$updatedCount} data";
        if ($skippedCount > 0) {
            $msg .= ", Dilewati: {$skippedCount} data.";
        } else {
            $msg .= ".";
        }

        return redirect()->route('sodc.products.index')->with('success', $msg);
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
                            'product_category_code' => $p['productCategoryCode'] ?: null,
                            'company_code' => $p['company_code'] ?: null,
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
                        'product_category_code' => $p['productCategoryCode'] ?: null,
                        'company_code' => $p['company_code'] ?: null,
                        'packname' => null,
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
                    'product_category_code' => $item['product_category_code'] ?? null,
                    'company_code' => $item['company_code'] ?? null,
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