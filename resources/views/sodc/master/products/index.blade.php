@extends('layouts.app')
@section('title', 'Product')
@section('page_title', 'Master Product')

@section('page_actions')
<a href="{{ route('sodc.products.import_page') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #107c41; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; margin-right: 10px; text-decoration: none;">
    <i class="fas fa-file-excel"></i> Import CSV
</a>
<a href="{{ route('sodc.products.create') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #0ea5e9; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; text-decoration: none;">
    <i class="fas fa-plus"></i> Tambah Product
</a>
@endsection

@section('content')
@if(session('success'))
    <div style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div style="margin-top: 20px; padding: 24px; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid rgba(226,232,240,0.8);">
    
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

    <table class="premium-table" style="width: 100%; text-align: left; border-collapse: collapse;">
        <thead>
            <tr>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>SKU Code</th>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>SKU Name</th>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Barcode</th>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Packname & UOM</th>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Principal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9; font-weight: bold;'>{{ $row->sku_code }}</td>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>{{ $row->sku_name }}</td>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>{{ $row->barcode ?: '-' }}</td>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>
                    <div style="font-weight: 600; color: #334155; font-size: 0.9rem;">{{ $row->packname ?: '-' }}</div>
                    <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">
                        Isi: <span style="background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-weight: bold;">{{ $row->uom ?: 1 }} Pcs</span>
                    </div>
                </td>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>
                    <div style="font-weight: 600; color: #334155;">{{ $row->principal ?: '-' }}</div>
                </td>
                <td>
                    <a href="{{ route('sodc.products.edit', $row->id) }}" style="color: #0284c7; margin-right: 10px;"><i class="fas fa-edit"></i> Edit</a>
                    <form action="{{ route('sodc.products.destroy', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus data?');">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:none; border:none; color: #ef4444; cursor:pointer;"><i class="fas fa-trash"></i> Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
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
@endsection