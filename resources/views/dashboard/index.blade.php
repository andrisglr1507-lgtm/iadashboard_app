@extends('layouts.app')

@section('page_title', 'Opname Dashboard')

@section('page_actions')
<button class="btn-refresh" style="display: inline-flex; align-items: center; gap: 8px; background: white; color: var(--phoenix-text-muted); border: 1px solid var(--phoenix-border-color); padding: 8px 16px; border-radius: var(--phoenix-btn-radius); font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.02);" onmouseover="this.style.color='var(--phoenix-primary)'; this.style.borderColor='var(--phoenix-primary)';" onmouseout="this.style.color='var(--phoenix-text-muted)'; this.style.borderColor='var(--phoenix-border-color)';">
    <i class="fas fa-sync-alt"></i> Refresh Data
</button>
@endsection

@section('content')

@php
    $totalSku = $totalSku ?? 0;
    $skuCounted = $skuCounted ?? 0;
    $skuUncounted = $totalSku - $skuCounted;
    $skuVariance = $skuVariance ?? 0;

    $pctCounted = $totalSku > 0 ? ($skuCounted / $totalSku) * 100 : 0;
    
    $teamAProgress = $teamAProgress ?? 0;
    $teamBProgress = $teamBProgress ?? 0;
    $recountProgress = $recountProgress ?? 0;
@endphp

@if(isset($session) && $session)
<div class="content-panel" style="padding: 16px 24px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; border-left: 4px solid var(--phoenix-primary);">
    <div>
        <h4 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--phoenix-dark); letter-spacing: 0.5px;"><i class="fas fa-satellite-dish text-primary me-2"></i> Realtime Dashboard</h4>
        <p style="margin: 4px 0 0; font-size: 0.85rem; color: var(--phoenix-text-muted);">Data di bawah mengacu pada Sesi Aktif: <strong style="color: var(--phoenix-dark);">{{ $session->session_code }}</strong> (Cabang: {{ $session->branch_id }})</p>
    </div>
    <div style="background: rgba(39, 199, 143, 0.1); color: var(--phoenix-success); padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; border: 1px solid rgba(39, 199, 143, 0.2);">
        <i class="fas fa-circle" style="font-size: 0.5rem; margin-right: 6px; animation: blink 2s infinite;"></i> Live Updates
    </div>
</div>
<style>
@keyframes blink { 50% { opacity: 0.3; } }
</style>
@else
<div class="alert alert-warning">Tidak ada Sesi Opname yang aktif saat ini. Menampilkan data kosong.</div>
@endif

