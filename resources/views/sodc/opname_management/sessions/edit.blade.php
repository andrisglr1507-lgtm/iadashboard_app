@extends('layouts.app')
@section('title', 'Edit Session')
@section('page_title', 'Edit Session')

@section('page_actions')
<a href="{{ route('sodc.sessions.index') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #64748b; color: white; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
    <i class="fas fa-arrow-left"></i> Kembali
</a>
@endsection

@section('content')
<div style="padding: 24px; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
    <form action="{{ isset($item) ? route('sodc.sessions.update', $item->id) : route('sodc.sessions.store') }}" method="POST">
        @csrf
        @if(isset($item)) @method('PUT') @endif

        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Session Code</label>
            <input type='text' name='session_code' value="{{ $item->session_code ?? '' }}" readonly style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc; color: #64748b; font-weight: bold;'>
        </div>

        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Tanggal Opname</label>
            <input type='date' name='opname_date' value="{{ $item->opname_date ?? '' }}" required style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
        </div>

        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Mode Opname <span style="color:red;">*</span></label>
            <select name="mode" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff;">
                <option value="DOUBLE" {{ (isset($item) && $item->mode == 'DOUBLE') ? 'selected' : '' }}>DOUBLE - A vs B (Hitungan Fisik A dibanding Hitungan Fisik B)</option>
                <option value="SINGLE" {{ (isset($item) && $item->mode == 'SINGLE') ? 'selected' : '' }}>SINGLE - WMS vs Single Team (Hitungan Fisik dibanding Saldo WMS)</option>
                <option value="RECORD_ONLY" {{ (isset($item) && $item->mode == 'RECORD_ONLY') ? 'selected' : '' }}>RECORD ONLY - Murni mencatat hasil hitung fisik (Tanpa Komparasi)</option>
            </select>
        </div>

        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Metode Perhitungan <span style="color:red;">*</span></label>
            <select name="counting_method" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff;">
                <option value="BLIND" {{ (isset($item) && $item->counting_method == 'BLIND') ? 'selected' : '' }}>BLIND - Tim tidak tahu kunci jawaban qty saldo</option>
                <option value="OPEN" {{ (isset($item) && $item->counting_method == 'OPEN') ? 'selected' : '' }}>OPEN - Tim bisa melihat qty target</option>
            </select>
        </div>
        
        <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 15px;">Simpan</button>
    </form>
</div>
@endsection