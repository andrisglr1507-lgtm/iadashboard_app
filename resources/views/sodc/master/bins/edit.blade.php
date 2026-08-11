@extends('layouts.app')
@section('title', 'Edit Bin')
@section('page_title', 'Edit Bin')

@section('page_actions')
<a href="{{ route('sodc.bins.index') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #64748b; color: white; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
    <i class="fas fa-arrow-left"></i> Kembali
</a>
@endsection

@section('content')
<div style="padding: 24px; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
    <form action="{{ isset($item) ? route('sodc.bins.update', $item->id) : route('sodc.bins.store') }}" method="POST">
        @csrf
        @if(isset($item)) @method('PUT') @endif

        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Gudang <span style="color:red;">*</span></label>
            <select name="warehouse_id" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;' required>
                <option value="">-- Pilih Gudang --</option>
                @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ (isset($item) && $item->warehouse_id == $warehouse->id) ? 'selected' : '' }}>{{ $warehouse->warehouse_name }} ({{ $warehouse->warehouse_code }})</option>
                @endforeach
            </select>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Kode Bin <span style="color:red;">*</span></label>
                <input type='text' name='bin_code' required value="{{ $item->bin_code ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
            </div>
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Nama Bin</label>
                <input type='text' name='bin_name' value="{{ $item->bin_name ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Zona</label>
                <input type='text' name='zone' value="{{ $item->zone ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
            </div>
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Lorong (Aisle)</label>
                <input type='text' name='aisle' value="{{ $item->aisle ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
            </div>
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Rak (Rack)</label>
                <input type='text' name='rack' value="{{ $item->rack ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Level (Angka)</label>
                <input type='number' name='level' value="{{ $item->level ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
            </div>
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Ganjil/Genap</label>
                <select name="ganjil_genap" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
                    <option value="">-- Pilih --</option>
                    <option value="Ganjil" {{ (isset($item) && $item->ganjil_genap == 'Ganjil') ? 'selected' : '' }}>Ganjil</option>
                    <option value="Genap" {{ (isset($item) && $item->ganjil_genap == 'Genap') ? 'selected' : '' }}>Genap</option>
                </select>
            </div>
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>No Level (Teks)</label>
                <input type='text' name='level_no' value="{{ $item->level_no ?? '' }}" style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Tipe Bin <span style="color:red;">*</span></label>
                <select name="bin_type" required style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
                    @foreach(['STORAGE', 'PICKING', 'RECEIVING', 'STAGING', 'QUARANTINE', 'DAMAGED', 'OTHER'] as $type)
                        <option value="{{ $type }}" {{ (isset($item) && $item->bin_type == $type) ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
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