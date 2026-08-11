@extends('layouts.app')

@section('title', 'Detail BA Pemeriksaan')
@section('page_title', 'Detail BA Pemeriksaan')

@section('page_actions')
<a href="{{ route('ba_pemeriksaan.index') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #f1f5f9; color: #475569; text-decoration: none; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; transition: all 0.2s;">
    <i class="fas fa-arrow-left"></i>
    <span>Kembali</span>
</a>
@endsection

@section('content')

@if(session('success'))
    <div class="alert-message success" style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
        {{ session('success') }}
    </div>
@endif

<!-- Header Info Card -->
<div style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8); margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0 0 8px 0;">Sesi BA #{{ $header->id_ba }}</h2>
        <div style="display: flex; gap: 24px; color: #64748b; font-size: 0.9rem;">
            <span><i class="far fa-calendar-alt" style="margin-right: 6px;"></i> Periode: <strong>{{ $header->periode }}</strong></span>
            <span><i class="far fa-user" style="margin-right: 6px;"></i> PIC: <strong>{{ $header->pic_pemeriksaan }}</strong></span>
            <span><i class="far fa-clock" style="margin-right: 6px;"></i> Dibuat: <strong>{{ $header->created_at->format('d M Y H:i') }}</strong></span>
        </div>
    </div>
    <div>
        <span style="background: #dcfce7; color: #166534; padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">Status: Done</span>
    </div>
</div>

<!-- Details Table -->
<div class="content-panel table-panel" style="margin-top: 20px; padding: 24px 0 0 0; overflow: hidden; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; padding: 0 24px 20px 24px; border-bottom: 1px solid #f1f5f9;">
        <h2 class="panel-title" style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin: 0;">Data Pemeriksaan ({{ count($header->details) }} baris)</h2>
    </div>

    <div class="table-container" style="width: 100%; overflow-x: auto;">
        <table class="premium-table" id="detailsTable" style="width: 100%; border-collapse: separate; border-spacing: 0; text-align: left;">
            <thead>
                <tr>
                    <th style="background: #f8fafc; padding: 16px 24px; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Cabang</th>
                    <th style="background: #f8fafc; padding: 16px 24px; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Invoice</th>
                    <th style="background: #f8fafc; padding: 16px 24px; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Pelanggan</th>
                    <th style="background: #f8fafc; padding: 16px 24px; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Tgl Faktur</th>
                    <th style="background: #f8fafc; padding: 16px 24px; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; text-align: right;">Nilai Invoice</th>
                    <th style="background: #f8fafc; padding: 16px 24px; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; text-align: center;">Status AR</th>
                </tr>
            </thead>
            <tbody>
                @forelse($header->details as $d)
                    <tr style="transition: all 0.2s ease;">
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.875rem;">{{ $d->cabang }}</td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.875rem;">{{ $d->invoice }}</td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.875rem;">{{ $d->pelanggan }}</td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.875rem;">
                            {{ $d->tgl_faktur ? \Carbon\Carbon::parse($d->tgl_faktur)->format('d M Y') : '-' }}
                        </td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.875rem; text-align: right; font-weight: 600;">
                            Rp {{ number_format($d->nilai_invoice, 0, ',', '.') }}
                        </td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; text-align: center;">
                            @if($d->status_ar)
                                <span style="background: #e0f2fe; color: #0284c7; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">
                                    {{ $d->status_ar }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4" style="color: #8ba0b5; text-align: center; padding: 32px;">
                            <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 8px;"></i>
                            <p>Tidak ada baris data untuk sesi ini.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    /* DataTables Overrides */
    .dataTables_wrapper { padding-top: 10px; }
    .dataTables_length { float: left; padding: 0 24px 20px 24px; font-size: 0.85rem; color: #64748b; font-weight: 500; }
    .dataTables_length select { border: 1px solid #cbd5e1 !important; border-radius: 8px !important; padding: 6px 12px !important; outline: none !important; color: #334155; font-weight: 600; margin: 0 6px !important; }
    .dataTables_filter { float: right; padding: 0 24px 20px 24px; font-size: 0.85rem; color: #64748b; font-weight: 500; }
    .dataTables_filter input { border: 1px solid #cbd5e1 !important; border-radius: 10px !important; padding: 7px 14px !important; outline: none !important; width: 240px !important; }
    .dataTables_info { float: left; padding: 24px !important; font-size: 0.85rem !important; color: #64748b !important; }
    .dataTables_paginate { float: right; padding: 18px 24px !important; display: flex; align-items: center; gap: 4px; }
    .paginate_button { border: 1px solid transparent !important; background: transparent !important; color: #64748b !important; padding: 6px 12px !important; border-radius: 8px !important; font-weight: 600 !important; cursor: pointer !important; margin: 0 !important; }
    .paginate_button.current { background: #e9f0f8 !important; color: #1a6d9f !important; }
    .premium-table tbody tr:hover { background: #f8fafc; }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#detailsTable').DataTable({
            language: { search: "", searchPlaceholder: "Cari data..." },
            pageLength: 25,
            ordering: false
        });
    });
</script>
@endpush
