@extends('layouts.app')
@section('title', 'Branch')
@section('page_title', 'Master Branch')

@section('page_actions')
<a href="{{ route('sodc.branches.create') }}" style="display: inline-flex; align-items: center; gap: 8px; background: var(--phoenix-primary); color: white; border: none; padding: 8px 16px; border-radius: var(--phoenix-btn-radius); font-size: 0.875rem; font-weight: 700; cursor: pointer; text-decoration: none; box-shadow: 0 .125rem .25rem rgba(56, 116, 255, .15); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='var(--phoenix-primary-hover)'" onmouseout="this.style.backgroundColor='var(--phoenix-primary)'">
    <i class="fas fa-plus" style="font-size: 0.875rem;"></i> Tambah Branch
</a>
@endsection

@section('content')
@if(session('success'))
    <div style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div class="content-panel">
    <div class="table-container">
        <table class="premium-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Kode Cabang</th>
                    <th style="width: 60%;">Nama Cabang</th>
                    <th style="width: 20%; text-align: center;">Aksi</th>
                </tr>
            </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td style="font-weight: 700; color: var(--phoenix-primary);">{{ $row->branch_code }}</td>
                <td style="font-weight: 600;">{{ $row->branch_name }}</td>
                <td style="text-align: center;">
                    <div style="display: inline-flex; gap: 8px;">
                        <a href="{{ route('sodc.branches.edit', $row->branch_id) }}" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: var(--phoenix-btn-radius); background: var(--phoenix-light); color: var(--phoenix-text-muted); text-decoration: none; transition: all 0.2s;" onmouseover="this.style.color='var(--phoenix-primary)'; this.style.background='rgba(56, 116, 255, 0.1)';" onmouseout="this.style.color='var(--phoenix-text-muted)'; this.style.background='var(--phoenix-light)';">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('sodc.branches.destroy', $row->branch_id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
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