@extends('layouts.app')
@section('title', 'Input By Team')
@section('page_title', 'Record Opname by Team')

@push('styles')
<style>
    /* Fix for huge SVG pagination arrows */
    svg.w-5.h-5 { width: 1.2rem; height: 1.2rem; }
    .pagination-wrapper nav div { margin-top: 10px; }
</style>
@endpush

@section('content')
@if(session('success'))
    <div style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if(!$activeSession)
    <div class="content-panel" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 48px 20px; margin-top: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <x-empty-state 
            title="Tidak Ada Sesi Opname yang Aktif"
            description="Tidak ada sesi stock opname yang berstatus aktif saat ini. Silakan buat atau aktifkan sesi di menu Manajemen Session untuk melihat riwayat input tim."
            :actionUrl="route('sodc.sessions.index')"
            actionText="Buka Manajemen Session"
            actionIcon="fas fa-calendar-check"
        />
    </div>
@else
<div class="content-panel" style="margin-top: 20px;">
    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <h3 class="panel-title" style="margin: 0; font-size: 1.1rem; color: #1e293b;">Daftar Transaksi Input</h3>
                <span class="badge" style="background: #e0f2fe; color: #0284c7; font-size: 0.75rem; padding: 4px 8px; border-radius: 6px; font-weight: 700;">
                    <i class="fas fa-play-circle"></i> {{ $activeSession->session_code }}
                </span>
            </div>
            <p style="margin: 4px 0 0; font-size: 0.85rem; color: #64748b;">Merekam setiap baris input dari aplikasi team counting</p>
        </div>
        <form method="GET" action="{{ route('sodc.input_by_team.index') }}" style="display: flex; gap: 10px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Bin / SKU / Nama / Tim..." style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.85rem; width: 250px;">
            <button type="submit" class="btn btn-primary" style="padding: 8px 12px; border-radius: 6px; cursor: pointer;"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <div class="table-container" style="overflow-x: auto;">
        <table class="premium-table" style="width: 100%; border-collapse: collapse; min-width: 1000px;">
            <thead>
                <tr>
                    <th style="padding: 12px 16px;">Waktu Submit</th>
                    <th style="padding: 12px 16px;">Petugas</th>
                    <th style="padding: 12px 16px;">Tim</th>
                    <th style="padding: 12px 16px;">Lokasi / Bin</th>
                    <th style="padding: 12px 16px;">Produk (SKU)</th>
                    <th style="padding: 12px 16px; text-align: right;">Input (Qty)</th>
                    <th style="padding: 12px 16px; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                    <tr>
                        <td style="padding: 12px 16px;">
                            <div style="font-weight: 600; color: #334155;">{{ \Carbon\Carbon::parse($row->counted_at)->format('d M Y') }}</div>
                            <div style="font-size: 0.75rem; color: #64748b;">{{ \Carbon\Carbon::parse($row->counted_at)->format('H:i:s') }}</div>
                        </td>
                        <td style="padding: 12px 16px; font-weight: 600; color: #0284c7;">
                            {{ $row->counter_name ?? 'Unknown' }}
                        </td>
                        <td style="padding: 12px 16px;">
                            <span style="background: #e0e7ff; color: #4f46e5; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                                {{ $row->team_id }}
                            </span>
                        </td>
                        <td style="padding: 12px 16px; font-family: monospace; font-weight: bold;">
                            {{ $row->bin_code ?? '-' }}
                        </td>
                        <td style="padding: 12px 16px; min-width: 250px;">
                            <div style="font-family: monospace; font-weight: 600;">{{ $row->sku_code ?? '-' }}</div>
                            <div style="font-size: 0.75rem; color: #64748b; line-height: 1.4; margin-top: 4px;">{{ $row->sku_name ?? '' }}</div>
                        </td>
                        <td style="padding: 12px 16px; text-align: right; font-weight: 800; color: #0f172a;">
                            {{ (float)$row->count_qty }}
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            @if($row->count_status == 'SUBMITTED')
                                <span style="background: #dcfce3; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 700;"><i class="fas fa-check"></i> OK</span>
                            @else
                                <span style="background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 700;">{{ $row->count_status }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <x-empty-state 
                        colspan="7"
                        title="Belum Ada Inputan Tim"
                        description="Belum ada data transaksi input yang masuk dari aplikasi team counting untuk sesi ini."
                        :actionUrl="request('search') ? route('sodc.input_by_team.index') : null"
                        actionText="Reset Pencarian"
                        actionIcon="fas fa-undo"
                    />
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($data->hasPages())
    <div class="pagination-wrapper d-flex justify-content-center" style="margin-top: 20px;">
        {{ $data->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endif
@endsection
