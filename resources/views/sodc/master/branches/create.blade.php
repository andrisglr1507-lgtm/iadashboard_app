@extends('layouts.app')
@section('title', 'Tambah Branch')
@section('page_title', 'Tambah Branch')

@section('page_actions')
<a href="{{ route('sodc.branches.index') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #64748b; color: white; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
    <i class="fas fa-arrow-left"></i> Kembali
</a>
@endsection

@section('content')
<div style="padding: 24px; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
    <form action="{{ isset($item) ? route('sodc.branches.update', $item->id) : route('sodc.branches.store') }}" method="POST">
        @csrf
        @if(isset($item)) @method('PUT') @endif

        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Kode Cabang</label>
            <input type='text' name='branch_code' value="{{ $item->branch_code ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
        </div>
        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Nama Cabang</label>
            <input type='text' name='branch_name' value="{{ $item->branch_name ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
        </div>
        
        <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 15px;">Simpan</button>
    </form>
</div>
@endsection