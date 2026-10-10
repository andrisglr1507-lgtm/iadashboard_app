@extends('layouts.app')
@section('title', 'Riwayat Recount & Assignment')
@section('page_title', 'Riwayat Recount & Assignment')

@section('content')
@if(session('success'))
    <div style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if(isset($error))
    <div style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 16px; border-radius: 8px; text-align: center; margin-top: 20px;">
        <i class="fas fa-exclamation-triangle" style="font-size: 1.5rem; margin-bottom: 8px;"></i>
        <p style="font-weight: 600; margin: 0;">{{ $error }}</p>
    </div>
@else
    <div class="content-panel">
        <div class="panel-header">
            <div style="display: flex; align-items: center; gap: 15px;">
                <h3 class="panel-title">Data Riwayat Recount</h3>
                <form action="{{ route('sodc.recount_history.index') }}" method="GET" style="margin: 0;">
                    <select name="session_id" onchange="this.form.submit()" style="padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; outline: none;">
                        @foreach($activeSessions as $s)
                            <option value="{{ $s->id }}" {{ $activeSessionId == $s->id ? 'selected' : '' }}>
                                {{ $s->session_code }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            
            <form action="{{ route('sodc.recount_history.index') }}" method="GET" style="margin: 0; display: flex; gap: 10px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Bin / SKU / PIC..." style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; width: 250px; outline: none;">
                <button type="submit" style="background: #0f172a; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer;"><i class="fas fa-search"></i></button>
                @if(request('search'))
                    <a href="{{ route('sodc.recount_history.index') }}" style="background: #f1f5f9; color: #475569; padding: 8px 16px; border-radius: 6px; text-decoration: none; display: flex; align-items: center; font-weight: 600;">Reset</a>
                @endif
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Waktu Assignment</th>
                        <th>Location (Bin)</th>
                        <th>Produk</th>
                        <th>Level (R)</th>
                        <th>Assigned To (PIC)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                    <tr>
                        <td style="color: #64748b; font-size: 0.9rem;">
                            <div style="font-weight: bold; color: #334155;">{{ \Carbon\Carbon::parse($row->assigned_at)->format('d M Y') }}</div>
                            <div>{{ \Carbon\Carbon::parse($row->assigned_at)->format('H:i:s') }}</div>
                        </td>
                        <td style="font-weight: 600; color: #334155;">{{ $row->location_code }}</td>
                        <td>
                            <div style="font-weight: 600; color: #334155; font-size: 0.9rem;">{{ $row->id_product }}</div>
                            <div style="color: #64748b; font-size: 0.85rem; margin-top: 2px;">{{ $row->product_name ?: 'Unknown Product' }}</div>
                        </td>
                        <td>
                            @if($row->round_number == 2)
                                <span style="background: #e0e7ff; color: #4338ca; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 0.8rem;">RECOUNT (R2)</span>
                            @elseif($row->round_number == 3)
                                <span style="background: #fee2e2; color: #b91c1c; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 0.8rem;">FINAL (R3)</span>
                            @else
                                <span style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 0.8rem;">R{{ $row->round_number }}</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #0ea5e9;">{{ $row->assigned_to_name ?: '-' }}</div>
                            <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 2px;">by: {{ $row->assigned_by_name ?: '-' }}</div>
                        </td>
                        <td>
                            @if($row->status == 'ASSIGNED')
                                <span style="background: #fffbeb; color: #d97706; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><i class="fas fa-clock"></i> PENDING</span>
                            @elseif($row->status == 'COMPLETED')
                                <span style="background: #ecfdf5; color: #10b981; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><i class="fas fa-check-circle"></i> SELESAI</span>
                            @else
                                <span style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">{{ $row->status }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #64748b;">
                            <i class="fas fa-inbox fa-3x" style="color: #cbd5e1; margin-bottom: 10px;"></i>
                            <p style="margin: 0;">Tidak ada data riwayat recount.</p>
                        </td>
                    </tr>
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
@endif
@endsection
