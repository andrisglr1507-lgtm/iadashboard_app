@extends('layouts.app')
@section('title', 'Edit Team')
@section('page_title', 'Edit Team')

@section('content')
<div class="content-panel" style="padding: 24px; margin-top: 20px;">
    <form action="{{ isset($item) ? route('sodc.teams.update', $item->id) : route('sodc.teams.store') }}" method="POST">
        @csrf
        @if(isset($item)) @method('PUT') @endif

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Team Code <span style="color:red;">*</span></label>
                <input type='text' name='team_code' required value="{{ $item->team_code ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;' onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'" placeholder="e.g. TM001">
            </div>
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Team Name <span style="color:red;">*</span></label>
                <input type='text' name='team_name' required value="{{ $item->team_name ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;' onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Tipe Tim <span style="color:red;">*</span></label>
                <select name="team_type" required style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;' onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'">
                    <option value="COUNTING" {{ (isset($item) && $item->team_type == 'COUNTING') ? 'selected' : '' }}>COUNTING</option>
                    <option value="RECOUNT" {{ (isset($item) && $item->team_type == 'RECOUNT') ? 'selected' : '' }}>RECOUNT</option>
                </select>
            </div>
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Nomor Urut (Sequence)</label>
                <input type='number' name='sequence_no' value="{{ $item->sequence_no ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;' onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'" placeholder="Otomatis jika kosong">
            </div>
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Status Aktif <span style="color:red;">*</span></label>
                <select name="is_active" required style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;' onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'">
                    <option value="1" {{ (!isset($item) || $item->is_active == 1) ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ (isset($item) && $item->is_active == '0') ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary" style="margin-top: 10px; padding: 10px 20px;">
            <i class="fas fa-save"></i> Simpan Data Team
        </button>
    </form>
</div>
@endsection