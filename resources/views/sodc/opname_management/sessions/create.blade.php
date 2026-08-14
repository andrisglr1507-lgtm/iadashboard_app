@extends('layouts.app')
@section('title', 'Tambah Session')
@section('page_title', 'Tambah Session')

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
            <input type='text' value="[ AUTO GENERATED ]" readonly style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc; color: #64748b; font-weight: bold;'>
            <small style="color: #64748b;">Kode sesi akan otomatis dibuat oleh sistem (maksimal 5 karakter, contoh: S0001).</small>
        </div>

        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Pilih Referensi WMS (Opsional)</label>
            <select name="reference_id" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff;">
                <option value="">-- TANPA REFERENSI (Opname Kosongan / Bad Stock) --</option>
                @foreach($references as $ref)
                    <option value="{{ $ref->id }}">{{ $ref->reference_code }} ({{ date('d-M-Y', strtotime($ref->reference_datetime)) }}) - {{ $ref->total_qty }} Pcs</option>
                @endforeach
            </select>
            <small style="color: #64748b;">Pilih dokumen WMS sebagai acuan hitung. Kosongkan jika ini adalah Opname Bad Stock tanpa data Master Saldo WMS.</small>
        </div>

        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Tanggal Opname (Jadwal) <span style="color:red;">*</span></label>
            <input type='date' name='opname_date' required style='width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;'>
        </div>

        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Mode Opname <span style="color:red;">*</span></label>
            <select name="mode" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff;">
                <option value="DOUBLE">DOUBLE - A vs B (Hitungan Fisik A dibanding Hitungan Fisik B)</option>
                <option value="SINGLE">SINGLE - WMS vs Single Team (Hitungan Fisik dibanding Saldo WMS)</option>
                <option value="RECORD_ONLY">RECORD ONLY - Murni mencatat hasil hitung fisik (Tanpa Komparasi)</option>
            </select>
        </div>

        <div style='margin-bottom: 15px;'>
            <label style='display:block; margin-bottom:5px; font-weight:600; color:#334155;'>Metode Perhitungan <span style="color:red;">*</span></label>
            <select name="counting_method" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff;">
                <option value="BLIND">BLIND - Tim tidak tahu qty saldo</option>
                <option value="OPEN">OPEN - Tim bisa melihat qty target</option>
            </select>
        </div>
        
        <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 15px;">Simpan</button>
    </form>
</div>
@endsection