<!-- Scorecards Row -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 24px;">
    
    <!-- Card 1: Total SKU -->
    <div class="content-panel" style="padding: 24px; position: relative; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h4 style="margin: 0; color: var(--phoenix-text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Total SKU</h4>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--phoenix-dark); letter-spacing: -0.02em;">{{ number_format($totalSku) }}</div>
            </div>
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(56, 116, 255, 0.1); display: flex; align-items: center; justify-content: center; color: var(--phoenix-primary);">
                <i class="fas fa-boxes" style="font-size: 1.25rem;"></i>
            </div>
        </div>
        <div style="margin-top: 16px; font-size: 0.8rem; color: var(--phoenix-text-muted);">
            <span style="color: var(--phoenix-success); font-weight: 600;"><i class="fas fa-arrow-up"></i> WMS Database</span> Sync active
        </div>
    </div>

    <!-- Card 2: SKU Counted -->
    <div class="content-panel" style="padding: 24px; position: relative; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h4 style="margin: 0; color: var(--phoenix-text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">SKU Terhitung</h4>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--phoenix-dark); letter-spacing: -0.02em;">{{ number_format($skuCounted) }}</div>
            </div>
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(39, 199, 143, 0.1); display: flex; align-items: center; justify-content: center; color: var(--phoenix-success);">
                <i class="fas fa-check-circle" style="font-size: 1.25rem;"></i>
            </div>
        </div>
        <div style="margin-top: 16px; width: 100%; height: 6px; background: var(--phoenix-border-color); border-radius: 4px; overflow: hidden;">
            <div style="height: 100%; background: var(--phoenix-success); width: {{ $pctCounted }}%; border-radius: 4px;"></div>
        </div>
        <div style="margin-top: 8px; font-size: 0.8rem; color: var(--phoenix-text-muted); text-align: right; font-weight: 600;">
            {{ number_format($pctCounted, 0) }}% Selesai
        </div>
    </div>

    <!-- Card 3: SKU Uncounted -->
    <div class="content-panel" style="padding: 24px; position: relative; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h4 style="margin: 0; color: var(--phoenix-text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Belum Dihitung</h4>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--phoenix-dark); letter-spacing: -0.02em;">{{ number_format($skuUncounted) }}</div>
            </div>
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center; color: var(--phoenix-warning);">
                <i class="fas fa-clock" style="font-size: 1.25rem;"></i>
            </div>
        </div>
        <div style="margin-top: 16px; font-size: 0.8rem; color: var(--phoenix-text-muted);">
            Sisa barang di area Floor & Rack
        </div>
    </div>

    <!-- Card 4: Variance / Recount -->
    <div class="content-panel" style="padding: 24px; position: relative; overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h4 style="margin: 0; color: var(--phoenix-text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Selisih / Recount</h4>
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--phoenix-danger); letter-spacing: -0.02em;">{{ number_format($skuVariance) }}</div>
            </div>
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(230, 55, 87, 0.1); display: flex; align-items: center; justify-content: center; color: var(--phoenix-danger);">
                <i class="fas fa-exclamation-triangle" style="font-size: 1.25rem;"></i>
            </div>
        </div>
        <div style="margin-top: 16px; font-size: 0.8rem; color: var(--phoenix-text-muted);">
            <a href="{{ route('sodc.results.index', ['status' => 'RECOUNT']) }}" style="color: var(--phoenix-primary); font-weight: 600; text-decoration: none;">Lihat Detail &rarr;</a>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    
    <!-- Left Column: Top Variance & Chart -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        
        <!-- Leaderboard Tim (Scan Terbanyak) -->
        <div class="content-panel" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--phoenix-dark);"><i class="fas fa-trophy text-warning me-2"></i> Leaderboard Petugas (Scan Terbanyak)</h3>
            </div>
            
            <div class="table-container">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">Rank</th>
                            <th>Nama Petugas</th>
                            <th>Role / Tim</th>
                            <th style="text-align: right;">Total Scan Barang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($topCounters) && count($topCounters) > 0)
                            @foreach($topCounters as $index => $c)
                            <tr>
                                <td style="text-align: center; font-weight: 800; color: {{ $index == 0 ? '#f59e0b' : ($index == 1 ? '#94a3b8' : ($index == 2 ? '#b45309' : 'var(--phoenix-text-muted)')) }}; font-size: 1.2rem;">
                                    #{{ $index + 1 }}
                                </td>
                                <td style="font-weight: 700; color: var(--phoenix-dark);">{{ $c->user->full_name ?? 'Unknown User' }}</td>
                                <td style="color: var(--phoenix-text-muted); font-size: 0.85rem;">{{ $c->user->role ?? '-' }}</td>
                                <td style="text-align: right; font-weight: 800; color: var(--phoenix-primary); font-size: 1.1rem;">{{ number_format($c->total_scan) }}</td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--phoenix-text-muted); padding: 24px;">Belum ada data hasil scan yang masuk.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Right Column: Progress Teams & Activity -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        
        <!-- Team Progress -->
        <div class="content-panel" style="padding: 24px;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--phoenix-dark); margin-bottom: 24px;">Progress Area Terhitung</h3>
            
            <!-- Team A -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.85rem; font-weight: 600;">
                    <span style="color: var(--phoenix-dark);"><i class="fas fa-users" style="color: var(--phoenix-primary); margin-right: 6px;"></i>Tim A (Initial)</span>
                    <span style="color: var(--phoenix-text-muted);">{{ $teamAProgress }}%</span>
                </div>
                <div style="width: 100%; height: 6px; background: var(--phoenix-border-color); border-radius: 4px; overflow: hidden;">
                    <div style="height: 100%; background: var(--phoenix-primary); width: {{ $teamAProgress }}%; border-radius: 4px;"></div>
                </div>
            </div>

            <!-- Team B -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.85rem; font-weight: 600;">
                    <span style="color: var(--phoenix-dark);"><i class="fas fa-users" style="color: #8b5cf6; margin-right: 6px;"></i>Tim B (Blind)</span>
                    <span style="color: var(--phoenix-text-muted);">{{ $teamBProgress }}%</span>
                </div>
                <div style="width: 100%; height: 6px; background: var(--phoenix-border-color); border-radius: 4px; overflow: hidden;">
                    <div style="height: 100%; background: #8b5cf6; width: {{ $teamBProgress }}%; border-radius: 4px;"></div>
                </div>
            </div>

            <!-- Team Recount -->
            <div style="margin-bottom: 10px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.85rem; font-weight: 600;">
                    <span style="color: var(--phoenix-dark);"><i class="fas fa-search" style="color: var(--phoenix-warning); margin-right: 6px;"></i>Tim Recount</span>
                    <span style="color: var(--phoenix-text-muted);">{{ $recountProgress }}% Selesai</span>
                </div>
                <div style="width: 100%; height: 6px; background: var(--phoenix-border-color); border-radius: 4px; overflow: hidden;">
                    <div style="height: 100%; background: var(--phoenix-warning); width: {{ $recountProgress }}%; border-radius: 4px;"></div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Log (Realtime) -->
        <div class="content-panel" style="padding: 24px;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--phoenix-dark); margin-bottom: 24px;"><i class="fas fa-bolt text-warning me-2"></i> Aktivitas Scan Terkini</h3>
            
            <div style="display: flex; flex-direction: column; gap: 16px;">
                @if(isset($recentActivities) && count($recentActivities) > 0)
                    @foreach($recentActivities as $act)
                    <div style="display: flex; gap: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                        <div style="width: 10px; height: 10px; border-radius: 50%; background: var(--phoenix-success); margin-top: 5px; flex-shrink: 0;"></div>
                        <div>
                            <div style="font-size: 0.85rem; font-weight: 700; color: var(--phoenix-dark);">{{ $act->user->full_name ?? 'Sistem' }} <span style="font-weight: normal; color: #64748b; font-size: 0.75rem;">({{ $act->team_id ?? 'N/A' }})</span></div>
                            <div style="font-size: 0.75rem; color: var(--phoenix-text-muted); margin-top: 2px;">Melakukan scan untuk <strong>{{ $act->referenceDetail->sku_code ?? ($act->referenceDetail->product->sku_code ?? '?') }}</strong> di lokasi <strong>{{ $act->referenceDetail->bin_code ?? '?' }}</strong> dengan qty <span style="font-weight: 700; color: var(--phoenix-primary);">{{ (float)$act->count_qty }}</span></div>
                            <div style="font-size: 0.7rem; color: #a0aec0; margin-top: 4px;"><i class="far fa-clock me-1"></i>{{ $act->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div style="text-align: center; padding: 20px; color: var(--phoenix-text-muted); font-size: 0.85rem;">Belum ada aktivitas.</div>
                @endif
            </div>
            
            <div style="margin-top: 16px; text-align: center;">
                <a href="#" style="font-size: 0.8rem; font-weight: 700; color: var(--phoenix-primary); text-decoration: none;">(Hanya menampilkan 7 scan terakhir)</a>
            </div>
        </div>

    </div>
</div>

@endsection