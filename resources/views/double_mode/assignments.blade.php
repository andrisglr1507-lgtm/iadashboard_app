@extends('layouts.app')

@section('title', 'Double Mode - Monitoring Assignment')
@section('page_title', 'Monitoring Assignment (A Vs B)')

@section('page_actions')
@if(!empty($activeSessionId) && !$error)
<div class="assignment-stats-inline">
    <span class="stat-pill total"><i class="fas fa-layer-group"></i> Total: <strong>{{ $stats['total'] }}</strong></span>
    <span class="stat-pill assigned"><i class="fas fa-clock"></i> Assigned: <strong>{{ $stats['assigned'] }}</strong></span>
    <span class="stat-pill distributed"><i class="fas fa-paper-plane"></i> Distributed: <strong>{{ $stats['distributed'] }}</strong></span>
    <span class="stat-pill in-progress"><i class="fas fa-spinner"></i> In Progress: <strong>{{ $stats['in_progress'] }}</strong></span>
    <span class="stat-pill completed"><i class="fas fa-check-circle"></i> Completed: <strong>{{ $stats['completed'] }}</strong></span>
</div>
@endif
@endsection

@section('content')

@if($error)
    <div class="alert-box alert-error">
        <i class="fas fa-exclamation-triangle"></i>
        <div>{{ $error }}</div>
    </div>
@else

    {{-- Status Filter Tabs --}}
    <div class="filter-tabs">
        <button class="filter-tab active" data-filter="">Semua <span class="tab-count">{{ $stats['total'] }}</span></button>
        <button class="filter-tab" data-filter="assigned">Assigned <span class="tab-count">{{ $stats['assigned'] }}</span></button>
        <button class="filter-tab" data-filter="distributed">Distributed <span class="tab-count">{{ $stats['distributed'] }}</span></button>
        <button class="filter-tab" data-filter="in_progress">In Progress <span class="tab-count">{{ $stats['in_progress'] }}</span></button>
        <button class="filter-tab" data-filter="completed">Completed <span class="tab-count">{{ $stats['completed'] }}</span></button>
    </div>

    {{-- Table --}}
    <div class="content-panel table-panel">
        <div class="table-container">
            <table class="premium-table" id="assignmentTable">
                <thead>
                    <tr>
                        <th width="40" class="text-center">No</th>
                        <th>Product ID</th>
                        <th>Nama Produk</th>
                        <th>Principal</th>
                        <th>Lokasi</th>
                        <th class="text-center">Round</th>
                        <th>Ditugaskan ke</th>
                        <th>Ditugaskan oleh</th>
                        <th class="text-center">Status</th>
                        <th>Waktu Assign</th>
                        <th>Waktu Distributed</th>
                        <th>Waktu Mulai</th>
                        <th>Waktu Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $i => $a)
                        <tr data-status="{{ $a->status }}" data-id="{{ $a->assignment_id }}">
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td class="font-mono text-id">{{ $a->id_product }}</td>
                            <td title="{{ $a->product_name }}">{{ Str::limit($a->product_name ?? '-', 35) }}</td>
                            <td>{{ $a->principal ?? '-' }}</td>
                            <td><span class="bin-badge">{{ $a->location_code }}</span></td>
                            <td class="text-center">
                                <span class="round-badge round-{{ $a->round_number }}">R{{ $a->round_number - 1 }}</span>
                            </td>
                            <td>
                                <div class="user-cell">
                                    <span class="user-avatar">{{ strtoupper(substr($a->assignee_name ?? '?', 0, 1)) }}</span>
                                    <span>{{ $a->assignee_name ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="text-secondary">{{ $a->assigner_name ?? '-' }}</td>
                            <td class="text-center">
                                <span class="status-badge status-{{ $a->status }}">
                                    @if($a->status === 'assigned') <i class="fas fa-clock"></i>
                                    @elseif($a->status === 'distributed') <i class="fas fa-paper-plane"></i>
                                    @elseif($a->status === 'in_progress') <i class="fas fa-spinner fa-spin"></i>
                                    @elseif($a->status === 'completed') <i class="fas fa-check-circle"></i>
                                    @else <i class="fas fa-info-circle"></i>
                                    @endif
                                    {{ ucfirst(str_replace('_', ' ', $a->status)) }}
                                </span>
                            </td>
                            <td class="font-date">{{ $a->assigned_at ? \Carbon\Carbon::parse($a->assigned_at)->format('d/m/Y H:i') : '-' }}</td>
                            <td class="font-date">{{ $a->distributed_at ? \Carbon\Carbon::parse($a->distributed_at)->format('d/m/Y H:i') : '-' }}</td>
                            <td class="font-date">{{ $a->started_at ? \Carbon\Carbon::parse($a->started_at)->format('d/m/Y H:i') : '-' }}</td>
                            <td class="font-date">{{ $a->submitted_at ? \Carbon\Carbon::parse($a->submitted_at)->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center" style="padding: 60px 24px; color: #94a3b8;">
                                <i class="fas fa-inbox" style="font-size: 2.5rem; margin-bottom: 12px; display: block;"></i>
                                <p style="font-weight: 600; margin-bottom: 4px;">Belum ada data assignment</p>
                                <p style="font-size: 0.8rem;">Assignment akan muncul setelah user di-assign pada halaman Recount A Vs B.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endif

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    /* ==========================================
       ASSIGNMENT PAGE STYLES
       ========================================== */

    /* Stats inline */
    .assignment-stats-inline {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 600;
        border: 1px solid;
    }

    .stat-pill i { font-size: 0.65rem; }
    .stat-pill strong { font-weight: 800; }

    .stat-pill.total { background: #f8fafc; border-color: #e2e8f0; color: #475569; }
    .stat-pill.assigned { background: #fef3c7; border-color: #fde68a; color: #92400e; }
    .stat-pill.distributed { background: #e0e7ff; border-color: #a5b4fc; color: #3730a3; }
    .stat-pill.in-progress { background: #dbeafe; border-color: #bfdbfe; color: #1e40af; }
    .stat-pill.completed { background: #dcfce7; border-color: #86efac; color: #15803d; }

    /* Alert box */
    .alert-box {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 10px;
        margin-bottom: 16px;
        font-size: 0.85rem;
    }
    .alert-box i { margin-top: 2px; }
    .alert-error {
        background: #fff1f2;
        border: 1px solid #fecdd3;
        color: #be123c;
    }

    /* Filter Tabs */
    .filter-tabs {
        display: flex;
        gap: 4px;
        margin-bottom: 16px;
        background: #f8fafc;
        padding: 4px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        width: fit-content;
    }

    .filter-tab {
        padding: 8px 16px;
        border: none;
        background: transparent;
        border-radius: 8px;
        font-size: 0.78rem;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .filter-tab:hover {
        background: #e2e8f0;
        color: #334155;
    }

    .filter-tab.active {
        background: white;
        color: #0f172a;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    .tab-count {
        background: #e2e8f0;
        color: #475569;
        padding: 1px 7px;
        border-radius: 10px;
        font-size: 0.68rem;
        font-weight: 700;
    }

    .filter-tab.active .tab-count {
        background: #0ea5e9;
        color: white;
    }

    /* Table Panel */
    .table-panel {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03), 0 1px 3px rgba(0,0,0,0.02);
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, 0.8);
    }

    .table-container { width: 100%; overflow-x: auto; }

    .premium-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        text-align: left;
    }

    .premium-table th {
        background: #f8fafc;
        padding: 14px 16px;
        font-size: 0.72rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e2e8f0;
        border-top: 1px solid #e2e8f0;
        white-space: nowrap;
    }

    .premium-table td {
        padding: 12px 16px;
        font-size: 0.82rem;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        white-space: nowrap;
    }

    .premium-table tbody tr {
        transition: all 0.15s ease;
    }

    .premium-table tbody tr:hover { background: #f8fafc; }

    /* Typography */
    .font-mono { font-family: 'Courier New', monospace; font-weight: 600; }
    .text-id { color: #0284c7; font-weight: 700; font-size: 0.82rem; }
    .font-date { font-size: 0.78rem; color: #64748b; font-weight: 500; }
    .text-secondary { color: #94a3b8; }

    /* Bin badge */
    .bin-badge {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 600;
        color: #475569;
        font-family: 'Courier New', monospace;
    }

    /* Round badge */
    .round-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.5px;
    }

    .round-2 { background: #fef3c7; color: #92400e; }
    .round-3 { background: #fee2e2; color: #b91c1c; }

    /* User cell */
    .user-cell {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .user-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.68rem;
        font-weight: 800;
        flex-shrink: 0;
    }

    /* Status badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .status-assigned { background: #fef3c7; color: #92400e; }
    .status-distributed { background: #e0e7ff; color: #3730a3; }
    .status-in_progress { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #dcfce7; color: #15803d; }

    /* Row highlight for completed */
    tr[data-status="completed"] {
        background: #f0fdf4 !important;
    }
    tr[data-status="completed"]:hover {
        background: #dcfce7 !important;
    }

    /* DataTables overrides */
    .dataTables_wrapper { position: relative; padding-top: 10px; }

    .dataTables_length {
        float: left; padding: 8px 16px 16px; font-size: 0.82rem; color: #64748b; font-weight: 500;
    }
    .dataTables_length select {
        border: 1px solid #cbd5e1 !important; border-radius: 8px !important;
        padding: 5px 10px !important; outline: none !important; color: #334155;
        font-weight: 600; margin: 0 6px !important; background: white;
    }
    .dataTables_filter {
        float: right; padding: 8px 16px 16px; font-size: 0.82rem; color: #64748b;
    }
    .dataTables_filter input {
        border: 1px solid #e2e8f0 !important; border-radius: 10px !important;
        padding: 7px 14px !important; outline: none !important; font-size: 0.82rem !important;
        width: 220px !important; background: #f8fafc; transition: all 0.2s !important;
    }
    .dataTables_filter input:focus {
        background: white; border-color: #38bdf8 !important;
        box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.12) !important;
    }
    table.dataTable.no-footer { border-bottom: 1px solid #f1f5f9 !important; }
    .dataTables_info {
        float: left; padding: 16px !important; font-size: 0.82rem !important;
        color: #64748b !important; font-weight: 500 !important;
    }
    .dataTables_paginate {
        float: right; padding: 12px 16px !important; display: flex; align-items: center; gap: 4px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: 1px solid transparent !important; background: transparent !important;
        color: #64748b !important; padding: 6px 12px !important; border-radius: 8px !important;
        font-weight: 600 !important; font-size: 0.82rem !important; cursor: pointer !important;
        transition: all 0.2s !important; margin: 0 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #0ea5e9 !important; color: white !important;
        border-color: #0ea5e9 !important; box-shadow: 0 2px 4px rgba(14, 165, 233, 0.2) !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
        background: #f1f5f9 !important; color: #0f172a !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = null;
    if ($('#assignmentTable tbody tr').length > 0 && !$('#assignmentTable tbody td[colspan]').length) {
        table = $('#assignmentTable').DataTable({
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            order: [[9, 'desc']], // Sort by waktu assign by default
            language: {
                search: "",
                searchPlaceholder: "Cari assignment...",
                lengthMenu: "Tampilkan _MENU_ entri",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ assignment",
                infoEmpty: "Menampilkan 0 assignment",
                infoFiltered: "(difilter dari _MAX_ total)",
                paginate: {
                    previous: '<i class="fas fa-angle-left"></i>',
                    next: '<i class="fas fa-angle-right"></i>',
                },
                zeroRecords: "Tidak ada data assignment yang cocok."
            }
        });
    }

    // Filter tabs
    $('.filter-tab').on('click', function() {
        $('.filter-tab').removeClass('active');
        $(this).addClass('active');
        var filter = $(this).data('filter');

        if (table) {
            if (filter) {
                // Exact match for the status column
                table.column(8).search('^' + filter.replace('_', ' '), true, false).draw();
            } else {
                table.column(8).search('').draw();
            }
        }
    });
});
</script>
@endpush
