@extends('layouts.app')
@section('title', 'Edit Warehouse')
@section('page_title', 'Edit Warehouse')

@section('page_actions')
<a href="{{ route('sodc.warehouses.index') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #64748b; color: white; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
    <i class="fas fa-arrow-left"></i> Kembali
</a>
@endsection

@section('content')
<div style="padding: 24px; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
    <form action="{{ isset($item) ? route('sodc.warehouses.update', $item->id) : route('sodc.warehouses.store') }}" method="POST">
        @csrf
        @if(isset($item)) @method('PUT') @endif

        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Cabang</label>
            <select name="branch_id" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;' required>
                <option value="">-- Pilih Cabang --</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ (isset($item) && $item->branch_id == $branch->id) ? 'selected' : '' }}>{{ $branch->branch_name }} ({{ $branch->branch_code }})</option>
                @endforeach
            </select>
        </div>
        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Kode Gudang</label>
            <input type='text' name='warehouse_code' value="{{ $item->warehouse_code ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
        </div>
        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Nama Gudang</label>
            <input type='text' name='warehouse_name' value="{{ $item->warehouse_name ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
        </div>
        
        <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 15px;">Simpan</button>
    </form>
</div>
@endsection