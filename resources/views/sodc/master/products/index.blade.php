@extends('layouts.app')
@section('title', 'Product')
@section('page_title', 'Master Product')

@section('content')
@if(session('success'))
    <div style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div class="content-panel" style="margin-top: 20px;">
    <div class="panel-header">
        <h3 class="panel-title">Daftar Product</h3>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="{{ route('sodc.products.import_page') }}" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Import CSV
            </a>
            <button type="button" class="btn btn-trigger-sync-wms" style="background: #0ea5e9; color: white; display: flex; align-items: center; gap: 6px;">
                <i class="fas fa-sync"></i> Sync API WMS
            </button>
            <a href="{{ route('sodc.products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Product
            </a>
        </div>
    </div>
    
    <div style="padding: 24px 24px 0 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="color: #64748b; font-size: 0.9rem;">Menampilkan {{ $data->count() }} dari {{ $data->total() }} data</div>
            <form action="{{ route('sodc.products.index') }}" method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari SKU / Barcode..." style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; width: 250px; outline: none;">
            <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600;"><i class="fas fa-search"></i> Cari</button>
            @if(request('search'))
                <a href="{{ route('sodc.products.index') }}" style="background: #f1f5f9; color: #475569; padding: 8px 16px; border-radius: 6px; text-decoration: none; display: flex; align-items: center; font-weight: 600;">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-container">
        <table class="premium-table">
            <thead>
                <tr>
                    <th style='border-bottom: 1px solid #e2e8f0;'>SKU Code</th>
                    <th style='border-bottom: 1px solid #e2e8f0;'>SKU Name</th>
                    <th style='border-bottom: 1px solid #e2e8f0;'>Barcode</th>
                    <th style='border-bottom: 1px solid #e2e8f0;'>Packname & UOM</th>
                    <th style='border-bottom: 1px solid #e2e8f0;'>Principal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                <tr>
                    <td style='border-bottom: 1px solid #f1f5f9; font-weight: bold;'>{{ $row->sku_code }}</td>
                    <td style='border-bottom: 1px solid #f1f5f9;'>{{ $row->sku_name }}</td>
                    <td style='border-bottom: 1px solid #f1f5f9;'>{{ $row->barcode ?: '-' }}</td>
                    <td style='border-bottom: 1px solid #f1f5f9;'>
                        <span style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 0.85rem; white-space: nowrap;">
                            Isi {{ $row->uom ?: 1 }} Pcs / {{ $row->packname ?: 'Karton' }}
                        </span>
                    </td>
                    <td style='border-bottom: 1px solid #f1f5f9;'>
                        <div style="font-weight: 600; color: #334155;">{{ $row->principal ?: '-' }}</div>
                    </td>
                    <td style='border-bottom: 1px solid #f1f5f9;'>
                        <a href="{{ route('sodc.products.edit', $row->id) }}" style="color: #0284c7; margin-right: 10px; text-decoration: none;"><i class="fas fa-edit"></i> Edit</a>
                        <form action="{{ route('sodc.products.destroy', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus data?');">
                            @csrf @method('DELETE')
                            <button type="submit" style="background:none; border:none; color: #ef4444; cursor:pointer; font-size: inherit;"><i class="fas fa-trash"></i> Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div style="padding: 20px 24px; display: flex; justify-content: flex-end; border-top: 1px solid #e2e8f0;">
        @if(method_exists($data, 'links'))
            <style>
                .pagination { display: flex; list-style: none; padding: 0; margin: 0; gap: 5px; }
                .pagination li { display: inline-block; }
                .pagination li a, .pagination li span { padding: 6px 12px; border: 1px solid #e2e8f0; border-radius: 6px; color: #334155; text-decoration: none; background: white; }
                .pagination li.active span { background: #0ea5e9; color: white; border-color: #0ea5e9; }
                .pagination li.disabled span { color: #94a3b8; background: #f8fafc; }
                .pagination li a:hover { background: #f1f5f9; }
            </style>
            {{ $data->appends(request()->query())->links('pagination::bootstrap-4') }}
        @endif
    </div>
</div>

@include('partials.sync-wms-modal')
@endsection