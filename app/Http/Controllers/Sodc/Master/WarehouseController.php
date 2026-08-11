<?php

namespace App\Http\Controllers\Sodc\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Warehouse;

class WarehouseController extends Controller
{
    public function index()
    {
        $data = Warehouse::orderBy('id', 'desc')->get();
        return view('sodc.master.warehouses.index', compact('data'));
    }

    public function create()
    {
        $branches = \App\Models\Branch::all();
        return view('sodc.master.warehouses.create', compact('branches'));
    }

    public function store(Request $request)
    {
        Warehouse::create($request->all());
        return redirect()->route('sodc.warehouses.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        $item = Warehouse::findOrFail($id);
        $branches = \App\Models\Branch::all();
        return view('sodc.master.warehouses.edit', compact('item', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $item = Warehouse::findOrFail($id);
        $item->update($request->all());
        return redirect()->route('sodc.warehouses.index')->with('success', 'Data berhasil diupdate');
    }

    public function showImport()
    {
        return view('sodc.master.warehouses.import');
    }

    public function downloadTemplate()
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=template_import_warehouses.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = array('branch_id', 'warehouse_code', 'warehouse_name');
        
        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, array('1', 'WH-A', 'Gudang Utama A'));
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
            
            $branchVal = trim($row[0]);
            $branchId = null;
            
            // Coba cari berdasarkan branch_code (misal: 'COGBYR')
            $branch = \App\Models\Branch::where('branch_code', $branchVal)->first();
            if ($branch) {
                $branchId = $branch->id;
            } elseif (is_numeric($branchVal)) {
                $branchId = $branchVal; // Fallback jika user benar-benar isi angka ID
            }

            if (!$branchId) continue; // Lewati jika branch tidak ditemukan
            
            Warehouse::updateOrCreate(
                ['warehouse_code' => trim($row[1])],
                [
                    'branch_id' => $branchId,
                    'warehouse_name' => isset($row[2]) ? trim($row[2]) : ''
                ]
            );
        }
        fclose($handle);

        return redirect()->route('sodc.warehouses.index')->with('success', 'Data Warehouses berhasil diimport dari CSV!');
    }

    public function destroy($id)
    {
        Warehouse::findOrFail($id)->delete();
        return redirect()->route('sodc.warehouses.index')->with('success', 'Data berhasil dihapus');
    }
}