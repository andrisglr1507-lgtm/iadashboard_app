<?php

namespace App\Http\Controllers\Sodc\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpnameAssignment;

class AssignmentController extends Controller
{
    public function index()
    {
        $data = OpnameAssignment::orderBy('id', 'desc')->get();
        return view('sodc.opname_management.assignments.index', compact('data'));
    }

    public function create()
    {
        return view('sodc.opname_management.assignments.create');
    }

    public function store(Request $request)
    {
        OpnameAssignment::create($request->all());
        return redirect()->route('sodc.assignments.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit($id)
    {
        $item = OpnameAssignment::findOrFail($id);
        return view('sodc.opname_management.assignments.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = OpnameAssignment::findOrFail($id);
        $item->update($request->all());
        return redirect()->route('sodc.assignments.index')->with('success', 'Data berhasil diupdate');
    }

    public function destroy($id)
    {
        OpnameAssignment::findOrFail($id)->delete();
        return redirect()->route('sodc.assignments.index')->with('success', 'Data berhasil dihapus');
    }
}