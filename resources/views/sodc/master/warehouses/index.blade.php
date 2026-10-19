@extends('layouts.app')
@section('title', 'Warehouse')
@section('page_title', 'Master Data Warehouse')

@section('content')
@if(session('success'))
    <div style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div class="content-panel" style="margin-top: 20px;">
    <div class="panel-header">
        <h3 class="panel-title">Daftar Warehouse</h3>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="{{ route('sodc.warehouses.import_page') }}" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Import CSV
            </a>
            <a href="{{ route('sodc.warehouses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Warehouse
            </a>
        </div>
    </div>
    
    <div class="table-container">
        <table class="premium-table">
            <thead>
            <tr>
                <th style='border-bottom: 1px solid #e2e8f0;'>Cabang</th>
                <th style='border-bottom: 1px solid #e2e8f0;'>Kode Gudang</th>
                <th style='border-bottom: 1px solid #e2e8f0;'>Nama Gudang</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td style='border-bottom: 1px solid #f1f5f9;'>{{ $row->branch_id }}</td>
                <td style='border-bottom: 1px solid #f1f5f9;'>{{ $row->warehouse_code }}</td>
                <td style='border-bottom: 1px solid #f1f5f9;'>{{ $row->warehouse_name }}</td>
                <td style='border-bottom: 1px solid #f1f5f9;'>
                    <a href="{{ route('sodc.warehouses.edit', $row->id) }}" style="color: #0284c7; margin-right: 10px; text-decoration: none;"><i class="fas fa-edit"></i> Edit</a>
                    <form action="{{ route('sodc.warehouses.destroy', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus data?');">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:none; border:none; color: #ef4444; cursor:pointer; font-size: inherit;"><i class="fas fa-trash"></i> Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection