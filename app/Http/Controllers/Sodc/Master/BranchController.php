<?php

namespace App\Http\Controllers\Sodc\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;

class BranchController extends Controller
{
    public function index()
    {
        $data = Branch::orderBy('id', 'desc')->get();
        return view('sodc.master.branches.index', compact('data'));
    }

    public function create()
    {
        return view('sodc.master.branches.create');
    }

    public function store(Request $request)
    {
        Branch::create($request->all());
        return redirect()->route('sodc.branches.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        $item = Branch::findOrFail($id);
        return view('sodc.master.branches.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Branch::findOrFail($id);
        $item->update($request->all());
        return redirect()->route('sodc.branches.index')->with('success', 'Data berhasil diupdate');
    }

    public function destroy($id)
    {
        Branch::findOrFail($id)->delete();
        return redirect()->route('sodc.branches.index')->with('success', 'Data berhasil dihapus');
    }
}