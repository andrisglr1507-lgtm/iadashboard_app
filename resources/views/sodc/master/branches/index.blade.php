@extends('layouts.app')
@section('title', 'Master Data Branch')
@section('content')
@if(session('success'))
    <div style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div class="content-panel" style="margin-top: 20px;">
    <div class="panel-header">
        <h3 class="panel-title">Daftar Branch</h3>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="{{ route('sodc.branches.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Branch
            </a>
        </div>
    </div>
    
    <div class="table-container">
        <table class="premium-table">
            <thead>
                <tr>
                    <th style="width: 20%; border-bottom: 1px solid #e2e8f0;">Kode Cabang</th>
                    <th style="width: 60%; border-bottom: 1px solid #e2e8f0;">Nama Cabang</th>
                    <th style="width: 20%; text-align: center;">Aksi</th>
                </tr>
            </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td style="font-weight: 700; color: var(--phoenix-primary); border-bottom: 1px solid #f1f5f9;">{{ $row->branch_code }}</td>
                <td style="font-weight: 600; border-bottom: 1px solid #f1f5f9;">{{ $row->branch_name }}</td>
                <td style="text-align: center; border-bottom: 1px solid #f1f5f9;">
                    <div style="display: inline-flex; gap: 8px;">
                        <a href="{{ route('sodc.branches.edit', $row->id) }}" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: var(--phoenix-btn-radius); background: var(--phoenix-light); color: var(--phoenix-text-muted); text-decoration: none; transition: all 0.2s;" onmouseover="this.style.color='var(--phoenix-primary)'; this.style.background='rgba(56, 116, 255, 0.1)';" onmouseout="this.style.color='var(--phoenix-text-muted)'; this.style.background='var(--phoenix-light)';">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('sodc.branches.destroy', $row->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: var(--phoenix-btn-radius); background: var(--phoenix-light); color: var(--phoenix-text-muted); border: none; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.color='var(--phoenix-danger)'; this.style.background='rgba(230, 55, 87, 0.1)';" onmouseout="this.style.color='var(--phoenix-text-muted)'; this.style.background='var(--phoenix-light)';">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endsection