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
        
        $columns = array('sku_code', 'sku_name', 'barcode', 'packname', 'uom', 'principal');
        
        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, array('PRD001', 'Kopi Saset ABC', '8991234567890', '1 CTN = 24 PCS', 24, 'ABC Group'));
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
                    'packname' => $row[3] ?: null,
                    'uom' => isset($row[4]) && is_numeric($row[4]) ? (int)$row[4] : 1,
                    'principal' => $row[5] ?: null,
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
}