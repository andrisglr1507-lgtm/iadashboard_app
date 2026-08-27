<?php

namespace App\Http\Controllers\Sodc\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BadStockRecord;

class BadStockController extends Controller
{
    public function index(Request $request)
    {
        $query = BadStockRecord::with(['product', 'user']);

        // Optional filtering by category or status
        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }

        if ($request->has('item_status') && $request->item_status != '') {
            $query->where('item_status', $request->item_status);
        }

        if ($request->has('koli') && $request->koli != '') {
            $query->where('koli', $request->koli);
        }

        $records = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('sodc.management.bad_stock.index', compact('records'));
    }

    public function print(Request $request)
    {
        $query = BadStockRecord::with(['product', 'user'])->orderBy('created_at', 'desc');

        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }

        if ($request->has('item_status') && $request->item_status != '') {
            $query->where('item_status', $request->item_status);
        }

        if ($request->has('koli') && $request->koli != '') {
            $query->where('koli', $request->koli);
        }

        $records = $query->get();

        return view('sodc.management.bad_stock.print', compact('records'));
    }

    public function edit($id)
    {
        $record = BadStockRecord::with('product')->findOrFail($id);
        return view('sodc.management.bad_stock.edit', compact('record'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'qty_carton' => 'required|integer|min:0',
            'qty_pcs' => 'required|integer|min:0',
            'total_qty' => 'required|integer|min:0',
            'koli' => 'nullable|integer',
            'item_status' => 'required|in:SALEABLE,NON_SALEABLE',
            'category' => 'required|in:BAD_STOCK,EXPIRED_STOCK',
            'batch_no' => 'nullable|string|max:100',
            'expired_date' => 'nullable|date',
        ]);

        $record = BadStockRecord::findOrFail($id);
        $record->update($request->only([
            'qty_carton', 'qty_pcs', 'total_qty', 'koli', 'item_status', 'category', 'batch_no', 'expired_date'
        ]));

        return redirect()->route('sodc.bad_stock.index')->with('success', 'Data Bad Stock berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $record = BadStockRecord::findOrFail($id);
        $record->delete();

        return redirect()->route('sodc.bad_stock.index')->with('success', 'Data Bad Stock berhasil dihapus!');
    }
}
