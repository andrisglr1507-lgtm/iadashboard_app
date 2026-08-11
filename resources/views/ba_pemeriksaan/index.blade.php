@extends('layouts.app')

@section('title', 'BA Pemeriksaan')
@section('page_title', 'BA Pemeriksaan')

@section('page_actions')
<a href="{{ route('ba_pemeriksaan.create') }}" class="btn-create-session" style="display: inline-flex; align-items: center; gap: 8px; background: #0ea5e9; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; text-decoration: none; box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.2);">
    <i class="fas fa-plus"></i>
    <span>Buat Sesi BA Baru</span>
</a>
<div class="badge-status">
    <span>{{ count($headers) }} Sesi BA</span>
</div>
@endsection

@section('content')

@if(session('success'))
    <div class="alert-message success" style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
        {{ session('success') }}
    </div>
@endif

<div class="content-panel table-panel" style="margin-top: 20px; padding: 24px 0 0 0; overflow: hidden; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; padding: 0 24px 20px 24px; border-bottom: 1px solid #f1f5f9;">
        <h2 class="panel-title" style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin: 0;">Daftar Sesi BA Pemeriksaan</h2>
    </div>

    <div class="table-container" style="width: 100%; overflow-x: auto;">
        <table class="premium-table" id="headersTable" style="width: 100%; border-collapse: separate; border-spacing: 0; text-align: left;">
            <thead>
                <tr>
                    <th style="background: #f8fafc; padding: 16px 24px; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">ID Sesi BA</th>
                    <th style="background: #f8fafc; padding: 16px 24px; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Periode</th>
                    <th style="background: #f8fafc; padding: 16px 24px; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">PIC Pemeriksaan</th>
                    <th style="background: #f8fafc; padding: 16px 24px; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Dibuat Pada</th>
                    <th class="text-center" style="background: #f8fafc; padding: 16px 24px; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; text-align: center;">Status</th>
                    <th class="text-center" style="background: #f8fafc; padding: 16px 24px; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; text-align: center; width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($headers as $h)
                    <tr style="transition: all 0.2s ease;">
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9;">
                            <span style="color: #0284c7; font-weight: 700; font-family: monospace; font-size: 0.95rem;">
                                #{{ $h->id_ba }}
                            </span>
                        </td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.875rem;">
                            {{ $h->periode }}
                        </td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.875rem;">
                            {{ $h->pic_pemeriksaan }}
                        </td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.85rem; font-weight: 500;">
                            {{ $h->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="text-center" style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; text-align: center;">
                            @if($h->status === 'draft')
                                <span style="background: #fef9c3; color: #854d0e; padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                                    Draft
                                </span>
                            @else
                                <span style="background: #dcfce7; color: #166534; padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                                    Done
                                </span>
                            @endif
                        </td>
                        <td class="text-center" style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; text-align: center;">
                            @if($h->status === 'draft')
                                <a href="{{ route('ba_pemeriksaan.upload', $h->id_ba) }}" style="display: inline-flex; align-items: center; gap: 6px; background: #e0f2fe; color: #0284c7; text-decoration: none; font-size: 0.8rem; font-weight: 600; padding: 6px 12px; border-radius: 8px; transition: all 0.2s;">
                                    <i class="fas fa-upload"></i> Upload Data
                                </a>
                            @else
                                <a href="{{ route('ba_pemeriksaan.detail', $h->id_ba) }}" style="display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; color: #475569; text-decoration: none; font-size: 0.8rem; font-weight: 600; padding: 6px 12px; border-radius: 8px; transition: all 0.2s;">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4" style="color: #8ba0b5; text-align: center; padding: 32px;">
                            <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 8px;"></i>
                            <p>Tidak ada data sesi BA Pemeriksaan.</p>
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
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#headersTable').DataTable({
            language: { search: "", searchPlaceholder: "Cari sesi..." },
            pageLength: 25,
            ordering: false
        });
    });
</script>
@endpush
