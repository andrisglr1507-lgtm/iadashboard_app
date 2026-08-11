<?php

namespace App\Http\Controllers\Sodc\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bin;

class BinController extends Controller
{
    public function index(Request $request)
    {
        $query = Bin::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('bin_code', 'like', "%{$search}%")
                  ->orWhere('zone', 'like', "%{$search}%");
        }
        
        $data = $query->orderBy('id', 'desc')->paginate(15);
        return view('sodc.master.bins.index', compact('data'));
    }

    public function create()
    {
        $warehouses = \App\Models\Warehouse::all();
        return view('sodc.master.bins.create', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        
        // Auto parse bin_code
        if (!empty($data['bin_code'])) {
            $parsed = Bin::parseBinCodeData($data['bin_code']);
            $data['zone'] = $parsed['zone'];
            $data['ganjil_genap'] = $parsed['ganjil_genap'];
            $data['level'] = $parsed['level'];
        }

        Bin::create($data);
        return redirect()->route('sodc.bins.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        $item = Bin::findOrFail($id);
        $warehouses = \App\Models\Warehouse::all();
        return view('sodc.master.bins.edit', compact('item', 'warehouses'));
    }

    public function update(Request $request, $id)
    {
        $item = Bin::findOrFail($id);
        
        $data = $request->all();
        if (!empty($data['bin_code'])) {
            $parsed = Bin::parseBinCodeData($data['bin_code']);
            $data['zone'] = $parsed['zone'];
            $data['ganjil_genap'] = $parsed['ganjil_genap'];
            $data['level'] = $parsed['level'];
        }

        $item->update($data);
        return redirect()->route('sodc.bins.index')->with('success', 'Data berhasil diupdate');
    }

    public function showImport()
    {
        return view('sodc.master.bins.import');
    }

    public function downloadTemplate()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=template_import_bins.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = array('warehouse_id', 'bin_code', 'bin_type');
        
        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            // Example row
            fputcsv($file, array('1', 'AA.001.01', 'STORAGE'));
            fputcsv($file, array('1', 'BADSTOCK', 'QUARANTINE'));
            
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
        
        $warehouseCache = [];
        $binsToInsert = [];
        $now = now();
        
        // Asumsi format CSV: warehouse_id, bin_code, bin_type
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            if (count($row) < 2) continue;
            
            $whVal = trim($row[0]);
            
            // Caching pencarian gudang agar tidak query berulang-ulang
            if (!array_key_exists($whVal, $warehouseCache)) {
                $warehouse = \App\Models\Warehouse::where('warehouse_code', $whVal)->first();
                $warehouseCache[$whVal] = $warehouse ? $warehouse->id : (is_numeric($whVal) ? $whVal : null);
            }
            
            $whId = $warehouseCache[$whVal];
            if (!$whId) continue;
            
            $bin_code = trim($row[1]);
            $bin_type = isset($row[2]) ? trim($row[2]) : 'STORAGE';
            
            $parsed = Bin::parseBinCodeData($bin_code);
            
            $binsToInsert[] = [
                'warehouse_id' => $whId,
                'bin_code' => $bin_code,
                'zone' => $parsed['zone'],
                'ganjil_genap' => $parsed['ganjil_genap'],
                'level' => $parsed['level'],
                'bin_type' => $bin_type,
                'created_at' => $now,
                'updated_at' => $now
            ];
            
            // Bulk insert setiap 500 baris agar tidak memakan banyak memory/waktu
            if (count($binsToInsert) >= 500) {
                Bin::insert($binsToInsert);
                $binsToInsert = [];
            }
        }
        
        // Insert sisa baris
        if (count($binsToInsert) > 0) {
            Bin::insert($binsToInsert);
        }
        
        fclose($handle);

        return redirect()->route('sodc.bins.index')->with('success', 'Data Bins berhasil diimport dari CSV!');
    }

    public function destroy($id)
    {
        Bin::findOrFail($id)->delete();
        return redirect()->route('sodc.bins.index')->with('success', 'Data berhasil dihapus');
    }
}