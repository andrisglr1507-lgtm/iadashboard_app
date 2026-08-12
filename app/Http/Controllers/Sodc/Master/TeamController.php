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
            'team_code' => 'required|unique:opname_teams',
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
            'team_code' => 'required|unique:opname_teams,team_code,'.$id,
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

    public function members($id)
    {
        $team = OpnameTeam::findOrFail($id);
        $members = \App\Models\OpnameTeamMember::with('user')->where('team_id', $id)->get();
        $users = \App\Models\User::where('is_active', true)->get(); 
        return view('sodc.master.teams.members', compact('team', 'members', 'users'));
    }

    public function addMember(Request $request, $id)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        
        // Remove user from any other teams first to prevent overlapping assignments
        \App\Models\OpnameTeamMember::where('user_id', $request->user_id)->delete();

        \App\Models\OpnameTeamMember::create([
            'team_id' => $id,
            'user_id' => $request->user_id,
            'role_in_team' => 'MEMBER',
            'is_active' => true
        ]);
        
        return back()->with('success', 'User berhasil dimasukkan ke dalam tim.');
    }

    public function removeMember($id, $member_id)
    {
        \App\Models\OpnameTeamMember::where('id', $member_id)->where('team_id', $id)->delete();
        return back()->with('success', 'Anggota berhasil dikeluarkan dari tim.');
    }
}