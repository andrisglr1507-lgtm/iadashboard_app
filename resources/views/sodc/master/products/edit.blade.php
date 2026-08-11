@extends('layouts.app')
@section('title', 'Edit Product')
@section('page_title', 'Edit Product')

@section('page_actions')
<a href="{{ route('sodc.products.index') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #64748b; color: white; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
    <i class="fas fa-arrow-left"></i> Kembali
</a>
@endsection

@section('content')
<div style="padding: 24px; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
    <form action="{{ isset($item) ? route('sodc.products.update', $item->id) : route('sodc.products.store') }}" method="POST">
        @csrf
        @if(isset($item)) @method('PUT') @endif

        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>SKU Code</label>
            <input type='text' name='sku_code' value="{{ $item->sku_code ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
        </div>
        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>SKU Name</label>
            <input type='text' name='sku_name' value="{{ $item->sku_name ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
        </div>
        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Barcode</label>
            <input type='text' name='barcode' value="{{ $item->barcode ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
        </div>
        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Packname</label>
            <input type='text' name='packname' value="{{ $item->packname ?? '' }}" placeholder="Misal: 1 CTN = 24 PCS" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
        </div>
        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Isi per Karton (UOM)</label>
            <input type='number' name='uom' value="{{ $item->uom ?? 1 }}" min="1" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
        </div>
        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Principal</label>
            <input type='text' name='principal' value="{{ $item->principal ?? '' }}" placeholder="Nama Principal/Merk" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
        </div>
        
        <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 15px;">Simpan</button>
    </form>
</div>
@endsection