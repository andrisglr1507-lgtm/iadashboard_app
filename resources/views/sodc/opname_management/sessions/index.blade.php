@extends('layouts.app')
@section('title', 'Opname Session')

@php
    $svgAddSession = file_exists(public_path('svg/Daftar_Team/Opname_session/add_opname_session.svg'))
        ? file_get_contents(public_path('svg/Daftar_Team/Opname_session/add_opname_session.svg'))
        : '<i class="fas fa-plus"></i>';
    $svgPencil = file_exists(public_path('svg/Daftar_Team/Opname_session/pencil.svg'))
        ? file_get_contents(public_path('svg/Daftar_Team/Opname_session/pencil.svg'))
        : '<i class="fas fa-edit"></i>';
    $svgTrash = file_exists(public_path('svg/Daftar_Team/Opname_session/trash-x.svg'))
        ? file_get_contents(public_path('svg/Daftar_Team/Opname_session/trash-x.svg'))
        : '<i class="fas fa-trash"></i>';
@endphp

@push('styles')
<style>
    .btn svg {
        width: 18px;
        height: 18px;
        stroke-width: 2;
        flex-shrink: 0;
    }
    .table-action-link,
    .table-action-delete {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        font-weight: 500;
        transition: opacity 0.2s;
    }
    .table-action-link:hover,
    .table-action-delete:hover {
        opacity: 0.8;
    }
    .table-action-link svg,
    .table-action-delete svg {
        width: 17px;
        height: 17px;
        stroke-width: 2;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')
@if(session('success'))
    <div style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div class="content-panel" style="margin-top: 20px;">
    <div class="panel-header">
        <h3 class="panel-title">Opname Session</h3>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="{{ route('sodc.sessions.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                {!! $svgAddSession !!} Tambah Session
            </a>
        </div>
    </div>
    
    <div class="table-container">
        <table class="premium-table">
            <thead>
                <tr>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Session Code</th>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Reference Doc</th>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Opname Date</th>
                <th style='padding: 12px; border-bottom: 1px solid #e2e8f0;'>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9; font-weight: bold;'>{{ $row->session_code }}</td>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>{{ $row->reference_id }}</td>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>{{ $row->opname_date }}</td>
                <td style='padding: 12px; border-bottom: 1px solid #f1f5f9;'>
                    @if($row->status == 'DRAFT')
                        <span style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">DRAFT</span>
                    @elseif($row->status == 'ACTIVE')
                        <span style="background: #dcfce3; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">ACTIVE</span>
                    @elseif($row->status == 'CLOSED')
                        <span style="background: #e2e8f0; color: #475569; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">CLOSED</span>
                    @else
                        <span style="background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">{{ $row->status }}</span>
                    @endif
                </td>
                <td>
                    @if($row->status == 'DRAFT')
                    <form action="{{ route('sodc.sessions.start', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Mulai sesi ini sekarang? Semua sesi aktif lainnya akan dimatikan.');">
                        @csrf
                        <button type="submit" style="background: #10b981; border:none; color: white; cursor:pointer; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; margin-right: 10px;"><i class="fas fa-play"></i> START</button>
                    </form>
                    @endif
                    @if($row->status == 'ACTIVE')
                    <form action="{{ route('sodc.sessions.close', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Tutup sesi ini sekarang? Petugas tidak akan bisa mengakses sesi ini lagi.');">
                        @csrf
                        <button type="submit" style="background: #ef4444; border:none; color: white; cursor:pointer; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; margin-right: 10px;"><i class="fas fa-stop"></i> CLOSE</button>
                    </form>
                    @endif
                    <a href="{{ route('sodc.sessions.edit', $row->id) }}" class="table-action-link" style="color: #0284c7; margin-right: 12px;" title="Edit Session">
                        {!! $svgPencil !!} Edit
                    </a>
                    <form action="{{ route('sodc.sessions.destroy', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus data?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="table-action-delete" style="background:none; border:none; color: #ef4444; cursor:pointer; font-size: inherit; padding: 0;" title="Hapus Session">
                            {!! $svgTrash !!} Hapus
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection