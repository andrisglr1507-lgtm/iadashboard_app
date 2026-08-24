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
            {{ number_format($pctCounted, 1) }}% Selesai
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
        
        <!-- Top 5 Selisih Chart -->
        <div class="content-panel" style="padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--phoenix-dark);">10 SKU Selisih Terbesar (Minus)</h3>
                <button style="background: none; border: none; color: var(--phoenix-text-muted); cursor: pointer;"><i class="fas fa-ellipsis-h"></i></button>
            </div>
            
            <div class="table-container">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>SKU Code</th>
                            <th>Product Name</th>
                            <th style="text-align: right;">System Qty</th>
                            <th style="text-align: right;">Physical Qty</th>
                            <th style="text-align: right;">Variance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($topVariances) && count($topVariances) > 0)
                            @foreach($topVariances as $v)
                            <tr>
                                <td style="font-weight: 700; color: var(--phoenix-primary);">{{ $v->referenceDetail->sku_code ?? '-' }}</td>
                                <td style="font-weight: 600;">{{ Str::limit($v->referenceDetail->product->sku_name ?? 'Unknown Product', 40) }}</td>
                                <td style="text-align: right;">{{ (float)$v->system_qty }}</td>
                                <td style="text-align: right;">{{ (float)$v->team_a_qty }}</td>
                                <td style="text-align: right; color: var(--phoenix-danger); font-weight: 700;">{{ (float)($v->team_a_qty - $v->system_qty) }}</td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--phoenix-text-muted); padding: 24px;">Belum ada data selisih yang ditemukan.</td>
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

        <!-- Recent Activity Log (Dummy) -->
        <div class="content-panel" style="padding: 24px;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--phoenix-dark); margin-bottom: 24px;">Aktivitas Terkini</h3>
            
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div style="display: flex; gap: 12px;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--phoenix-success); margin-top: 6px;"></div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 600; color: var(--phoenix-dark);">Andri Setiawan (Tim A)</div>
                        <div style="font-size: 0.75rem; color: var(--phoenix-text-muted);">Mensubmit hasil hitung Bin A-01-01</div>
                        <div style="font-size: 0.7rem; color: #a0aec0; margin-top: 4px;">2 menit lalu</div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--phoenix-warning); margin-top: 6px;"></div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 600; color: var(--phoenix-dark);">Budi (Tim Recount)</div>
                        <div style="font-size: 0.75rem; color: var(--phoenix-text-muted);">Memulai Recount 1 untuk SKU 01-001A</div>
                        <div style="font-size: 0.7rem; color: #a0aec0; margin-top: 4px;">15 menit lalu</div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--phoenix-danger); margin-top: 6px;"></div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 600; color: var(--phoenix-dark);">Sistem Auto-Dispatch</div>
                        <div style="font-size: 0.75rem; color: var(--phoenix-text-muted);">Mendistribusikan 12 tugas Recount baru</div>
                        <div style="font-size: 0.7rem; color: #a0aec0; margin-top: 4px;">1 jam lalu</div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 16px; text-align: center;">
                <a href="#" style="font-size: 0.8rem; font-weight: 700; color: var(--phoenix-primary); text-decoration: none;">Lihat Semua Aktivitas</a>
            </div>
        </div>

    </div>
</div>

@endsection