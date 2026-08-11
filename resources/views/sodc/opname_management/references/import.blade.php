@extends('layouts.app')
@section('title', 'Import WMS Reference')
@section('page_title', 'Import Data WMS')

@section('page_actions')
<a href="{{ route('sodc.references.index') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #64748b; color: white; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
    <i class="fas fa-arrow-left"></i> Kembali
</a>
@endsection

@section('content')
@if(session('error'))
    <div style="margin-bottom: 20px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 8px;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif

<div style="padding: 24px; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
    <div style="display: flex; gap: 20px; align-items: flex-start;">
        <div style="flex: 1;">
            <h3 style="margin-top: 0; color: #0f172a;">Upload Dokumen Referensi WMS</h3>
            <p style="color: #64748b; font-size: 0.95rem; margin: 0;">Pilih Gudang target dan upload CSV hasil ekspor Warehouse Management System Anda.</p>
            <p style="color: #475569; font-size: 0.85rem; margin: 10px 0 0 0; background: #f8fafc; padding: 10px; border-radius: 6px; border: 1px dashed #cbd5e1;">
                <b>Format Kolom CSV:</b><br>
                <code style="color: #e21d48;">bin_code, sku_code, system_qty, uom, batch_number, expired_date</code>
            </p>
        </div>
        <a href="{{ route('sodc.references.template') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
            <i class="fas fa-download"></i> Download Template
        </a>
    </div>

    <form action="{{ route('sodc.references.import') }}" method="POST" enctype="multipart/form-data" style="margin-top: 24px;">
        @csrf
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: 600; color: #334155; margin-bottom: 8px;">File CSV WMS <span style="color: red;">*</span></label>
            <input type="file" name="file" accept=".csv" required style="display: block; width: 100%; max-width: 400px; padding: 10px; border: 1px dashed #94a3b8; border-radius: 8px; background: #f8fafc;">
            <small style="color: #64748b; margin-top: 5px; display: block;">Pastikan Bin dan SKU yang ada di dalam CSV sudah terdaftar di Master Data Anda. <br><b>Info:</b> Sistem akan otomatis mendeteksi Gudang (Warehouse) berdasarkan kode Bin yang ada di dalam file.</small>
        </div>

        <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-upload"></i> Upload & Proses Referensi
        </button>
    </form>
</div>
@endsection
