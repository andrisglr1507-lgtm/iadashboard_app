@extends('layouts.app')
@section('title', 'Tracking Opname')
@section('page_title', 'Tracking Opname')

@section('content')
@if(session('success'))
    <div style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if(!$session)
    <div style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 16px; border-radius: 8px; text-align: center; margin-top: 20px;">
        <i class="fas fa-exclamation-triangle" style="font-size: 1.5rem; margin-bottom: 8px;"></i>
        <p style="font-weight: 600; margin: 0;">Tidak ada Sesi Opname yang Aktif.</p>
        <p style="margin: 5px 0 0 0; font-size: 0.9rem;">Silakan buat atau aktifkan sesi di menu Manajemen Session.</p>
    </div>
@else
    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
        <!-- Total -->
        <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Total Data WMS</div>
                <div style="font-size: 1.8rem; font-weight: bold; color: #0f172a; margin-top: 5px;">{{ number_format($summary['total']) }}</div>
            </div>
            <div style="background: #f1f5f9; padding: 15px; border-radius: 12px; color: #64748b;">
                <i class="fas fa-database fa-lg"></i>
            </div>
        </div>

        <!-- Belum Dihitung -->
        <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Belum Dihitung</div>
                <div style="font-size: 1.8rem; font-weight: bold; color: #ef4444; margin-top: 5px;">{{ number_format($summary['belum']) }}</div>
            </div>
            <div style="background: #fef2f2; padding: 15px; border-radius: 12px; color: #ef4444;">
                <i class="fas fa-times-circle fa-lg"></i>
            </div>
        </div>

        <!-- Team A Only -->
        <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Baru Team A</div>
                <div style="font-size: 1.8rem; font-weight: bold; color: #f59e0b; margin-top: 5px;">{{ number_format($summary['team_a_only']) }}</div>
            </div>
            <div style="background: #fffbeb; padding: 15px; border-radius: 12px; color: #f59e0b;">
                <i class="fas fa-user-clock fa-lg"></i>
            </div>
        </div>

        <!-- Match / Final -->
        <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Sudah Selesai (Match)</div>
                <div style="font-size: 1.8rem; font-weight: bold; color: #10b981; margin-top: 5px;">{{ number_format($summary['match_final']) }}</div>
            </div>
            <div style="background: #ecfdf5; padding: 15px; border-radius: 12px; color: #10b981;">
                <i class="fas fa-check-double fa-lg"></i>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-panel" style="margin-top: 20px;">
        <div class="panel-header">
            <h3 class="panel-title">Tracking Referensi WMS ({{ $session->session_code }})</h3>
            
            <div style="display: flex; gap: 12px; align-items: center;">
                <!-- Filter Status -->
                <form action="{{ route('sodc.tracking.index') }}" method="GET" style="margin: 0; display: flex; gap: 10px;">
                    <select name="status" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff;" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="BELUM" {{ request('status') == 'BELUM' ? 'selected' : '' }}>Belum Dihitung Sama Sekali</option>
                        <option value="TEAM_A_ONLY" {{ request('status') == 'TEAM_A_ONLY' ? 'selected' : '' }}>Sudah Dihitung (Hanya Team A)</option>
                        <option value="TEAM_B_ONLY" {{ request('status') == 'TEAM_B_ONLY' ? 'selected' : '' }}>Sudah Dihitung (Hanya Team B)</option>
                        <option value="MISMATCH" {{ request('status') == 'MISMATCH' ? 'selected' : '' }}>Mismatch / Butuh Recount</option>
                        <option value="MATCH_FINAL" {{ request('status') == 'MATCH_FINAL' ? 'selected' : '' }}>Sudah Match / Final</option>
                    </select>

                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Bin / SKU / Produk..." style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; width: 200px; outline: none;">
                    <button type="submit" style="background: #0f172a; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer;"><i class="fas fa-search"></i></button>
                    
                    @if(request('search') || request('status'))
                        <a href="{{ route('sodc.tracking.index') }}" style="background: #f1f5f9; color: #475569; padding: 8px 16px; border-radius: 6px; text-decoration: none; display: flex; align-items: center; font-weight: 600;">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th style="min-width: 100px;">Location (Bin)</th>
                        <th style="min-width: 250px;">Produk</th>
                        <th>Qty System</th>
                        <th>Input Team A</th>
                        <th>Input Team B</th>
                        <th>Final Qty</th>
                        <th>Status Tracking</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                        @php
                            // Tentukan status badge
                            $statusBadge = '<span style="background: #f1f5f9; color: #64748b; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">BELUM DIHITUNG</span>';
                            
                            if (is_null($row->id_result) && is_null($row->team_a_qty) && is_null($row->team_b_qty)) {
                                $statusBadge = '<span style="background: #fef2f2; color: #ef4444; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><i class="fas fa-times-circle"></i> BELUM DIHITUNG</span>';
                            } else if (!is_null($row->team_a_qty) && is_null($row->team_b_qty)) {
                                $statusBadge = '<span style="background: #fffbeb; color: #d97706; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><i class="fas fa-clock"></i> HANYA TEAM A</span>';
                            } else if (is_null($row->team_a_qty) && !is_null($row->team_b_qty)) {
                                $statusBadge = '<span style="background: #fffbeb; color: #d97706; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><i class="fas fa-clock"></i> HANYA TEAM B</span>';
                            } else {
                                if (in_array($row->result_status, ['MATCH', 'FINAL'])) {
                                    $statusBadge = '<span style="background: #ecfdf5; color: #10b981; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><i class="fas fa-check-circle"></i> ' . $row->result_status . '</span>';
                                } else {
                                    $statusBadge = '<span style="background: #fef2f2; color: #ef4444; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;"><i class="fas fa-exclamation-triangle"></i> ' . ($row->result_status ?: 'MISMATCH') . '</span>';
                                }
                            }
                        @endphp
                    <tr>
                        <td style="font-weight: 600; color: #334155; font-size: 0.95rem;">
                            {{ $row->bin_code }}
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #334155; font-size: 0.9rem;">{{ $row->sku_code }}</div>
                            <div style="color: #64748b; font-size: 0.85rem; margin-top: 2px;">{{ $row->sku_name ?: 'Unknown Product' }}</div>
                        </td>
                        <td style="font-weight: 600; color: #334155;">{{ round($row->system_qty) }}</td>
                        <td>
                            @if(is_null($row->team_a_qty))
                                <span style="color: #cbd5e1; font-style: italic;">Belum</span>
                            @else
                                <span style="font-weight: bold; color: #0ea5e9;">{{ round($row->team_a_qty) }}</span>
                            @endif
                        </td>
                        <td>
                            @if(is_null($row->team_b_qty))
                                <span style="color: #cbd5e1; font-style: italic;">Belum</span>
                            @else
                                <span style="font-weight: bold; color: #0ea5e9;">{{ round($row->team_b_qty) }}</span>
                            @endif
                        </td>
                        <td>
                            @if(!is_null($row->final_qty))
                                <span style="font-weight: bold; color: #10b981;">{{ round($row->final_qty) }}</span>
                            @else
                                <span style="color: #cbd5e1;">-</span>
                            @endif
                        </td>
                        <td>
                            {!! $statusBadge !!}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #64748b;">
                            <i class="fas fa-inbox fa-3x" style="color: #cbd5e1; margin-bottom: 10px;"></i>
                            <p style="margin: 0;">Tidak ada data ditemukan.</p>
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
