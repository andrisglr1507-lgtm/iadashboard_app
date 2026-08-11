<?php

namespace App\Http\Controllers\Sodc\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpnameTeam;

class TeamController extends Controller
{
    public function index()
    {
        $data = OpnameTeam::orderBy('id', 'desc')->get();
        return view('sodc.master.teams.index', compact('data'));
    }

    public function create()
    {
        return view('sodc.master.teams.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'team_code' => 'required|unique:equuddbx_so_dc.opname_teams',
            'team_name' => 'required',
            'team_type' => 'required'
        ]);

        $data = $request->all();
        if (!isset($data['sequence_no'])) {
            $latest = OpnameTeam::orderBy('sequence_no', 'desc')->first();
            $data['sequence_no'] = $latest ? $latest->sequence_no + 1 : 1;
        }

        OpnameTeam::create($data);
        return redirect()->route('sodc.teams.index')->with('success', 'Data Team berhasil ditambahkan');
    }

    public function edit($id)
    {
        $item = OpnameTeam::findOrFail($id);
        return view('sodc.master.teams.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = OpnameTeam::findOrFail($id);
        $request->validate([
            'team_code' => 'required|unique:equuddbx_so_dc.opname_teams,team_code,'.$id,
            'team_name' => 'required',
            'team_type' => 'required'
        ]);

        $item->update($request->all());
        return redirect()->route('sodc.teams.index')->with('success', 'Data Team berhasil diupdate');
    }

    public function destroy($id)
    {
        OpnameTeam::findOrFail($id)->delete();
        return redirect()->route('sodc.teams.index')->with('success', 'Data berhasil dihapus');
    }
}