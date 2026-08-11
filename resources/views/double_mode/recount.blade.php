@extends('layouts.app')

@section('title', 'Single Mode - Recount')
@section('page_title', 'Recount')

@section('page_actions')
@if(!empty($activeSessionId) && !$error)
<div class="recount-stats-inline">
    <span class="stat-badge total">SKU: <strong>{{ $stats['totalData'] }}</strong></span>
    <span class="stat-badge final">Final: <strong>{{ $stats['finalData'] }}</strong></span>
    <span class="stat-badge r1">Need R1: <strong>{{ $stats['needR1'] }}</strong></span>
    <span class="stat-badge r2">Need R2: <strong>{{ $stats['needR2'] }}</strong></span>
    @if($stats['possibleTypo'] > 0)
        <span class="stat-badge typo" title="Potensi Typo Kode Produk">❗ Typo: <strong>{{ $stats['possibleTypo'] }}</strong></span>
    @endif
</div>
@endif
@endsection

@section('content')

@if($error)
    <div style="display: flex; align-items: flex-start; gap: 12px; background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; padding: 14px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 0.85rem;">
        <i class="fas fa-exclamation-triangle" style="margin-top: 2px;"></i>
        <div>{{ $error }}</div>
    </div>
@else
    <!-- Actions & Filters Bar -->
    <div class="recount-filter-bar">
        <!-- Filter Principal -->
        <div class="filter-group">
            <label for="principalFilter">Principal:</label>
            <select id="principalFilter">
                <option value="">Semua Principal</option>
                @foreach ($principals as $p)
                    <option value="{{ $p }}">{{ $p }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filter Status -->
        <div class="filter-group">
            <label for="statusFilter">Status:</label>
            <select id="statusFilter">
                <option value="">Semua Status</option>
                <option value="MATCH">MATCH</option>
                <option value="NEED R1">NEED R1</option>
                <option value="NEED R2">NEED R2</option>
                <option value="WAITING">WAITING</option>
                <option value="FINAL R1">FINAL R1</option>
                <option value="FINAL R2">FINAL R2</option>
                <option value="FINAL R3">FINAL R3</option>
            </select>
        </div>

        <!-- Filter Analisa -->
        <div class="filter-group">
            <label for="analisaFilter">Analisa AI:</label>
            <select id="analisaFilter">
                <option value="">Semua</option>
                <option value="has_analisa">⚠️ Ada Anomali</option>
                <option value="possible_typo">❗ Analisa</option>
                <option value="anomaly">🔍 Perlu Investigasi</option>
            </select>
        </div>

        <!-- Vertical Divider -->
        <div class="divider-vertical"></div>

        <!-- Assign Combobox + Button -->
        <div class="filter-group">
            <select id="userSelect">
                <option value="">-- Pilih User --</option>
                @foreach ($users as $user)
                    <option value="{{ $user->user_id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            
            <button id="assignBtn" class="btn-assign" disabled>
                <span id="assignBtnText">Assign</span>
                <span id="assignBtnLoading" class="hidden"><i class="fas fa-spinner fa-spin"></i></span>
            </button>
        </div>

        <!-- Selected Counter -->
        <div class="selected-label">
            <span id="selectedCount">0</span> terpilih
        </div>

        <!-- Vertical Divider -->
        <div class="divider-vertical"></div>

        <!-- Tombol Analisa Produk -->
        @if($stats['totalAnalysis'] > 0)
            <button id="openAnalysisModal" class="btn-analysis-modal">
                <i class="fas fa-brain"></i>
                <span>Analisa Kemiripan Produk</span>
                <span class="analysis-badge-pulse">{{ $stats['totalAnalysis'] }}</span>
            </button>
        @else
            <div style="margin-left: auto; font-size: 0.75rem; color: var(--text-secondary); font-style: italic;">
                ✅ Tidak ada anomali terdeteksi
            </div>
        @endif
    </div>

    <!-- DataTable Container -->
    <div style="background: white; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.03); overflow-x: auto; width: 100%;">
        <table id="singleTable" class="display compact nowrap" style="width:100%">
            <thead>
                <tr>
                    <th rowspan="2" width="40">Pilih</th>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Product ID</th>
                    <th rowspan="2">Lokasi</th>
                    <th rowspan="2">Principal</th>
                    <th rowspan="2">Product Name</th>
                    <th rowspan="2">Packname</th>
                    <th rowspan="2">UOM</th>
                    <th colspan="3">Stock System</th>
                    <th colspan="3">Team A</th>
                    <th colspan="3">Team B</th>
                    <th rowspan="2" width="180">Analisa AI</th>
                    <th colspan="3">Recount 1 (R1)</th>
                    <th colspan="3">Recount 2 (R2)</th>
                    <th rowspan="2">Status</th>
                    <th rowspan="2">Result</th>
                    <th rowspan="2" width="50">Aksi</th>
                </tr>
                <tr>
                    <th>Krt</th><th>Pcs</th><th>Total</th>
                    <th>Krt</th><th>Pcs</th><th>Total</th>
                    <th>Krt</th><th>Pcs</th><th>Total</th>
                    <th>Krt</th><th>Pcs</th><th>Total</th>
                    <th>Krt</th><th>Pcs</th><th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($summaryData as $row)
                    @php
                        $checkboxValue = $row['id_product'] . '|' . $row['location'] . '|' . $row['round_to_assign'];
                        $canAssign = $row['can_assign'] && $row['round_to_assign'] !== null;
                        $assignRound = $row['round_to_assign'] == 2 ? 'R1' : ($row['round_to_assign'] == 3 ? 'R2' : '');
                        $analisisType = $row['analisis']['type'] ?? '';
                        $hasInput = (($row['a_total'] !== '-' && $row['a_total'] > 0) || ($row['b_total'] !== '-' && $row['b_total'] > 0));
                        $isFinalRow = str_starts_with($row['result_stage'], 'final');
                        $isTypoRow = ($row['stock_system'] == 0 && $hasInput);
                    @endphp
                    <tr class="{{ $isFinalRow ? 'row-final' : '' }} {{ $isTypoRow ? 'row-typo-highlight' : '' }}"
                        data-stage="{{ $row['result_stage'] }}"
                        data-can-assign="{{ $canAssign ? 'true' : 'false' }}"
                        data-analisa-type="{{ $analisisType }}"
                        data-stock-system="{{ $row['stock_system'] }}"
                        data-a-total="{{ $row['a_total'] }}"
                        data-b-total="{{ $row['b_total'] }}">
                        
                        <!-- Checkbox -->
                        <td style="text-align: center;">
                            @if($canAssign)
                                <input type="checkbox" 
                                       class="product-checkbox"
                                       value="{{ $checkboxValue }}"
                                       data-product="{{ $row['id_product'] }}"
                                       data-round="{{ $row['round_to_assign'] }}">
                                @if($assignRound)
                                    <span style="font-size: 8px; color: var(--text-secondary); display: block; font-weight: 600;">({{ $assignRound }})</span>
                                @endif
                            @else
                                <input type="checkbox" disabled style="opacity: 0.4; cursor: not-allowed;">
                            @endif
                        </td>

                        <td style="text-align: center;">{{ $row['no'] }}</td>
                        <td class="font-mono font-medium {{ $isTypoRow ? 'text-orange-bold' : '' }}">
                            {{ $row['id_product'] }}
                        </td>
                        <td class="font-mono" style="color: #b45309; font-weight: 600; text-align: center;">
                            {{ $row['location'] }}
                        </td>
                        <td style="font-weight: 500;">{{ $row['principal'] }}</td>
                        <td title="{{ $row['product_name'] }}">{{ Str::limit($row['product_name'], 40) }}</td>
                        <td>{{ $row['packname'] }}</td>
                        <td style="text-align: center;">{{ $row['uom'] }}</td>
                        
                        <!-- Stock System -->
                        <td class="text-right num-col {{ $row['stock_system'] == 0 ? 'bg-red-light text-red-muted' : '' }}">
                            {{ number_format($row['stock_system_karton']) }}
                        </td>
                        <td class="text-right num-col {{ $row['stock_system'] == 0 ? 'bg-red-light text-red-muted' : '' }}">
                            {{ number_format($row['stock_system_pcs']) }}
                        </td>
                        <td class="text-right num-col font-semibold {{ $row['stock_system'] == 0 ? 'bg-red-medium text-red-dark' : '' }}">
                            {{ number_format($row['stock_system']) }}
                        </td>
                        
                        <!-- Team A -->
                        <td class="text-right num-col">{{ $row['a_karton'] !== '-' ? number_format($row['a_karton']) : '-' }}</td>
                        <td class="text-right num-col">{{ $row['a_pcs'] !== '-' ? number_format($row['a_pcs']) : '-' }}</td>
                        <td class="text-right num-col font-semibold">
                            {{ $row['a_total'] !== '-' ? number_format($row['a_total']) : '-' }}
                        </td>
                        
                        <!-- Team B -->
                        <td class="text-right num-col">{{ $row['b_karton'] !== '-' ? number_format($row['b_karton']) : '-' }}</td>
                        <td class="text-right num-col">{{ $row['b_pcs'] !== '-' ? number_format($row['b_pcs']) : '-' }}</td>
                        <td class="text-right num-col font-semibold">
                            {{ $row['b_total'] !== '-' ? number_format($row['b_total']) : '-' }}
                        </td>

                        <!-- AI Analysis -->
                        <td>
                            @if($row['has_analisis'] && $row['analisis'])
                                <div class="analisa-container">
                                    <div class="analisa-trigger {{ $row['analisis']['type'] }}">
                                        @if($row['analisis']['type'] == 'possible_typo')
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <span>🔍 Analisa</span>
                                        @elseif($row['analisis']['type'] == 'anomaly')
                                            <i class="fas fa-question-circle"></i>
                                            <span>⚠️ Anomali</span>
                                        @else
                                            <i class="fas fa-info-circle"></i>
                                            <span>Info Kode</span>
                                        @endif
                                    </div>
                                    
                                    <!-- Tooltip Popup -->
                                    <div class="analisa-tooltip">
                                        <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                                            <span style="font-size: 1.1rem;">
                                                @if($row['analisis']['type'] == 'possible_typo') ❗ @elseif($row['analisis']['type'] == 'anomaly') ⚠️ @else ℹ️ @endif
                                            </span>
                                            <div style="font-size: 0.75rem; font-weight: 700; line-height: 1.3; color: {{ $row['analisis']['type'] == 'possible_typo' ? '#dc2626' : ($row['analisis']['type'] == 'anomaly' ? '#ea580c' : '#ca8a04') }}">
                                                {{ $row['analisis']['message'] }}
                                            </div>
                                        </div>
                                        
                                        @if(!empty($row['analisis']['suggestions']))
                                            <div style="font-size: 0.7rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">💡 Rekomendasi:</div>
                                            <div class="suggestion-list">
                                                @foreach ($row['analisis']['suggestions'] as $suggestion)
                                                    <div class="suggestion-item">
                                                        <div>
                                                            <code>{{ $suggestion['id_product'] }}</code>
                                                            @if(isset($suggestion['product_name']))
                                                                <div style="font-size: 9px; color: var(--text-secondary); white-space: nowrap; max-width: 160px; overflow: hidden; text-overflow: ellipsis;" title="{{ $suggestion['product_name'] }}">
                                                                    {{ $suggestion['product_name'] }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <span class="stock">Stok: {{ number_format($suggestion['stock_system']) }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                        <div style="margin-top: 8px; padding-top: 6px; border-top: 1px solid var(--border); font-size: 9px; color: var(--text-secondary); display: flex; gap: 4px;">
                                            <span>🤖</span>
                                            <span>Terdeteksi anomali input. Cek kemungkinan kesalahan input kode produk.</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span style="color: #cbd5e1; font-size: 0.75rem;">-</span>
                            @endif
                        </td>
                        
                        <!-- R1 -->
                        <td class="text-right num-col">{{ $row['r1_karton'] !== '-' ? number_format($row['r1_karton']) : '-' }}</td>
                        <td class="text-right num-col">{{ $row['r1_pcs'] !== '-' ? number_format($row['r1_pcs']) : '-' }}</td>
                        <td class="text-right num-col">{{ $row['r1_total'] !== '-' ? number_format($row['r1_total']) : '-' }}</td>
                        
                        <!-- R2 -->
                        <td class="text-right num-col">{{ $row['r2_karton'] !== '-' ? number_format($row['r2_karton']) : '-' }}</td>
                        <td class="text-right num-col">{{ $row['r2_pcs'] !== '-' ? number_format($row['r2_pcs']) : '-' }}</td>
                        <td class="text-right num-col font-semibold">{{ $row['r2_total'] !== '-' ? number_format($row['r2_total']) : '-' }}</td>
                        
                        <!-- Status & Result -->
                        <td style="text-align: center;">
                            <span class="badge-stage {{ $row['result_stage'] }}">{{ $row['result_label'] }}</span>
                        </td>
                        <td class="text-right num-col font-bold" style="color: {{ $row['result_total'] ? 'var(--success)' : 'var(--text-secondary)' }}">
                            {{ $row['result_total'] ? number_format($row['result_total']) : '-' }}
                        </td>
                        
                        <!-- Actions -->
                        <td style="text-align: center;">
                            <button type="button" class="btn-detail" data-product="{{ $row['id_product'] }}" title="Lihat detail histori">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- ============================================ -->
    <!-- MODAL DETAIL HISTORI HITUNG -->
    <!-- ============================================ -->
    <div id="detailModal" class="modal-overlay">
        <div class="modal-window">
            <div class="modal-header">
                <div>
                    <h3 style="margin: 0; font-size: 1.05rem;">Histori Perhitungan Produk</h3>
                    <p id="modalSubTitle" style="margin: 4px 0 0 0; font-size: 0.78rem;"></p>
                </div>
                <button type="button" class="modal-close" id="closeModalBtn">&times;</button>
            </div>
            
            <div class="modal-body">
                <div class="modal-product-summary">
                    <div class="summary-tile">
                        <span class="label">Principal</span>
                        <p class="value" id="mPrincipal">-</p>
                    </div>
                    <div class="summary-tile" style="flex: 2;">
                        <span class="label">Nama Produk</span>
                        <p class="value" id="mProdName" title="">-</p>
                    </div>
                    <div class="summary-tile">
                        <span class="label">Packname</span>
                        <p class="value" id="mPackname">-</p>
                    </div>
                    <div class="summary-tile">
                        <span class="label">UOM</span>
                        <p class="value" id="mUom">-</p>
                    </div>
                </div>

                <div style="margin-top: 16px;">
                    <h4 style="font-size: 0.85rem; font-weight: 600; color: var(--dark); margin-bottom: 8px;">Detail Hitung Sesi (Per Lokasi & Putaran)</h4>
                    <div class="modal-table-container">
                        <table id="modalTable" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th width="150">Product ID</th>
                                    <th>Lokasi</th>
                                    <th>Tim/Putaran</th>
                                    <th style="text-align: right;" width="90">Karton</th>
                                    <th style="text-align: right;" width="90">Pcs</th>
                                    <th style="text-align: right;" width="110">Total Qty (Pcs)</th>
                                    <th>Waktu Hitung</th>
                                    <th style="text-align: center;" width="130">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="modalTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="closeModalBtn2">Tutup</button>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- MODAL ANALISA KEMIRIPAN PRODUK (BESAR) -->
    <!-- ============================================ -->
    <div id="analysisModal" class="modal-overlay">
        <div class="modal-window" style="max-width: 1100px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #4c1d95, #1e3a8a);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="analysis-header-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 1.05rem;">Analisa Kemiripan Produk</h3>
                        <p style="margin: 4px 0 0 0; font-size: 0.78rem; color: #cbd5e1;">Rekomendasi koreksi berdasarkan kode & nama produk yang stok sistemnya bernilai 0</p>
                    </div>
                </div>
                <button type="button" class="modal-close" id="closeAnalysisBtn">&times;</button>
            </div>
            
            <!-- Stats Bar -->
            <div class="analysis-stats-bar">
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 16px;">
                    <div class="indicator"><span class="dot violet"></span> Total Anomali: <strong class="text-violet">{{ $stats['totalAnalysis'] }}</strong></div>
                    <div class="divider-v"></div>
                    @if($stats['highConfidence'] > 0)
                        <div class="indicator"><span class="dot red pulse"></span> Kemungkinan Tinggi: <strong class="text-danger">{{ $stats['highConfidence'] }}</strong></div>
                    @endif
                    @if($stats['medConfidence'] > 0)
                        <div class="indicator"><span class="dot amber"></span> Kemungkinan Sedang: <strong class="text-warning">{{ $stats['medConfidence'] }}</strong></div>
                    @endif
                    @if($stats['lowConfidence'] > 0)
                        <div class="indicator"><span class="dot gray"></span> Kemungkinan Rendah: <strong class="text-secondary">{{ $stats['lowConfidence'] }}</strong></div>
                    @endif
                    <div class="divider-v"></div>
                    <div class="indicator"><span class="badge-mini match-code">KODE</span> <strong>{{ $stats['codeMatches'] }}</strong></div>
                    <div class="indicator"><span class="badge-mini match-name">NAMA</span> <strong>{{ $stats['nameMatches'] }}</strong></div>
                    
                    <!-- Filters inside modal -->
                    <div style="margin-left: auto; display: flex; gap: 8px;">
                        <select id="analysisConfidenceFilter" class="mini-select">
                            <option value="">Semua Level</option>
                            <option value="Tinggi">🔴 Tinggi</option>
                            <option value="Sedang">🟡 Sedang</option>
                            <option value="Rendah">⚪ Rendah</option>
                        </select>
                        <select id="analysisTypeFilter" class="mini-select">
                            <option value="">Semua Tipe</option>
                            <option value="both">Kode & Nama</option>
                            <option value="code">Hanya Kode</option>
                            <option value="name">Hanya Nama</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Legend Bar -->
            <div class="analysis-legend-bar">
                <span>ℹ️ <strong>Cara Membaca:</strong> Produk dengan stok sistem = 0 namun terdapat input fisik dicocokkan dengan produk berstok yang kodenya mirip (Levenshtein) atau namanya mirip (Similar Text / Jaccard).</span>
            </div>
            
            <div class="modal-body" style="background: #f8fafc; padding: 16px;">
                <div class="analysis-cards-container" id="analysisContent"></div>
            </div>
            
            <div class="modal-footer">
                <div style="margin-right: auto; font-size: 0.72rem; color: var(--text-secondary); display: flex; align-items: center; gap: 6px;">
                    <span>🤖</span>
                    <span>Analisa dijalankan secara otomatis berdasarkan algoritma pencarian kecocokan string pada database</span>
                </div>
                <button type="button" class="btn-secondary" id="closeAnalysisBtn2">Tutup</button>
            </div>
        </div>
    </div>
@endif

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<style>
    /* Premium aesthetics for recount single mode */
    :root {
        --bg-final: #10b981;
        --bg-need-r1: #f59e0b;
        --bg-need-r2: #ef4444;
        --bg-waiting: #94a3b8;
    }

    .recount-stats-inline {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .stat-badge {
        display: inline-flex;
        align-items: center;
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--text-secondary);
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }

    .stat-badge strong {
        font-weight: 700;
        margin-left: 4px;
    }

    .stat-badge.total strong { color: var(--dark); }
    .stat-badge.final strong { color: var(--success); }
    .stat-badge.r1 strong { color: var(--warning); }
    .stat-badge.r2 strong { color: var(--danger); }
    .stat-badge.typo {
        border-color: #fecaca;
        background: #fef2f2;
    }
    .stat-badge.typo strong { color: #dc2626; }

    /* Filter Bar style */
    .recount-filter-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        background: white;
        padding: 10px 16px;
        border-radius: 10px;
        border: 1px solid var(--border);
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        margin-bottom: 16px;
        width: 100%;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .filter-group label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .filter-group select {
        padding: 5px 10px;
        border: 1px solid var(--border);
        border-radius: 6px;
        background: white;
        font-size: 0.75rem;
        outline: none;
        color: var(--dark);
        font-weight: 500;
    }

    .filter-group select:focus {
        border-color: var(--primary);
    }

    .divider-vertical {
        width: 1px;
        height: 20px;
        background: var(--border);
    }

    .btn-assign {
        background: var(--success);
        color: white;
        border: none;
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .btn-assign:hover {
        background: #059669;
    }

    .btn-assign:disabled {
        background: #a7f3d0;
        cursor: not-allowed;
    }

    .selected-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .selected-label span {
        color: var(--success);
        font-weight: 700;
    }

    .btn-analysis-modal {
        margin-left: auto;
        background: linear-gradient(135deg, #7c3aed, #2563eb);
        color: white;
        border: none;
        border-radius: 6px;
        padding: 6px 14px;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.15);
        transition: all 0.2s;
    }

    .btn-analysis-modal:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 10px -1px rgba(99, 102, 241, 0.25);
    }

    .analysis-badge-pulse {
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        animation: pulse-badge 2s infinite;
    }

    @keyframes pulse-badge {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        50% { transform: scale(1.1); box-shadow: 0 0 0 4px rgba(239, 68, 68, 0); }
    }

    /* DataTable styles */
    #singleTable {
        font-size: 0.8rem;
        border-collapse: collapse;
        width: 100% !important;
    }

    #singleTable thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        padding: 8px 10px;
        border: 1px solid var(--border);
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }

    #singleTable tbody td {
        padding: 6px 10px;
        border: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #334155;
    }

    #singleTable tbody tr:hover {
        background: #f8fafc;
    }

    #singleTable tbody tr.row-final {
        background: #f0fdf4;
    }

    #singleTable tbody tr.row-final:hover {
        background: #dcfce7;
    }

    #singleTable tbody tr.row-typo-highlight {
        border-left: 4px solid #f97316;
        background: #fffbeb;
    }

    #singleTable tbody tr.row-typo-highlight:hover {
        background: #fef3c7;
    }

    .font-mono {
        font-family: 'Courier New', monospace;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .text-orange-bold {
        color: #ea580c;
        font-weight: 700;
    }

    .text-orange-medium {
        color: #d97706;
        font-weight: 600;
    }

    .num-col {
        font-variant-numeric: tabular-nums;
    }

    /* Background classes */
    .bg-red-light { background: #fef2f2 !important; }
    .text-red-muted { color: #f87171 !important; }
    .bg-red-medium { background: #fee2e2 !important; }
    .text-red-dark { color: #b91c1c !important; }

    /* Badge Stages */
    .badge-stage {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        color: white;
        letter-spacing: 0.3px;
        text-align: center;
    }
    .badge-stage.final { background: var(--bg-final); }
    .badge-stage.need_r1 { background: var(--bg-need-r1); }
    .badge-stage.need_r2 { background: var(--bg-need-r2); }
    .badge-stage.waiting { background: var(--bg-waiting); }
    .badge-stage.assigned { background: #3b82f6; } /* Blue for assigned */

    /* AI Analysis cells */
    .analisa-container {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .analisa-container:hover .analisa-tooltip {
        display: block;
    }

    .analisa-trigger {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.72rem;
        font-weight: 600;
        border: 1px solid transparent;
        width: max-content;
        transition: all 0.2s;
    }

    .analisa-trigger.possible_typo {
        color: #dc2626;
        background: #fef2f2;
        border-color: #fecaca;
        animation: pulse-slow 2s infinite;
    }

    .analisa-trigger.anomaly {
        color: #ea580c;
        background: #fff7ed;
        border-color: #ffedd5;
    }

    .analisa-trigger.similar_code {
        color: #ca8a04;
        background: #fef9c3;
        border-color: #fef08a;
    }

    @keyframes pulse-slow {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.75; }
    }

    .analisa-tooltip {
        display: none;
        position: absolute;
        z-index: 100;
        background: white;
        border: 1px solid var(--border);
        border-radius: 8px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        padding: 10px;
        min-width: 280px;
        left: 0;
        top: 100%;
        margin-top: 4px;
    }

    /* Suggestion Items */
    .suggestion-list {
        display: flex;
        flex-direction: column;
        gap: 4px;
        max-height: 120px;
        overflow-y: auto;
        margin-top: 4px;
    }

    .suggestion-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.7rem;
        padding: 5px 8px;
        border-radius: 4px;
        border: 1px solid #f1f5f9;
        background: #f8fafc;
    }

    .suggestion-item:hover {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .suggestion-item code {
        font-family: monospace;
        font-weight: 700;
        color: var(--dark);
        font-size: 0.75rem;
    }

    .suggestion-item .stock {
        color: var(--success);
        font-weight: 600;
    }

    /* Actions icons */
    .btn-detail {
        background: transparent;
        border: none;
        color: var(--primary);
        cursor: pointer;
        font-size: 1rem;
        padding: 4px;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .btn-detail:hover {
        background: #eff6ff;
        color: var(--primary-dark);
    }

    /* Checkbox */
    .product-checkbox {
        width: 15px;
        height: 15px;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
        cursor: pointer;
    }

    /* MODAL WINDOWS */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-window {
        background: white;
        border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        width: 95%;
        max-width: 900px;
        max-height: 88vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        animation: modal-enter 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes modal-enter {
        from { opacity: 0; transform: scale(0.96) translateY(8px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .modal-header {
        background: #0f172a;
        color: white;
        padding: 14px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .modal-header p {
        margin: 4px 0 0 0;
        font-size: 0.72rem;
        color: #94a3b8;
    }

    .modal-close {
        background: transparent;
        border: none;
        color: #94a3b8;
        font-size: 1.6rem;
        cursor: pointer;
        line-height: 1;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
    }

    .modal-close:hover {
        color: white;
    }

    .modal-body {
        padding: 20px;
        overflow-y: auto;
        flex: 1;
    }

    .modal-footer {
        padding: 12px 20px;
        background: #f8fafc;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        align-items: center;
    }

    /* Product summary inside Detail modal */
    .modal-product-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 16px;
    }

    .summary-tile {
        flex: 1;
        min-width: 120px;
    }

    .summary-tile .label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--text-secondary);
        display: block;
        margin-bottom: 2px;
    }

    .summary-tile .value {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Table inside Modal */
    .modal-table-container {
        border: 1px solid var(--border);
        border-radius: 8px;
        overflow: hidden;
    }

    #modalTable th {
        background: #f1f5f9;
        font-size: 0.72rem;
        font-weight: 600;
        color: #475569;
        padding: 8px 12px;
        border-bottom: 1px solid var(--border);
        text-align: left;
    }

    #modalTable td {
        padding: 8px 12px;
        font-size: 0.78rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
    }

    #modalTable tbody tr:last-child td {
        border-bottom: none;
    }

    .team-badge {
        font-size: 0.68rem;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
    }
    .team-badge.S { background: #d1fae5; color: #065f46; }
    .team-badge.R1 { background: #dbeafe; color: #1e40af; }
    .team-badge.R2 { background: #f3e8ff; color: #6b21a8; }
    .team-badge.R3 { background: #ffe4e6; color: #9f1239; }

    /* Button Edit & Delete inside Modal Table */
    .btn-edit-row {
        background: transparent;
        border: none;
        color: var(--primary);
        font-weight: 600;
        cursor: pointer;
        margin-right: 12px;
        font-size: 0.75rem;
    }

    .btn-edit-row:hover {
        text-decoration: underline;
    }

    .btn-delete-row {
        background: transparent;
        border: none;
        color: var(--danger);
        font-weight: 600;
        cursor: pointer;
        font-size: 0.75rem;
    }

    .btn-delete-row:hover {
        text-decoration: underline;
    }

    .edit-input {
        width: 100%;
        padding: 4px 8px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 0.75rem;
    }

    .btn-save-row {
        background: transparent;
        border: none;
        color: var(--success);
        font-weight: 700;
        cursor: pointer;
        margin-right: 12px;
        font-size: 0.75rem;
    }

    .btn-cancel-row {
        background: transparent;
        border: none;
        color: var(--secondary);
        font-weight: 700;
        cursor: pointer;
        font-size: 0.75rem;
    }

    /* ANALYSIS MODAL */
    .analysis-header-icon {
        background: rgba(255,255,255,0.15);
        border-radius: 8px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .analysis-stats-bar {
        background: white;
        border-bottom: 1px solid var(--border);
        padding: 10px 20px;
    }

    .analysis-stats-bar .indicator {
        font-size: 0.75rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .analysis-stats-bar .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    .analysis-stats-bar .dot.violet { background: #7c3aed; }
    .analysis-stats-bar .dot.red { background: #ef4444; }
    .analysis-stats-bar .dot.amber { background: #f59e0b; }
    .analysis-stats-bar .dot.gray { background: #94a3b8; }
    .analysis-stats-bar .dot.pulse { animation: pulse-dot 2s infinite; }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }

    .text-violet { color: #7c3aed; }
    .divider-v {
        width: 1px;
        height: 14px;
        background: var(--border);
    }

    .badge-mini {
        font-size: 8px;
        font-weight: 700;
        padding: 1px 4px;
        border-radius: 3px;
        color: white;
    }
    .badge-mini.match-code { background: #2563eb; }
    .badge-mini.match-name { background: #7c3aed; }

    .mini-select {
        padding: 3px 6px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 0.7rem;
        background: white;
    }

    .analysis-legend-bar {
        background: #eff6ff;
        border-bottom: 1px solid #dbeafe;
        padding: 6px 20px;
        font-size: 0.7rem;
        color: #1e40af;
    }

    /* Cards in analysis modal */
    .analysis-cards-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .analysis-card {
        background: white;
        border-radius: 10px;
        border: 1px solid var(--border);
        border-left: 4px solid transparent;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(0,0,0,0.01);
        transition: all 0.2s;
    }

    .analysis-card:hover {
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    .analysis-card.confidence-tinggi { border-left-color: #ef4444; }
    .analysis-card.confidence-sedang { border-left-color: #f59e0b; }
    .analysis-card.confidence-rendah { border-left-color: #94a3b8; }

    .analysis-card-header {
        padding: 10px 16px;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        background: #ffffff;
    }

    .analysis-card-body {
        border-top: 1px solid #f1f5f9;
        display: none;
    }

    .analysis-card-body.active {
        display: block;
    }

    .confidence-badge {
        font-size: 0.65rem;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
        display: inline-block;
    }
    .confidence-badge.tinggi { background: #fee2e2; color: #991b1b; }
    .confidence-badge.sedang { background: #fef3c7; color: #92400e; }
    .confidence-badge.rendah { background: #f1f5f9; color: #475569; }

    .toggle-matches-btn {
        background: transparent;
        border: none;
        color: var(--secondary);
        cursor: pointer;
        font-size: 0.85rem;
        padding: 4px;
        transition: transform 0.2s;
    }
    .toggle-matches-btn.rotated {
        transform: rotate(180deg);
    }

    /* Score Bar styling */
    .score-container {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .score-bar {
        background: #e2e8f0;
        height: 5px;
        border-radius: 3px;
        width: 50px;
        overflow: hidden;
    }
    .score-fill {
        height: 100%;
        border-radius: 3px;
    }

    /* DataTables controls reset for dashboard layout */
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 5px 10px;
        font-size: 0.78rem;
        outline: none;
        margin-left: 6px;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: var(--primary);
    }
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 0.78rem;
    }
    .dataTables_wrapper .dataTables_info {
        font-size: 0.75rem;
        color: var(--text-secondary);
        padding-top: 12px;
    }
    .dataTables_wrapper .dataTables_paginate {
        padding-top: 10px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        font-size: 0.75rem !important;
        padding: 4px 10px !important;
        border-radius: 6px !important;
        margin: 0 2px !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--primary) !important;
        color: white !important;
        border: 1px solid var(--primary) !important;
    }
    
    /* Excel Button styling */
    .dt-buttons .btn-export-excel {
        background: #10b981 !important;
        color: white !important;
        border: none !important;
        border-radius: 6px !important;
        padding: 5px 12px !important;
        font-size: 0.78rem !important;
        font-weight: 600 !important;
        margin-left: 12px !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
    }
    .dt-buttons .btn-export-excel:hover {
        background: #059669 !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script>
    // Data JSON untuk Analysis Modal
    const analysisData = @json($comprehensiveAnalysis);

    $(document).ready(function() {
        const sessionId = '{{ $activeSessionId }}';
        let currentUom = 1;
        let needsMainPageReload = false;

        // CSRF Token setup for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Initialize DataTable
        let table = $('#singleTable').DataTable({
            dom: '<"top"lBf>rt<"bottom"ip><"clear">',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Export Excel',
                    className: 'btn-export-excel',
                    title: 'Recount_Data_' + new Date().toISOString().split('T')[0],
                    exportOptions: {
                        columns: ':visible:not(:first-child):not(:last-child)' // Exclude Checkbox & Aksi
                    }
                }
            ],
            pageLength: 25,
            scrollX: false,
            autoWidth: false,
            language: {
                search: "Cari:",
                searchPlaceholder: "Ketik kata kunci...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(difilter dari _MAX_ total)",
                zeroRecords: "Tidak ada data yang cocok ditemukan",
                paginate: {
                    first: "«",
                    previous: "‹",
                    next: "›",
                    last: "»"
                }
            },
            order: [[1, 'asc']],
            drawCallback: function() {
                updateSelectedCount();
                attachCheckboxEvents();
            }
        });

        // Column adjustment on sidebar toggle
        const toggleBtn = document.getElementById('toggleSidebarBtn');
        const sidebar = document.getElementById('sidebar');
        
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                // Adjust columns continuously during the 300ms sidebar transition for visual smoothness
                let count = 0;
                const adjustInterval = setInterval(function() {
                    table.columns.adjust().draw(false);
                    count++;
                    if (count >= 12) { // 12 * 30ms = 360ms
                        clearInterval(adjustInterval);
                    }
                }, 30);
            });
        }

        if (sidebar) {
            sidebar.addEventListener('transitionend', function(e) {
                if (e.propertyName === 'width') {
                    table.columns.adjust().draw(false);
                }
            });
        }

        // Custom filters implementation
        $('#principalFilter').on('change', function() {
            const val = $(this).val();
            // Match exact value or empty
            table.column(3).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', val ? true : false, false).draw();
        });

        $('#statusFilter').on('change', function() {
            const val = $(this).val();
            table.column(23).search(val ? $.fn.dataTable.util.escapeRegex(val) : '', val ? true : false, false).draw();
        });

        $('#analisaFilter').on('change', function() {
            const val = $(this).val();
            $.fn.dataTable.ext.search.pop(); // Clear previous search filters

            if (val === 'has_analisa') {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    const row = table.row(dataIndex).node();
                    const trigger = $(row).find('.analisa-trigger');
                    return trigger.length > 0;
                });
            } else if (val === 'possible_typo') {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    const row = table.row(dataIndex).node();
                    return $(row).attr('data-analisa-type') === 'possible_typo';
                });
            } else if (val === 'anomaly') {
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    const row = table.row(dataIndex).node();
                    return $(row).attr('data-analisa-type') === 'anomaly';
                });
            }

            table.draw();
        });

        // Detail Modal Operations
        $('#singleTable').on('click', '.btn-detail', function() {
            const productId = $(this).data('product');
            needsMainPageReload = false;
            loadModalHistory(productId);
            $('#detailModal').addClass('active');
        });

        async function loadModalHistory(productId) {
            $('#modalTableBody').html('<tr><td colspan="8" style="text-align: center; padding: 20px; color: var(--text-secondary);"><i class="fas fa-spinner fa-spin"></i> Loading history...</td></tr>');
            
            try {
                const response = await fetch('{{ route("double_mode.recount.detail") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify({
                        session_id: sessionId,
                        id_product: productId
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    currentUom = parseInt(data.product.uom, 10) || parseInt(data.product.carton_size, 10) || 1;
                    
                    $('#modalSubTitle').html(`Product ID: <strong style="color: white; font-family: monospace;">${data.product.id_product}</strong>`);
                    $('#mPrincipal').text(data.product.principal);
                    $('#mProdName').text(data.product.product_name).attr('title', data.product.product_name);
                    $('#mPackname').text(data.product.packname);
                    $('#mUom').text(data.product.uom);
                    
                    let rows = '';
                    if (data.history.length === 0) {
                        rows = '<tr><td colspan="8" style="text-align: center; padding: 20px; color: var(--text-secondary);">Belum ada perhitungan untuk produk ini</td></tr>';
                    } else {
                        data.history.forEach(h => {
                            rows += `
                                <tr data-detail-id="${h.detail_id}" data-count-id="${h.count_id}" data-product-id="${h.id_product}" data-qty-karton="${h.qty_karton}" data-qty-pcs="${h.qty_pcs}" data-final-qty="${h.final_qty}">
                                    <td class="col-product-id font-mono">${h.id_product}</td>
                                    <td class="col-location font-mono" style="color: #b45309; font-weight: 600;">${h.location}</td>
                                    <td class="col-team"><span class="team-badge ${h.team}">${h.team}</span></td>
                                    <td class="col-qty-karton" style="text-align: right;">${formatNum(h.qty_karton)}</td>
                                    <td class="col-qty-pcs" style="text-align: right;">${formatNum(h.qty_pcs)}</td>
                                    <td class="col-final-qty font-semibold" style="text-align: right;">${formatNum(h.final_qty)}</td>
                                    <td class="col-timestamp" style="color: var(--text-secondary);">${h.timestamp}</td>
                                    <td class="col-action" style="text-align: center;">
                                        <button type="button" class="btn-edit-row">Edit</button>
                                        <button type="button" class="btn-delete-row">Hapus</button>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    $('#modalTableBody').html(rows);
                } else {
                    alert('Error: ' + (data.message || 'Gagal mengambil detail'));
                    $('#detailModal').removeClass('active');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan koneksi');
                $('#detailModal').removeClass('active');
            }
        }

        function formatNum(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        // Inline Editing in History Modal
        $('#modalTable').on('click', '.btn-edit-row', function() {
            const tr = $(this).closest('tr');
            const detailId = tr.attr('data-detail-id');
            const productId = tr.attr('data-product-id');
            const location = tr.find('.col-location').text();
            const team = tr.find('.col-team').html();
            const qtyKarton = tr.attr('data-qty-karton');
            const qtyPcs = tr.attr('data-qty-pcs');
            const finalQty = tr.attr('data-final-qty');
            const timestamp = tr.find('.col-timestamp').text();

            tr.html(`
                <td>
                    <input type="text" class="edit-id-product edit-input" value="${productId}">
                    <input type="hidden" class="edit-detail-id" value="${detailId}">
                </td>
                <td class="col-location font-mono" style="color: #b45309; font-weight: 600;">${location}</td>
                <td>${team}</td>
                <td><input type="number" class="edit-qty-karton edit-input" style="text-align: right;" value="${qtyKarton}"></td>
                <td><input type="number" class="edit-qty-pcs edit-input" style="text-align: right;" value="${qtyPcs}"></td>
                <td><input type="number" class="edit-final-qty edit-input" style="text-align: right; font-weight: 600;" value="${finalQty}"></td>
                <td style="color: var(--text-secondary); font-size: 11px;">${timestamp}</td>
                <td style="text-align: center;">
                    <button type="button" class="btn-save-row">Simpan</button>
                    <button type="button" class="btn-cancel-row">Batal</button>
                </td>
            `);
            tr.attr('data-original-product-id', productId);
        });

        $('#modalTable').on('input', '.edit-qty-karton, .edit-qty-pcs', function() {
            const tr = $(this).closest('tr');
            const karton = parseInt(tr.find('.edit-qty-karton').val()) || 0;
            const pcs = parseInt(tr.find('.edit-qty-pcs').val()) || 0;
            tr.find('.edit-final-qty').val(karton * currentUom + pcs);
        });

        $('#modalTable').on('click', '.btn-cancel-row', function() {
            const currentModalProdId = $('#modalSubTitle').text().replace('Product ID: ', '').trim();
            loadModalHistory(currentModalProdId);
        });

        $('#modalTable').on('click', '.btn-save-row', async function() {
            const tr = $(this).closest('tr');
            const detailId = tr.find('.edit-detail-id').val();
            const oldProductId = tr.attr('data-original-product-id');
            const newProductId = tr.find('.edit-id-product').val().trim();
            const qtyKarton = parseInt(tr.find('.edit-qty-karton').val()) || 0;
            const qtyPcs = parseInt(tr.find('.edit-qty-pcs').val()) || 0;
            const finalQty = parseInt(tr.find('.edit-final-qty').val()) || 0;

            if (!newProductId) {
                alert('Product ID tidak boleh kosong');
                return;
            }

            const $btn = $(this);
            $btn.prop('disabled', true).text('...');

            try {
                const response = await fetch('{{ route("double_mode.recount.edit") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify({
                        session_id: sessionId,
                        detail_id: detailId,
                        old_id_product: oldProductId,
                        new_id_product: newProductId,
                        qty_karton: qtyKarton,
                        qty_pcs: qtyPcs,
                        final_qty: finalQty
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    const currentModalProdId = $('#modalSubTitle').text().replace('Product ID: ', '').trim();
                    loadModalHistory(currentModalProdId);
                    needsMainPageReload = true;
                } else {
                    alert('Error: ' + data.message);
                    $btn.prop('disabled', false).text('Simpan');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan koneksi');
                $btn.prop('disabled', false).text('Simpan');
            }
        });

        $('#modalTable').on('click', '.btn-delete-row', async function() {
            const tr = $(this).closest('tr');
            const detailId = tr.attr('data-detail-id');
            const productId = tr.attr('data-product-id');

            if (!confirm(`Hapus record perhitungan produk "${productId}"?`)) return;

            const $btn = $(this);
            $btn.prop('disabled', true).text('...');

            try {
                const response = await fetch('{{ route("double_mode.recount.delete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify({
                        session_id: sessionId,
                        detail_id: detailId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    const currentModalProdId = $('#modalSubTitle').text().replace('Product ID: ', '').trim();
                    loadModalHistory(currentModalProdId);
                    needsMainPageReload = true;
                } else {
                    alert('Error: ' + data.message);
                    $btn.prop('disabled', false).text('Hapus');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan koneksi');
                $btn.prop('disabled', false).text('Hapus');
            }
        });

        // Close Detail Modal
        $('#closeModalBtn, #closeModalBtn2').on('click', function() {
            $('#detailModal').removeClass('active');
            if (needsMainPageReload) location.reload();
        });

        $('#detailModal').on('click', function(e) {
            if (e.target === this) {
                $(this).removeClass('active');
                if (needsMainPageReload) location.reload();
            }
        });

        // Recount Assignment Operations
        function attachCheckboxEvents() {
            $('.product-checkbox').off('change').on('change', function() {
                updateSelectedCount();
            });
        }

        function updateSelectedCount() {
            const count = $('.product-checkbox:checked').length;
            const userSelected = $('#userSelect').val();
            $('#selectedCount').text(count);
            $('#assignBtn').prop('disabled', count === 0 || !userSelected);
        }

        function getSelectedProducts() {
            const products = [];
            $('.product-checkbox:checked').each(function() {
                products.push($(this).val());
            });
            return products;
        }

        $('#userSelect').on('change', updateSelectedCount);

        $('#assignBtn').on('click', async function() {
            const selectedProducts = getSelectedProducts();
            const assignedTo = $('#userSelect').val();

            if (selectedProducts.length === 0 || !assignedTo) {
                alert('Pilih produk dan user terlebih dahulu');
                return;
            }

            if (!confirm(`Assign ${selectedProducts.length} produk ke user terpilih?`)) return;

            const $btn = $(this);
            $btn.prop('disabled', true).find('#assignBtnLoading').removeClass('hidden');
            $btn.find('#assignBtnText').text('Loading');

            try {
                const response = await fetch('{{ route("double_mode.recount.assign") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify({
                        session_id: sessionId,
                        assigned_to: assignedTo,
                        selected_products: selectedProducts,
                        assigned_by: 1
                    })
                });
                
                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan koneksi');
            } finally {
                $btn.prop('disabled', false).find('#assignBtnLoading').addClass('hidden');
                $btn.find('#assignBtnText').text('Assign');
            }
        });

        // Initialize checkbox actions
        attachCheckboxEvents();
        updateSelectedCount();

        // ============================================
        // ANALYSIS MODAL LOGIC & RENDERING
        // ============================================
        function renderAnalysisCards(filterConfidence, filterType) {
            const container = $('#analysisContent');
            container.empty();

            const filtered = analysisData.filter(item => {
                if (filterConfidence && item.best_confidence !== filterConfidence) return false;
                if (filterType && item.best_match_type !== filterType) return false;
                return true;
            });

            if (filtered.length === 0) {
                container.html(`
                    <div style="text-align: center; padding: 48px; color: var(--text-secondary);">
                        <i class="fas fa-search" style="font-size: 2.5rem; margin-bottom: 12px; color: #cbd5e1;"></i>
                        <p style="font-weight: 600; font-size: 0.9rem; margin: 0 0 4px 0;">Tidak ada hasil analisa cocok</p>
                        <p style="font-size: 0.78rem; margin: 0;">Coba sesuaikan pilihan filter untuk menampilkan data lainnya</p>
                    </div>
                `);
                return;
            }

            filtered.forEach((item, idx) => {
                const confidenceClass = item.best_confidence.toLowerCase();
                const confidenceEmoji = item.best_confidence === 'Tinggi' ? '🔴' : (item.best_confidence === 'Sedang' ? '🟡' : '⚪');
                
                let matchesHtml = '';
                item.matches.forEach((m, mIdx) => {
                    const matchBadgeClass = `match-${m.match_type}`;
                    const matchLabel = m.match_type === 'both' ? 'KODE+NAMA' : (m.match_type === 'code' ? 'KODE' : 'NAMA');
                    
                    const nameBarColor = m.name_score >= 80 ? 'var(--success)' : (m.name_score >= 60 ? 'var(--warning)' : 'var(--danger)');
                    const codeBarColor = m.code_score >= 70 ? 'var(--success)' : (m.code_score >= 40 ? 'var(--warning)' : 'var(--danger)');
                    const combinedBarColor = m.combined_score >= 70 ? 'var(--success)' : (m.combined_score >= 50 ? 'var(--warning)' : 'var(--danger)');
                    
                    matchesHtml += `
                        <tr style="background: ${mIdx % 2 === 0 ? 'white' : '#f8fafc'}; border-bottom: 1px solid #f1f5f9;">
                            <td style="text-align: center; color: var(--text-secondary); font-size: 0.7rem; padding: 6px 10px;">${mIdx + 1}</td>
                            <td style="padding: 6px 10px;">
                                <code class="font-mono" style="font-size: 0.75rem; background: #f1f5f9; padding: 2px 4px; border-radius: 4px; color: #1e293b;">${escHtml(m.id_product)}</code>
                                <div style="font-size: 10px; color: var(--text-secondary); margin-top: 2px; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escHtml(m.product_name)}">${escHtml(m.product_name)}</div>
                            </td>
                            <td style="font-size: 0.72rem; padding: 6px 10px; color: #475569;">${escHtml(m.packname)}</td>
                            <td class="num-col" style="text-align: right; font-size: 0.72rem; padding: 6px 10px; font-weight: 600; color: #047857;">${formatNum(m.stock_system)}</td>
                            <td style="text-align: center; padding: 6px 10px;">
                                <span class="badge-mini ${matchBadgeClass}">${matchLabel}</span>
                            </td>
                            <td style="padding: 6px 10px;">
                                <div class="score-container">
                                    <div class="score-bar"><div class="score-fill" style="width:${m.name_score}%; background:${nameBarColor}"></div></div>
                                    <span style="font-size: 10px; font-weight: 600; color:${nameBarColor}; width: 32px; text-align: right;">${m.name_score}%</span>
                                </div>
                            </td>
                            <td style="padding: 6px 10px;">
                                <div class="score-container">
                                    <div class="score-bar"><div class="score-fill" style="width:${m.code_score}%; background:${codeBarColor}"></div></div>
                                    <span style="font-size: 10px; font-weight: 600; color:${codeBarColor}; width: 32px; text-align: right;">${m.code_score}%</span>
                                </div>
                            </td>
                            <td style="padding: 6px 10px;">
                                <div class="score-container">
                                    <div class="score-bar"><div class="score-fill" style="width:${m.combined_score}%; background:${combinedBarColor}"></div></div>
                                    <span style="font-size: 10px; font-weight: 700; color:${combinedBarColor}; width: 32px; text-align: right;">${m.combined_score}%</span>
                                </div>
                            </td>
                        </tr>
                    `;
                });

                const cardHtml = `
                    <div class="analysis-card confidence-${confidenceClass}">
                        <div class="analysis-card-header">
                            <div style="display: flex; align-items: flex-start; gap: 10px; flex: 1;">
                                <div style="background: #e2e8f0; border-radius: 6px; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; color: #475569;">
                                    ${idx + 1}
                                </div>
                                <div style="min-width: 0; flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <code class="font-mono text-orange-bold" style="font-size: 0.8rem; background: #fff7ed; padding: 2px 6px; border-radius: 4px; border: 1px solid #ffedd5;">${escHtml(item.id_product)}</code>
                                        <span style="font-size: 11px; color: var(--text-secondary);">Stok: <strong style="color: #dc2626;">0</strong></span>
                                        <span style="font-size: 11px; color: var(--text-secondary);">|</span>
                                        <span style="font-size: 11px; color: var(--text-secondary);">Hitung Team S: <strong style="color: #2563eb;">${formatNum(item.s_qty)} pcs</strong> (${formatNum(item.s_karton)} krt + ${formatNum(item.s_pcs)} pcs)</span>
                                    </div>
                                    <div style="font-size: 0.8rem; font-weight: 500; color: var(--dark); margin-top: 4px;">
                                        ${escHtml(item.product_name)} <span style="font-size: 11px; color: var(--text-secondary); font-weight: 400;">(${escHtml(item.packname)})</span>
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span class="confidence-badge ${confidenceClass}">
                                    ${confidenceEmoji} ${item.best_confidence}
                                </span>
                                <span style="font-size: 11px; color: var(--text-secondary); font-weight: 500;">${item.match_count} opsi mirip</span>
                                <button class="toggle-matches-btn" data-target="matches-${idx}">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Collapsible Table -->
                        <div id="matches-${idx}" class="analysis-card-body">
                            <div style="padding: 6px 16px; background: #fafafa; font-size: 0.72rem; font-weight: 600; color: #475569; border-bottom: 1px solid #f1f5f9;">
                                💡 Alternatif produk berstok dengan nama atau kode mendekati:
                            </div>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 10px; color: #64748b; text-transform: uppercase;">
                                            <th style="padding: 6px 10px; width: 40px; text-align: center;">#</th>
                                            <th style="padding: 6px 10px; text-align: left;">Kode & Nama Produk</th>
                                            <th style="padding: 6px 10px; text-align: left;">Pack</th>
                                            <th style="padding: 6px 10px; text-align: right;">Stok</th>
                                            <th style="padding: 6px 10px; text-align: center;">Tipe</th>
                                            <th style="padding: 6px 10px; text-align: left; width: 100px;">Nama</th>
                                            <th style="padding: 6px 10px; text-align: left; width: 100px;">Kode</th>
                                            <th style="padding: 6px 10px; text-align: left; width: 100px;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>${matchesHtml}</tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
                
                container.append(cardHtml);
            });
        }

        function escHtml(str) {
            if (!str) return '';
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Open and close analysis modal
        $('#openAnalysisModal').on('click', function() {
            $('#analysisModal').addClass('active');
            renderAnalysisCards('', '');
        });

        $('#closeAnalysisBtn, #closeAnalysisBtn2').on('click', function() {
            $('#analysisModal').removeClass('active');
        });

        $('#analysisModal').on('click', function(e) {
            if (e.target === this) $(this).removeClass('active');
        });

        // Filter events inside analysis modal
        $('#analysisConfidenceFilter, #analysisTypeFilter').on('change', function() {
            const conf = $('#analysisConfidenceFilter').val();
            const type = $('#analysisTypeFilter').val();
            renderAnalysisCards(conf, type);
        });

        // Collapsible trigger for recommendations
        $(document).on('click', '.toggle-matches-btn', function() {
            const targetId = $(this).data('target');
            const body = $(`#${targetId}`);
            const btn = $(this);
            
            body.slideToggle(200, function() {
                body.toggleClass('active');
            });
            btn.toggleClass('rotated');
        });
    });
</script>
@endpush
