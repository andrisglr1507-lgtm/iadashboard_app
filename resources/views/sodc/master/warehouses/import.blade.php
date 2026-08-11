@extends('layouts.app')
@section('title', 'Import Warehouse')
@section('page_title', 'Import Master Warehouse')

@section('page_actions')
<a href="{{ route('sodc.warehouses.index') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #64748b; color: white; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
    <i class="fas fa-arrow-left"></i> Kembali
</a>
@endsection

@section('content')
@if(session('error'))
    <div style="margin-bottom: 20px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 8px;">
        <i class="fas fa-times-circle"></i> {{ session('error') }}
    </div>
@endif

<div style="padding: 24px; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
    
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
        <div>
            <h3 style="margin-top: 0; color: #1e293b; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-warehouse" style="color: #107c41; font-size: 1.5rem;"></i> Upload File CSV Warehouse
            </h3>
            <p style="color: #64748b; font-size: 0.95rem; margin: 0;">Silakan upload file master Warehouse Anda. Jika Kode Gudang sudah ada, data akan di-update otomatis.</p>
        </div>
        <a href="{{ route('sodc.warehouses.template') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
            <i class="fas fa-download"></i> Download Template CSV
        </a>
    </div>

    <form action="{{ route('sodc.warehouses.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 40px; text-align: center; margin-bottom: 20px;">
            <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #94a3b8; margin-bottom: 15px;"></i>
            <h4 style="margin: 0 0 10px 0; color: #334155;">Pilih file CSV Anda</h4>
            <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 20px;">Maksimal ukuran file: 2MB</p>
            <input type="file" name="file" accept=".csv" required style="width: 100%; max-width: 300px; margin: 0 auto; display: block; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: white;">
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 1rem; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-check"></i> Proses Import
            </button>
        </div>
    </form>
</div>
@endsection
