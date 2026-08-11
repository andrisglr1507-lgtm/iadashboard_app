@extends('layouts.app')

@section('title', 'Session Opname')
@section('page_title', 'Session Opname')

@section('page_actions')
<a href="{{ route('sessions.create') }}" class="btn-create-session">
    <i class="fas fa-plus"></i>
    <span>Buat Sesi Baru</span>
</a>
<div class="badge-status">
    <span>{{ count($sessions) }} Sesi Opname</span>
</div>
@endsection

@section('content')

@if(session('success'))
    <div class="alert-message success" style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46;">
        <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert-message error" style="margin-bottom: 20px;">
        <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>
        {{ session('error') }}
    </div>
@endif

<div class="content-panel table-panel">
    <div class="panel-header" style="border-bottom: none; padding-bottom: 8px;">
        <h2 class="panel-title">Daftar Sesi Opname</h2>
    </div>

    <div class="table-container">
        <table class="premium-table" id="sessionsTable">
            <thead>
                <tr>
                    <th>ID Sesi</th>
                    <th>Cabang</th>
                    <th>Kode Gudang</th>
                    <th>Bin Location</th>
                    <th>Periode</th>
                    <th class="text-center">Mode</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr>
                        <td class="text-id font-mono">{{ $session->session_id }}</td>
                        <td>{{ $session->branch_id }}</td>
                        <td>{{ $session->warehouse_code }}</td>
                        <td><span class="bin-badge">{{ $session->bin_location }}</span></td>
                        <td class="font-date">
                            {{ $session->periode_start->format('d M Y') }} - {{ $session->periode_end->format('d M Y') }}
                        </td>
                        <td class="text-center">
                            @if($session->mode === 'D')
                                <span class="mode-badge mode-double" title="Double Mode">Double</span>
                            @else
                                <span class="mode-badge mode-single" title="Single Mode">Single</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="status-badge status-{{ $session->status }}">
                                {{ ucfirst($session->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($session->status === 'draft')
                                <a href="{{ route('sessions.upload', $session->session_id) }}" class="btn-action btn-upload-action">
                                    <i class="fas fa-upload"></i>
                                    <span>Upload Data</span>
                                </a>
                            @else
                                <a href="#" class="btn-action btn-detail-action" onclick="alert('Hanya sesi draft yang dapat diunggah datanya.'); return false;">
                                    <i class="fas fa-eye"></i>
                                    <span>Lihat Detail</span>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4" style="color: #8ba0b5;">
                            <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 8px;"></i>
                            <p>Tidak ada data sesi opname ditemukan.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('styles')
<!-- Load DataTables Default CSS via CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    .btn-create-session {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #0ea5e9;
        color: white;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 10px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.2);
    }
    
    .btn-create-session:hover {
        background: #0284c7;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 6px 8px -1px rgba(14, 165, 233, 0.3);
    }

    /* Panel Styling */
    .table-panel {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03), 0 1px 3px rgba(0,0,0,0.02);
        padding: 24px 0 0 0;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    
    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 24px 20px 24px;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .panel-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        letter-spacing: -0.3px;
    }
    
    .table-container {
        width: 100%;
        overflow-x: auto;
    }
    
    /* Premium Table */
    .premium-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        text-align: left;
    }
    
    .premium-table th {
        background: #f8fafc;
        padding: 16px 24px;
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e2e8f0;
        border-top: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    
    .premium-table td {
        padding: 16px 24px;
        font-size: 0.875rem;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        white-space: nowrap;
    }
    
    .premium-table tbody tr {
        transition: all 0.2s ease;
    }
    
    .premium-table tbody tr:hover {
        background: #f8fafc;
        transform: scale(1.001);
    }
    
    /* Typography Utilities */
    .text-id {
        color: #0284c7;
        font-weight: 700;
        font-size: 0.85rem;
    }
    
    .font-date {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
    }
    
    /* Badges */
    .bin-badge {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #475569;
        font-family: 'Courier New', Courier, monospace;
        letter-spacing: 0.5px;
    }
    
    .mode-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .mode-single {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .mode-double {
        background: #f3e8ff;
        color: #6b21a8;
    }
    
    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }
    
    .status-draft { background: #f1f5f9; color: #475569; }
    .status-counting { background: #fef3c7; color: #b45309; }
    .status-recount { background: #e0f2fe; color: #0369a1; }
    .status-final { background: #dcfce7; color: #15803d; }
    .status-closed { background: #fee2e2; color: #b91c1c; }
    
    /* Action Buttons */
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 10px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid transparent;
    }
    
    .btn-upload-action {
        background: #ecfdf5;
        color: #059669;
        border-color: #a7f3d0;
    }
    
    .btn-upload-action:hover {
        background: #10b981;
        color: white;
        border-color: #10b981;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2), 0 2px 4px -1px rgba(16, 185, 129, 0.1);
    }
    
    .btn-detail-action {
        background: #f8fafc;
        color: #64748b;
        border-color: #e2e8f0;
    }
    
    .btn-detail-action:hover {
        background: #0ea5e9;
        color: white;
        border-color: #0ea5e9;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.2);
    }
    
    /* Alert Message */
    .alert-message {
        padding: 16px 20px;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 500;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    
    .alert-message.error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    
    /* ==========================================
       DATATABLES PREMIUM STYLE OVERRIDES
       ========================================== */
    .dataTables_wrapper {
        position: relative;
        padding-top: 10px;
    }
    
    /* Controls Top Bar */
    .dataTables_length {
        float: left;
        padding: 0 24px 20px 24px;
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
    }
    
    .dataTables_length select {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        padding: 6px 12px !important;
        outline: none !important;
        color: #334155;
        font-weight: 600;
        margin: 0 6px !important;
        background: white;
        transition: all 0.2s;
    }
    .dataTables_length select:focus {
        border-color: #38bdf8 !important;
        box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15) !important;
    }
    
    .dataTables_filter {
        float: right;
        padding: 0 24px 20px 24px;
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
    }
    
    .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .dataTables_filter input {
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        padding: 8px 16px !important;
        outline: none !important;
        color: #334155;
        font-size: 0.85rem !important;
        width: 260px !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        margin-left: 0 !important;
        background: #f8fafc;
    }
    
    .dataTables_filter input:focus {
        background: white;
        border-color: #38bdf8 !important;
        box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.15) !important;
    }
    
    /* Table borders resetting */
    table.dataTable.no-footer {
        border-bottom: 1px solid #f1f5f9 !important;
    }
    
    table.dataTable th, table.dataTable td {
        border-bottom: 1px solid #f1f5f9 !important;
    }
    
    /* Info Label Bottom */
    .dataTables_info {
        float: left;
        padding: 24px !important;
        font-size: 0.85rem !important;
        color: #64748b !important;
        font-weight: 500 !important;
    }
    
    /* Pagination Control Bottom */
    .dataTables_paginate {
        float: right;
        padding: 16px 24px !important;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    /* Pagination Buttons overrides */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: 1px solid transparent !important;
        background: transparent !important;
        color: #64748b !important;
        padding: 8px 14px !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
        font-size: 0.85rem !important;
        cursor: pointer !important;
        transition: all 0.2s !important;
        margin: 0 !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #0ea5e9 !important;
        color: white !important;
        border-color: #0ea5e9 !important;
        box-shadow: 0 2px 4px rgba(14, 165, 233, 0.2) !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
        background: #f1f5f9 !important;
        color: #0f172a !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        color: #cbd5e1 !important;
        cursor: not-allowed !important;
    }
    
    @media (max-width: 640px) {
        .dataTables_length, .dataTables_filter {
            float: none;
            width: 100%;
            padding-bottom: 16px;
        }
        .dataTables_filter input {
            width: 100% !important;
        }
        .dataTables_info, .dataTables_paginate {
            float: none;
            width: 100%;
            justify-content: center;
            text-align: center;
        }
    }
</style>
@endpush

@push('scripts')
<!-- Load jQuery & DataTables JS via CDN -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        if ($('#sessionsTable tbody tr').length > 0 && !$('#sessionsTable tbody tr td').hasClass('dataTables_empty')) {
            $('#sessionsTable').DataTable({
                pageLength: 15,
                lengthMenu: [10, 15, 25, 50],
                order: [[4, 'desc']], // Sort by period start date by default
                language: {
                    search: "",
                    searchPlaceholder: "Cari sesi...",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ sesi",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 sesi",
                    infoFiltered: "(difilter dari _MAX_ total sesi)",
                    paginate: {
                        first: '<i class="fas fa-angle-double-left"></i>',
                        previous: '<i class="fas fa-angle-left"></i>',
                        next: '<i class="fas fa-angle-right"></i>',
                        last: '<i class="fas fa-angle-double-right"></i>'
                    },
                    zeroRecords: "Tidak ada data sesi yang cocok ditemukan."
                }
            });
        }
    });
</script>
@endpush
