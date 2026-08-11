@extends('layouts.app')

@section('title', 'Detail Product - ' . $principalName)

@section('content')
<div class="top-bar">
    <div class="back-nav">
        <a href="{{ route('master.products.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali ke Master</span>
        </a>
    </div>
    <div class="badge-status">
        <i class="fas fa-industry" style="color:#1a6d9f; font-size: 0.85rem; margin-right: 4px;"></i>
        <span>{{ $principalName }}</span>
    </div>
</div>

<div class="content-panel table-panel">
    <div class="panel-header" style="border-bottom: none; padding-bottom: 8px;">
        <h2 class="panel-title">Daftar Produk</h2>
        <span class="product-count" id="productCountBadge">{{ count($products) }} Produk Terdaftar</span>
    </div>

    <div class="table-container">
        <table class="premium-table" id="productsTable">
            <thead>
                <tr>
                    <th>ID Produk</th>
                    <th>Nama Produk</th>
                    <th>Barcode</th>
                    <th>Kode Karton</th>
                    <th>Pack Name</th>
                    <th>UOM</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td class="text-id font-mono">{{ $product->id_product }}</td>
                        <td class="font-weight-medium">{{ $product->product_name }}</td>
                        <td>{{ $product->barcode ?: '-' }}</td>
                        <td>{{ $product->carton_code ?: '-' }}</td>
                        <td>{{ $product->packname ?: '-' }}</td>
                        <td>{{ $product->uom ? $product->uom . ' pcs' : '-' }}</td>
                        <td class="text-center">
                            @if($product->is_active)
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge badge-inactive">Inactive</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4" style="color: #8ba0b5;">
                            <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 8px;"></i>
                            <p>Tidak ada produk dalam principal ini.</p>
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
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #1a6d9f;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: color 0.2s;
    }
    
    .btn-back:hover {
        color: #0f2b3b;
    }
    
    .back-nav {
        display: flex;
        align-items: center;
    }
    
    .table-panel {
        padding: 24px 0 0 0;
        overflow: hidden;
    }
    
    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 24px 20px 24px;
        border-bottom: 1px solid #eff3f8;
    }
    
    .panel-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #0f2b3b;
        margin: 0;
    }
    
    .product-count {
        font-size: 0.85rem;
        color: #8ba0b5;
        font-weight: 500;
    }
    
    .table-container {
        width: 100%;
        overflow-x: auto;
    }
    
    .premium-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    
    .premium-table th {
        background: #f8fafc;
        padding: 14px 24px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #8ba0b5;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #eff3f8;
    }
    
    .premium-table td {
        padding: 16px 24px;
        font-size: 0.9rem;
        color: #2c3e4e;
        border-bottom: 1px solid #eff3f8;
        vertical-align: middle;
    }
    
    .premium-table tbody tr {
        transition: background 0.15s;
    }
    
    .premium-table tbody tr:hover {
        background: #f8fafc;
    }
    
    .text-id {
        color: #1a6d9f;
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .font-weight-medium {
        font-weight: 500;
    }
    
    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 30px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .badge-active {
        background: #ecfdf5;
        color: #059669;
    }
    
    .badge-inactive {
        background: #fef2f2;
        color: #dc2626;
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
        gap: 8px;
    }

    .dataTables_filter input {
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
        padding: 7px 14px !important;
        outline: none !important;
        color: #334155;
        font-size: 0.85rem !important;
        width: 240px !important;
        transition: all 0.2s !important;
        margin-left: 0 !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02) !important;
    }

    .dataTables_filter input:focus {
        border-color: #1a6d9f !important;
        box-shadow: 0 0 0 3px rgba(26,109,159,0.12) !important;
    }

    /* Table borders resetting */
    table.dataTable.no-footer {
        border-bottom: 1px solid #eff3f8 !important;
    }

    table.dataTable th {
        border-bottom: 1px solid #eff3f8 !important;
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
        padding: 18px 24px !important;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Pagination Buttons overrides */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: 1px solid transparent !important;
        background: transparent !important;
        color: #64748b !important;
        padding: 6px 12px !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
        font-size: 0.85rem !important;
        cursor: pointer !important;
        transition: all 0.15s !important;
        margin: 0 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #e9f0f8 !important;
        color: #1a6d9f !important;
        border-color: transparent !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current):not(.disabled) {
        background: #f1f5f9 !important;
        color: #1e293b !important;
        border-color: #cbd5e1 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
        color: #cbd5e1 !important;
        cursor: not-allowed !important;
        background: transparent !important;
        border-color: transparent !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button i {
        font-size: 0.8rem;
    }

    @media (max-width: 640px) {
        .dataTables_length, .dataTables_filter {
            float: none;
            width: 100%;
            padding-bottom: 12px;
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
<!-- Load jQuery and DataTables JS via CDN -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        if ($('#productsTable tbody tr').length > 0 && !$('#productsTable tbody tr td').hasClass('dataTables_empty')) {
            $('#productsTable').DataTable({
                pageLength: 15,
                lengthMenu: [10, 15, 25, 50, 100],
                order: [[1, 'asc']], // Order by product_name alphabetically
                language: {
                    search: "",
                    searchPlaceholder: "Cari produk...",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ produk",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 produk",
                    infoFiltered: "(difilter dari _MAX_ total produk)",
                    paginate: {
                        first: '<i class="fas fa-angle-double-left"></i>',
                        previous: '<i class="fas fa-angle-left"></i>',
                        next: '<i class="fas fa-angle-right"></i>',
                        last: '<i class="fas fa-angle-double-right"></i>'
                    },
                    zeroRecords: "Tidak ada data produk yang cocok ditemukan."
                }
            });
        }
    });
</script>
@endpush
