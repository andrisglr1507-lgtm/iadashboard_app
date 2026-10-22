@extends('layouts.app')
@section('title', 'Bin')
@section('page_title', 'Master Data Bin Loc')

@section('content')
@if(session('success'))
    <div style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div style="margin-bottom: 20px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 8px;">
        <i class="fas fa-times-circle"></i> {{ session('error') }}
    </div>
@endif

<div class="content-panel" style="margin-top: 20px;">
    <div class="panel-header">
        <h3 class="panel-title">Daftar Bin</h3>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="{{ route('sodc.bins.import_page') }}" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Import CSV
            </a>
            <a href="{{ route('sodc.bins.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Bin
            </a>
        </div>
    </div>
    
    <div style="padding: 24px 24px 0 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="color: #64748b; font-size: 0.9rem;">Menampilkan {{ $data->count() }} dari {{ $data->total() }} data</div>
            <form action="{{ route('sodc.bins.index') }}" method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Kode Bin / Zona..." style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; width: 250px; outline: none;">
            <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600;"><i class="fas fa-search"></i> Cari</button>
            @if(request('search'))
                <a href="{{ route('sodc.bins.index') }}" style="background: #f1f5f9; color: #475569; padding: 8px 16px; border-radius: 6px; text-decoration: none; display: flex; align-items: center; font-weight: 600;">Reset</a>
            @endif
        </form>
    </div>
    
    <div class="table-container">
        <table class="premium-table">
            <thead>
                <tr>
                    <th style='border-bottom: 1px solid #e2e8f0;'>Gudang ID</th>
                    <th style='border-bottom: 1px solid #e2e8f0;'>Kode Bin</th>
                    <th style='border-bottom: 1px solid #e2e8f0;'>Zona</th>
                    <th style='border-bottom: 1px solid #e2e8f0;'>Ganjil/Genap</th>
                    <th style='border-bottom: 1px solid #e2e8f0;'>Level</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td style='border-bottom: 1px solid #f1f5f9;'>{{ $row->warehouse_id }}</td>
                    <td style='border-bottom: 1px solid #f1f5f9; font-weight: bold;'>{{ $row->bin_code }}</td>
                    <td style='border-bottom: 1px solid #f1f5f9;'>{{ $row->zone ?: '-' }}</td>
                    <td style='border-bottom: 1px solid #f1f5f9;'>
                        @if($row->ganjil_genap == 'Ganjil') <span style="background: #fef08a; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Ganjil</span>
                        @elseif($row->ganjil_genap == 'Genap') <span style="background: #bfdbfe; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Genap</span>
                        @else - @endif
                    </td>
                    <td style='border-bottom: 1px solid #f1f5f9;'>{{ $row->level ? 'Level ' . $row->level : '-' }}</td>
                    <td style='border-bottom: 1px solid #f1f5f9;'>
                        <a href="{{ route('sodc.bins.edit', $row->id) }}" style="color: #0284c7; margin-right: 10px; text-decoration: none;"><i class="fas fa-edit"></i> Edit</a>
                        <form action="{{ route('sodc.bins.destroy', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus data?');">
                            @csrf @method('DELETE')
                            <button type="submit" style="background:none; border:none; color: #ef4444; cursor:pointer; font-size: inherit;"><i class="fas fa-trash"></i> Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <x-empty-state 
                    colspan="6"
                    title="Belum Ada Data Bin"
                    description="Belum ada data bin lokasi gudang yang tersimpan di sistem."
                    :actionUrl="route('sodc.bins.create')"
                    actionText="Tambah Bin Baru"
                    actionIcon="fas fa-plus"
                />
                @endforelse
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
@endsection