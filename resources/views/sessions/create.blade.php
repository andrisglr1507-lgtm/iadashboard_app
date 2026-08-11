@extends('layouts.app')

@section('title', 'Buat Sesi Opname Baru')
@section('page_title', 'Buat Sesi Opname Baru')

@section('page_actions')
<a href="{{ route('sessions.index') }}" class="btn-action" style="padding: 8px 12px; background: #f8fafc; border: 1px solid var(--border); color: var(--text-secondary); text-decoration: none; border-radius: 8px; font-size: 0.8rem; font-weight: 500;">
    <i class="fas fa-arrow-left" style="margin-right: 6px;"></i> Kembali
</a>
@endsection

@section('content')

<div class="content-panel form-panel">
    <div class="panel-header">
        <h2 class="panel-title">Informasi Sesi Opname</h2>
        <p class="panel-subtitle">Lengkapi formulir di bawah ini untuk memulai sesi opname baru.</p>
    </div>

    <form action="{{ route('sessions.store') }}" method="POST" class="premium-form">
        @csrf
        
        <div class="form-grid">
            <!-- Session ID -->
            <div class="form-group">
                <label for="session_id">ID Sesi <span class="required">*</span></label>
                <input type="text" id="session_id" name="session_id" value="{{ $nextId }}" readonly class="form-control readonly-input">
                <small class="form-help">ID otomatis berlanjut dari sistem.</small>
            </div>

            <!-- Cabang -->
            <div class="form-group">
                <label for="branch_id">Cabang <span class="required">*</span></label>
                <input type="text" id="branch_id" name="branch_id" value="{{ old('branch_id') }}" required class="form-control" placeholder="Contoh: JKT-01">
                @error('branch_id') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <!-- Kode Gudang -->
            <div class="form-group">
                <label for="warehouse_code">Kode Gudang <span class="required">*</span></label>
                <input type="text" id="warehouse_code" name="warehouse_code" value="{{ old('warehouse_code') }}" required class="form-control" placeholder="Contoh: WH-UTAMA">
                @error('warehouse_code') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <!-- Bin Location -->
            <div class="form-group">
                <label for="bin_location">Lokasi Bin (Opsional)</label>
                <input type="text" id="bin_location" name="bin_location" value="{{ old('bin_location') }}" class="form-control" placeholder="Contoh: RAK-A1">
                @error('bin_location') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <!-- Periode Start -->
            <div class="form-group">
                <label for="periode_start">Periode Mulai <span class="required">*</span></label>
                <input type="date" id="periode_start" name="periode_start" value="{{ old('periode_start', date('Y-m-d')) }}" required class="form-control">
                @error('periode_start') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <!-- Periode End -->
            <div class="form-group">
                <label for="periode_end">Periode Selesai <span class="required">*</span></label>
                <input type="date" id="periode_end" name="periode_end" value="{{ old('periode_end', date('Y-m-d')) }}" required class="form-control">
                @error('periode_end') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <!-- Mode -->
            <div class="form-group">
                <label for="mode">Mode Opname <span class="required">*</span></label>
                <select id="mode" name="mode" required class="form-control">
                    <option value="S" {{ old('mode') == 'S' ? 'selected' : '' }}>Single Mode (1 Kali Hitung)</option>
                    <option value="D" {{ old('mode') == 'D' ? 'selected' : '' }}>Double Mode (2 Kali Hitung)</option>
                </select>
                @error('mode') <span class="error-text">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-cancel" onclick="window.history.back()">Batal</button>
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i>
                <span>Simpan Sesi</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #0ea5e9;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: color 0.2s;
    }
    
    .btn-back:hover {
        color: #0284c7;
    }
    
    .back-nav {
        display: flex;
        align-items: center;
    }

    .form-panel {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03), 0 1px 3px rgba(0,0,0,0.02);
        padding: 32px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        max-width: 800px;
        margin-top: 20px;
    }

    .panel-header {
        margin-bottom: 28px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 16px;
    }

    .panel-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 6px 0;
        letter-spacing: -0.3px;
    }

    .panel-subtitle {
        font-size: 0.85rem;
        color: #64748b;
        margin: 0;
    }

    .premium-form {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px 24px;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #334155;
    }

    .required {
        color: #ef4444;
    }

    .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 0.9rem;
        color: #1e293b;
        outline: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        background: #f8fafc;
        width: 100%;
        box-sizing: border-box;
    }

    .form-control:focus {
        background: white;
        border-color: #38bdf8;
        box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.15);
    }

    .readonly-input {
        background: #f1f5f9;
        color: #475569;
        cursor: not-allowed;
        font-weight: 600;
    }
    
    .readonly-input:focus {
        border-color: #cbd5e1;
        box-shadow: none;
    }

    .form-help {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .error-text {
        font-size: 0.75rem;
        color: #ef4444;
        font-weight: 500;
        margin-top: 4px;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
        margin-top: 16px;
        padding-top: 24px;
        border-top: 1px solid #f1f5f9;
    }

    .btn-cancel {
        background: white;
        color: #64748b;
        border: 1px solid #e2e8f0;
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-cancel:hover {
        background: #f8fafc;
        color: #334155;
        border-color: #cbd5e1;
    }

    .btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #0ea5e9;
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.2);
    }

    .btn-submit:hover {
        background: #0284c7;
        transform: translateY(-1px);
        box-shadow: 0 6px 8px -1px rgba(14, 165, 233, 0.3);
    }
</style>
@endpush
