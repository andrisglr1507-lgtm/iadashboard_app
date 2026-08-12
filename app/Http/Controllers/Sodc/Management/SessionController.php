<?php

namespace App\Http\Controllers\Sodc\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpnameSession;

class SessionController extends Controller
{
    public function index()
    {
        $data = OpnameSession::orderBy('id', 'desc')->get();
        return view('sodc.opname_management.sessions.index', compact('data'));
    }

    public function create()
    {
        $references = \App\Models\OpnameReference::orderBy('id', 'desc')->get();
        return view('sodc.opname_management.sessions.create', compact('references'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference_id' => 'nullable|exists:opname_references,id',
            'opname_date' => 'required|date'
        ]);

        $latest = OpnameSession::orderBy('id', 'desc')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        $sessionCode = 'S' . str_pad($nextId, 4, '0', STR_PAD_LEFT); // Format: S0001
        
        $data = $request->all();
        $data['session_code'] = $sessionCode;
        $data['session_uuid'] = \Illuminate\Support\Str::uuid();
        $data['created_by'] = 1; // Default for now
        $data['status'] = 'DRAFT';
        
        OpnameSession::create($data);
        return redirect()->route('sodc.sessions.index')->with('success', 'Session berhasil ditambahkan dengan kode: ' . $sessionCode);
    }

    public function edit($id)
    {
        $item = OpnameSession::findOrFail($id);
        return view('sodc.opname_management.sessions.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = OpnameSession::findOrFail($id);
        $item->update($request->all());
        return redirect()->route('sodc.sessions.index')->with('success', 'Data berhasil diupdate');
    }

    public function destroy($id)
    {
        OpnameSession::findOrFail($id)->delete();
        return redirect()->route('sodc.sessions.index')->with('success', 'Data berhasil dihapus');
    }
}