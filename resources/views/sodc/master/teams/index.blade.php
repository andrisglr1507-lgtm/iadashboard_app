@extends('layouts.app')
@section('title', 'Team')
@section('page_title', 'Master Team')

@section('page_actions')
<a href="{{ route('sodc.teams.create') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #0ea5e9; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; text-decoration: none;">
    <i class="fas fa-plus"></i> Tambah Team
</a>
@endsection

@section('content')
@if(session('success'))
    <div style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div style="margin-top: 20px; padding: 24px; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
    <table class="premium-table" style="width: 100%; text-align: left; border-collapse: collapse;">
        <thead>
            <tr>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Team Code</th>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Team Name</th>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Type (COUNTING/RECOUNT)</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>{{ $row->team_code }}</td>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>{{ $row->team_name }}</td>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>{{ $row->team_type }}</td>
                <td>
                    <a href="{{ route('sodc.teams.members', $row->id) }}" style="color: #10b981; margin-right: 10px;"><i class="fas fa-users"></i> Anggota</a>
                    <a href="{{ route('sodc.teams.edit', $row->id) }}" style="color: #0284c7; margin-right: 10px;"><i class="fas fa-edit"></i> Edit</a>
                    <form action="{{ route('sodc.teams.destroy', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus data?');">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:none; border:none; color: #ef4444; cursor:pointer;"><i class="fas fa-trash"></i> Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection