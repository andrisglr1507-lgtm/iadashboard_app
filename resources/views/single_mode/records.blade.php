@extends('layouts.app')

@section('title', 'Single Mode - List Records')
@section('page_title', 'Single Mode Records')

@section('page_actions')
<div class="badge-status">
    <span>{{ count($records) }} Records</span>
</div>
@endsection

@section('content')

@if($error)
    <div style="display: flex; align-items: flex-start; gap: 12px; background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; padding: 14px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 0.85rem;">
        <i class="fas fa-exclamation-triangle" style="margin-top: 2px;"></i>
        <div>{{ $error }}</div>
    </div>
@endif

<div style="background: white; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.03); overflow-x: auto; width: 100%;">
    <table id="singleModeTable" class="display compact nowrap" style="width: 100%;">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>User</th>
                <th>ID Produk</th>
                <th>Nama Produk</th>
                <th>Qty Karton</th>
                <th>Qty Pcs</th>
                <th>Total Qty</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                <td>{{ \Carbon\Carbon::parse($record->created_at)->format('d/m/Y H:i') }}</td>
                <td>{{ $record->user_name ?? '-' }}</td>
                <td>{{ $record->id_product }}</td>
                <td>{{ $record->nama_product ?? '-' }}</td>
                <td>{{ $record->qty_karton ?? 0 }}</td>
                <td>{{ $record->qty_pcs ?? 0 }}</td>
                <td>{{ $record->qty_fisik ?? 0 }}</td>
                <td>{{ ucfirst($record->status ?? 'pending') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<style>
    /* DataTables overrides for clean look */
    #singleModeTable {
        font-size: 0.82rem;
        border-collapse: collapse;
    }

    #singleModeTable thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 10px 14px;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }

    #singleModeTable tbody td {
        padding: 8px 14px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    #singleModeTable tbody tr:hover {
        background: #f8fafc;
    }

    /* Numeric columns right-aligned */
    #singleModeTable tbody td:nth-child(5),
    #singleModeTable tbody td:nth-child(6),
    #singleModeTable tbody td:nth-child(7) {
        text-align: right;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    #singleModeTable thead th:nth-child(5),
    #singleModeTable thead th:nth-child(6),
    #singleModeTable thead th:nth-child(7) {
        text-align: right;
    }

    /* Mono font for ID columns */
    #singleModeTable tbody td:nth-child(3) {
        font-family: 'Courier New', monospace;
        font-weight: 500;
        font-size: 0.8rem;
    }

    /* DataTables wrapper tweaks */
    .dataTables_wrapper {
        padding: 12px 16px;
    }

    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 5px 10px;
        font-size: 0.8rem;
        outline: none;
        margin-left: 6px;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #94a3b8;
        box-shadow: 0 0 0 2px rgba(148,163,184,0.15);
    }

    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 0.8rem;
    }

    .dataTables_wrapper .dataTables_info {
        font-size: 0.75rem;
        color: #94a3b8;
        padding-top: 10px;
    }

    .dataTables_wrapper .dataTables_paginate {
        padding-top: 10px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        font-size: 0.8rem;
        padding: 4px 10px;
        border-radius: 6px;
        margin: 0 2px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #0ea5e9 !important;
        color: white !important;
        border: 1px solid #0ea5e9 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f1f5f9 !important;
        color: #334155 !important;
        border-color: #e2e8f0 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #0284c7 !important;
        color: white !important;
        border-color: #0284c7 !important;
    }

    table.dataTable.no-footer {
        border-bottom: none;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    var table = null;
    if ($('#singleModeTable tbody tr').length > 0) {
        table = $('#singleModeTable').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            autoWidth: false,
            language: {
                search: "Cari:",
                searchPlaceholder: "Ketik untuk filter...",
                lengthMenu: "Tampilkan _MENU_",
                info: "_START_ - _END_ dari _TOTAL_ record",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(filter dari _MAX_ total)",
                zeroRecords: "Data tidak ditemukan",
                paginate: {
                    first: "«",
                    previous: "‹",
                    next: "›",
                    last: "»"
                }
            }
        });
    }

    // Recalculate DataTable columns when sidebar toggles
    var toggleBtn = document.getElementById('toggleSidebarBtn');
    if (toggleBtn && table) {
        toggleBtn.addEventListener('click', function() {
            // Wait for CSS transition to finish (300ms matches sidebar transition)
            setTimeout(function() {
                table.columns.adjust().draw(false);
            }, 350);
        });
    }
});
</script>
@endpush
