@extends('layouts.app')

@section('title', 'Buat Sesi BA Pemeriksaan')
@section('page_title', 'Buat Sesi BA Pemeriksaan')

@section('page_actions')
<a href="{{ route('ba_pemeriksaan.index') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #f1f5f9; color: #475569; text-decoration: none; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; transition: all 0.2s;">
    <i class="fas fa-arrow-left"></i>
    <span>Kembali</span>
</a>
@endsection

@section('content')
<div style="background: white; border-radius: 16px; padding: 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8); margin-top: 20px; max-width: 600px;">
    <div style="margin-bottom: 24px; border-bottom: 1px solid #f1f5f9; padding-bottom: 16px;">
        <h2 style="font-size: 1.25rem; font-weight: 700; color: #0f2b3b; margin: 0 0 8px 0; display: flex; align-items: center;">
            <i class="fas fa-plus-circle" style="color: #0ea5e9; margin-right: 12px; font-size: 1.4rem;"></i>
            Buat Sesi BA Pemeriksaan Baru
        </h2>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Silakan masukkan detail periode dan PIC untuk sesi ini.</p>
    </div>

    <form action="{{ route('ba_pemeriksaan.store') }}" method="POST">
        @csrf
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 0.9rem; font-weight: 600; color: #334155; margin-bottom: 8px;">Periode</label>
            <input type="text" name="periode" required placeholder="Contoh: Juni 2026" style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.95rem; outline: none; transition: border-color 0.2s, box-shadow 0.2s;" onfocus="this.style.borderColor='#0ea5e9'; this.style.boxShadow='0 0 0 3px rgba(14,165,233,0.1)'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'">
        </div>
        
        <div style="margin-bottom: 28px;">
            <label style="display: block; font-size: 0.9rem; font-weight: 600; color: #334155; margin-bottom: 8px;">PIC Pemeriksaan</label>
            <input type="text" name="pic_pemeriksaan" required placeholder="Nama PIC Pemeriksaan" style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.95rem; outline: none; transition: border-color 0.2s, box-shadow 0.2s;" onfocus="this.style.borderColor='#0ea5e9'; this.style.boxShadow='0 0 0 3px rgba(14,165,233,0.1)'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'">
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <a href="{{ route('ba_pemeriksaan.index') }}" style="background: white; color: #475569; border: 1px solid #cbd5e1; padding: 10px 20px; border-radius: 8px; font-size: 0.9rem; font-weight: 600; text-decoration: none; display: inline-block;">Batal</a>
            <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 10px 24px; border-radius: 8px; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.2); transition: background 0.2s;">
                <i class="fas fa-save" style="margin-right: 6px;"></i> Simpan Sesi
            </button>
        </div>
    </form>
</div>
@endsection
