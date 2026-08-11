@extends('layouts.app')
@section('title', 'Edit Team')
@section('page_title', 'Edit Team')

@section('page_actions')
<a href="{{ route('sodc.teams.index') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #64748b; color: white; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
    <i class="fas fa-arrow-left"></i> Kembali
</a>
@endsection

@section('content')
<div style="padding: 24px; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
    <form action="{{ isset($item) ? route('sodc.teams.update', $item->id) : route('sodc.teams.store') }}" method="POST">
        @csrf
        @if(isset($item)) @method('PUT') @endif

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Team Code <span style="color:red;">*</span></label>
                <input type='text' name='team_code' required value="{{ $item->team_code ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;' placeholder="e.g. TM001">
            </div>
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Team Name <span style="color:red;">*</span></label>
                <input type='text' name='team_name' required value="{{ $item->team_name ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Tipe Tim <span style="color:red;">*</span></label>
                <select name="team_type" required style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
                    <option value="COUNTING" {{ (isset($item) && $item->team_type == 'COUNTING') ? 'selected' : '' }}>COUNTING</option>
                    <option value="RECOUNT" {{ (isset($item) && $item->team_type == 'RECOUNT') ? 'selected' : '' }}>RECOUNT</option>
                </select>
            </div>
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Nomor Urut (Sequence)</label>
                <input type='number' name='sequence_no' value="{{ $item->sequence_no ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;' placeholder="Otomatis jika kosong">
            </div>
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Status Aktif <span style="color:red;">*</span></label>
                <select name="is_active" required style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
                    <option value="1" {{ (!isset($item) || $item->is_active == 1) ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ (isset($item) && $item->is_active == '0') ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
        </div>
        
        <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 15px;">Simpan</button>
    </form>
</div>
@endsection