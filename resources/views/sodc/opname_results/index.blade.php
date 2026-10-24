@extends('layouts.app')
@section('title', 'Rekonsiliasi Hasil Opname')
@section('page_title', 'Hasil Rekonsiliasi Opname')

@section('page_actions')
@if(isset($activeSessions) && $activeSessions->count() > 0)
<form action="{{ route('sodc.results.index') }}" method="GET" style="display: inline-flex; align-items: center; gap: 10px;">
    <label style="color: #64748b; font-weight: 600; font-size: 0.85rem;">Pilih Sesi:</label>
    <select name="session_id" onchange="this.form.submit()" style="padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; font-size: 0.85rem; font-weight: 600; color: #334155;">
        @foreach($activeSessions as $s)
            <option value="{{ $s->id }}" {{ (isset($activeSessionId) && $activeSessionId == $s->id) ? 'selected' : '' }}>
                {{ $s->session_code }} ({{ $s->mode }})
            </option>
        @endforeach
    </select>
</form>
@endif
@endsection
@section('content')

@if(isset($error))
    <div class="content-panel" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 48px 20px; margin-top: 10px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <x-empty-state 
            title="Tidak Ada Sesi Opname yang Aktif"
            description="{{ $error }} Silakan pilih atau aktifkan sesi opname terlebih dahulu untuk menampilkan hasil rekonsiliasi."
            :actionUrl="route('sodc.sessions.index')"
            actionText="Kelola Sesi Opname"
            actionIcon="fas fa-calendar-alt"
        />
    </div>
@else
    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden;">
        <div style="padding: 16px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; flex-wrap: wrap; gap: 10px;">
            <h3 style="margin: 0; color: #0f172a; font-size: 1.1rem; min-width: 150px;">Data Rekonsiliasi</h3>
            
            <div style="display: flex; gap: 10px; align-items: center; flex-grow: 1; justify-content: flex-end;">
                <form method="GET" action="{{ route('sodc.results.index') }}" style="display: flex; gap: 10px; flex-grow: 1; max-width: 500px;">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari SKU, Nama Produk, atau Bin..." style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; outline: none; flex-grow: 1;">
                    <select name="status" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; outline: none;">
                        <option value="">Semua Status</option>
                        <option value="MATCH" {{ request('status') == 'MATCH' ? 'selected' : '' }}>MATCH</option>
                        <option value="RECOUNT_1" {{ request('status') == 'RECOUNT_1' ? 'selected' : '' }}>Butuh RECOUNT 1</option>
                        <option value="RECOUNT_2" {{ request('status') == 'RECOUNT_2' ? 'selected' : '' }}>Butuh RECOUNT 2</option>
                        <option value="FINAL" {{ request('status') == 'FINAL' ? 'selected' : '' }}>FINAL</option>
                        <option value="UNCOUNTED" {{ request('status') == 'UNCOUNTED' ? 'selected' : '' }}>UNCOUNTED</option>
                    </select>
                    <button type="submit" style="background: #0f172a; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer;"><i class="fas fa-search"></i></button>
                </form>
                
                @if($isAllFinal ?? false)
                <button type="button" onclick="openAssignR3Modal()" style="background: #ef4444; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; white-space: nowrap;">
                    <i class="fas fa-user-check"></i> Assign R3
                </button>
                @endif
                
                <div style="position: relative; display: inline-block;">
                    <button type="button" onclick="toggleExportDropdown()" style="background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-download"></i> Download Report <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                    </button>
                    <div id="exportDropdown" style="display: none; position: absolute; right: 0; background-color: white; min-width: 170px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 10; border-radius: 8px; margin-top: 5px; overflow: hidden; border: 1px solid #e2e8f0;">
                        <a href="{{ route('sodc.results.export_simple') }}" style="color: #334155; padding: 12px 16px; text-decoration: none; display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 600; border-bottom: 1px solid #f1f5f9; background: #fff; transition: 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                            <i class="fas fa-file-excel" style="color: #10b981; font-size: 1.1rem;"></i> Simple Export
                        </a>
                        <a href="{{ route('sodc.results.export_complex') }}" style="color: #334155; padding: 12px 16px; text-decoration: none; display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 600; background: #fff; transition: 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                            <i class="fas fa-file-csv" style="color: #0ea5e9; font-size: 1.1rem;"></i> Complex Export
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form id="bulkR3Form" method="POST" action="{{ route('sodc.results.assign_r3') }}">
            @csrf
        <div style="overflow-x: auto;">
            <table class="premium-table" style="width: 100%; border-collapse: collapse; min-width: 1000px;">
                <thead>
                    <tr style="background: #f1f5f9; text-align: left; font-size: 0.8rem; color: #475569;">
                        <th style="padding: 12px 16px; width: 40px;"><input type="checkbox" id="selectAllCheckbox" onclick="toggleAllCheckboxes(this)"></th>
                        <th style="padding: 12px 16px;">Bin / SKU</th>
                        <th style="padding: 12px 16px; text-align: right;">WMS Qty</th>
                        <th style="padding: 12px 16px; text-align: right;">Team A</th>
                        <th style="padding: 12px 16px; text-align: right;">Team B</th>
                        <th style="padding: 12px 16px; text-align: right;">Recount 1</th>
                        <th style="padding: 12px 16px; text-align: right;">Recount 2</th>
                        @if($hasR3 ?? false)
                        <th style="padding: 12px 16px; text-align: right;">Recount 3</th>
                        @endif
                        <th style="padding: 12px 16px; text-align: right;">Final Qty</th>
                        <th style="padding: 12px 16px; text-align: center;">Status</th>
                        <th style="padding: 12px 16px; text-align: center; width: 60px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $res)
                        @php
                            $isMatch = $res->result_status == 'MATCH';
                            $isFinal = in_array($res->result_status, ['FINAL', 'APPROVED']);
                            $isR1 = $res->result_status == 'RECOUNT' && is_null($res->recount1_qty);
                            $isR2 = $res->result_status == 'RECOUNT' && !is_null($res->recount1_qty) && is_null($res->recount2_qty);
                            
                            $badgeColor = '#94a3b8'; // default UNCOUNTED
                            $badgeText = $res->result_status;
                            
                            if ($isMatch) { $badgeColor = '#10b981'; } // green
                            if ($isFinal) { $badgeColor = '#3b82f6'; } // blue
                            if ($isR1) { $badgeColor = '#f59e0b'; $badgeText = 'RECOUNT 1'; } // yellow
                            if ($isR2) { $badgeColor = '#ef4444'; $badgeText = 'RECOUNT 2'; } // red
                            if (str_starts_with($res->result_status, 'WAITING')) { $badgeColor = '#8b5cf6'; } // purple
                            
                            $isNyasar = (float)$res->system_qty == 0;
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; {{ $isNyasar ? 'background-color: #fef9c3;' : '' }}">
                            <td style="padding: 12px 16px; text-align: center;">
                                <input type="checkbox" name="selected_results[]" value="{{ $res->id }}" class="result-checkbox">
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 700; color: #0ea5e9;">{{ $res->referenceDetail->bin_code ?? '-' }}</div>
                                <div style="font-family: monospace; font-weight: 600;">
                                    {{ $res->referenceDetail->sku_code ?? '-' }}
                                    @if($isNyasar)
                                        <span title="Barang ini tidak ada di data awal WMS untuk Bin ini" style="font-size: 0.65rem; background: #64748b; color: white; padding: 2px 6px; border-radius: 4px; margin-left: 6px; font-family: sans-serif; font-weight: 800;">NON-WMS</span>
                                    @endif
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b;">
                                    <strong style="color: #334155;">{{ Str::limit($res->referenceDetail->product->sku_name ?? 'Unknown Product', 40) }}</strong><br>
                                    UOM: {{ $res->referenceDetail->product->uom ?? '-' }} | Pack: {{ $res->referenceDetail->product->packname ?? '-' }}
                                </div>
                            </td>
                            <td style="padding: 12px 16px; text-align: right; font-weight: 600;">{{ (float)$res->system_qty }}</td>
                            <td style="padding: 12px 16px; text-align: right;">{{ is_null($res->team_a_qty) ? '-' : (float)$res->team_a_qty }}</td>
                            <td style="padding: 12px 16px; text-align: right;">{{ is_null($res->team_b_qty) ? '-' : (float)$res->team_b_qty }}</td>
                            <td style="padding: 12px 16px; text-align: right; font-weight: bold; color: #d97706;">
                                {{ is_null($res->recount1_qty) ? '-' : (float)$res->recount1_qty }}
                            </td>
                            <td style="padding: 12px 16px; text-align: right; font-weight: bold; color: #b91c1c;">
                                {{ is_null($res->recount2_qty) ? '-' : (float)$res->recount2_qty }}
                            </td>
                            @if($hasR3 ?? false)
                            <td style="padding: 12px 16px; text-align: right; font-weight: bold; color: #7e22ce;">
                                {{ is_null($res->recount3_qty ?? null) ? '-' : (float)$res->recount3_qty }}
                            </td>
                            @endif
                            <td style="padding: 12px 16px; text-align: right; font-weight: 800; font-size: 0.95rem; color: #0f172a;">
                                {{ is_null($res->final_qty) ? '-' : (float)$res->final_qty }}
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <span style="background: {{ $badgeColor }}; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase;">
                                    {{ $badgeText }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <button type="button" 
                                        onclick="openAuditDetailModal({{ $res->id }})" 
                                        title="Lihat Jejak Audit & Detail Input"
                                        style="background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; width: 34px; height: 34px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);"
                                        onmouseover="this.style.background='#0ea5e9'; this.style.color='#fff'; this.style.transform='scale(1.08)';"
                                        onmouseout="this.style.background='#f0f9ff'; this.style.color='#0284c7'; this.style.transform='scale(1)';">
                                    <i class="fas fa-eye" style="font-size: 0.95rem;"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                    <x-empty-state 
                        colspan="{{ ($hasR3 ?? false) ? 11 : 10 }}"
                        title="Tidak Ada Data Rekonsiliasi"
                        description="Tidak ditemukan data rekonsiliasi yang cocok dengan kriteria pencarian atau status yang dipilih."
                        :actionUrl="request()->hasAny(['search', 'status']) ? route('sodc.results.index') : null"
                        actionText="Reset Pencarian"
                        actionIcon="fas fa-undo"
                    />
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 16px; border-top: 1px solid #e2e8f0; background: #f8fafc;" class="pagination-wrapper">
            {{ $results->links() }}
        </div>
        </form>
    </div>
@endif

<!-- Recount Modal -->
<div id="recountModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; width: 400px; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="background: #0f172a; padding: 16px; color: white; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.1rem;">Input Hitung Ulang</h3>
            <button type="button" onclick="closeRecountModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; padding: 0;">&times;</button>
        </div>
        <form method="POST" action="{{ route('sodc.results.input_recount') }}">
            @csrf
            <div style="padding: 20px;">
                <input type="hidden" name="result_id" id="modalResultId">
                <input type="hidden" name="level" id="modalLevel">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Bin / SKU</label>
                    <div id="modalProductInfo" style="font-weight: 700; color: #0f172a; font-size: 1rem;">-</div>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Hasil Qty (Recount <span id="modalLevelText"></span>)</label>
                    <input type="number" step="0.01" name="recount_qty" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 1rem; box-sizing: border-box;">
                </div>
            </div>
            <div style="padding: 16px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: right;">
                <button type="button" onclick="closeRecountModal()" style="padding: 8px 16px; border: none; background: #e2e8f0; color: #475569; border-radius: 6px; font-weight: 600; cursor: pointer; margin-right: 8px;">Batal</button>
                <button type="submit" style="padding: 8px 16px; border: none; background: #0ea5e9; color: white; border-radius: 6px; font-weight: 600; cursor: pointer;">Simpan Recount</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
    .pagination-wrapper svg { width: 1.25rem; height: 1.25rem; }
    .pagination-wrapper nav > div { margin-top: 10px; }
    .pagination-wrapper p { font-size: 0.875rem; color: #64748b; }
</style>
<script>
    function openRecountModal(resultId, level, binCode, skuCode) {
        document.getElementById('modalResultId').value = resultId;
        document.getElementById('modalLevel').value = level;
        document.getElementById('modalLevelText').innerText = level;
        document.getElementById('modalProductInfo').innerText = binCode + ' - ' + skuCode;
        
        const modal = document.getElementById('recountModal');
        modal.style.display = 'flex';
    }

    function closeRecountModal() {
        document.getElementById('recountModal').style.display = 'none';
    }
    
    function toggleAllCheckboxes(source) {
        var checkboxes = document.querySelectorAll('.result-checkbox');
        for(var i=0, n=checkboxes.length; i<n; i++) {
            checkboxes[i].checked = source.checked;
        }
    }
    
    function openAssignR3Modal() {
        var selected = document.querySelectorAll('.result-checkbox:checked');
        if (selected.length === 0) {
            alert('Pilih setidaknya 1 item (Bin/SKU) untuk di-assign ke R3.');
            return;
        }
        document.getElementById('assignR3Modal').style.display = 'flex';
    }
    
    function closeAssignR3Modal() {
        document.getElementById('assignR3Modal').style.display = 'none';
    }
    function toggleExportDropdown() {
        var dropdown = document.getElementById('exportDropdown');
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    }

    // Modal Jejak Audit
    function openAuditDetailModal(resultId) {
        var modal = document.getElementById('auditDetailModal');
        var loading = document.getElementById('auditModalLoading');
        var content = document.getElementById('auditModalContent');
        var errorBox = document.getElementById('auditModalError');
        
        modal.style.display = 'flex';
        loading.style.display = 'flex';
        content.style.display = 'none';
        errorBox.style.display = 'none';

        fetch('{{ url("sodc/results") }}/' + resultId + '/detail-json')
            .then(function(response) {
                if (!response.ok) throw new Error('Gagal mengambil data detail');
                return response.json();
            })
            .then(function(res) {
                if (!res.success) throw new Error(res.message || 'Data tidak ditemukan');
                var data = res.data;

                // Header & Link Halaman Penuh
                document.getElementById('auditModalTitleBin').innerText = data.bin_code;
                document.getElementById('auditModalTitleSku').innerText = data.sku_code;
                document.getElementById('auditModalFullPageBtn').href = data.show_url;

                // Detail Produk & Kategori
                document.getElementById('auditProductSku').innerText = data.sku_code;
                document.getElementById('auditProductName').innerText = data.product_name;
                document.getElementById('auditProductCategory').innerText = data.product_category_code || '-';
                document.getElementById('auditProductPackname').innerText = data.packname || '-';
                document.getElementById('auditProductUom').innerText = (data.uom || 1) + ' Pcs';
                document.getElementById('auditProductPrincipal').innerText = data.principal || '-';
                document.getElementById('auditProductCompany').innerText = data.company_code || '-';
                document.getElementById('auditProductBarcode').innerText = (data.barcode || '-') + ' / ' + (data.carton_code || '-');

                // Ringkasan Qty
                document.getElementById('auditKpiSystem').innerText = data.system_qty;
                document.getElementById('auditKpiTeamA').innerText = data.team_a_qty !== null ? data.team_a_qty : '-';
                document.getElementById('auditKpiTeamB').innerText = data.team_b_qty !== null ? data.team_b_qty : '-';
                document.getElementById('auditKpiR1').innerText = data.recount1_qty !== null ? data.recount1_qty : '-';
                document.getElementById('auditKpiR2').innerText = data.recount2_qty !== null ? data.recount2_qty : '-';
                document.getElementById('auditKpiR3').innerText = data.recount3_qty !== null ? data.recount3_qty : '-';
                document.getElementById('auditKpiFinal').innerText = data.final_qty !== null ? data.final_qty : '-';
                
                var varText = (data.variance_qty > 0 ? '+' : '') + data.variance_qty;
                var varElem = document.getElementById('auditKpiVariance');
                varElem.innerText = varText;
                varElem.style.color = (data.variance_qty == 0) ? '#15803d' : '#e11d48';

                var statusElem = document.getElementById('auditKpiStatus');
                statusElem.innerText = data.result_status;
                if (data.result_status === 'MATCH') {
                    statusElem.style.background = '#10b981';
                } else if (['FINAL', 'APPROVED'].indexOf(data.result_status) !== -1) {
                    statusElem.style.background = '#3b82f6';
                } else if (data.result_status && data.result_status.indexOf('RECOUNT') !== -1) {
                    statusElem.style.background = '#f59e0b';
                } else {
                    statusElem.style.background = '#8b5cf6';
                }

                // Tabel Jejak Input (Timeline)
                var tbody = document.getElementById('auditTrailTableBody');
                tbody.innerHTML = '';

                if (!data.counts || data.counts.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 24px; color: #94a3b8;">Belum ada jejak input fisik yang tercatat.</td></tr>';
                } else {
                    data.counts.forEach(function(c, idx) {
                        var stageName = 'Hitung Awal';
                        var stageColor = '#0ea5e9';
                        var stageBg = '#f0f9ff';

                        if (c.count_sequence == 1) {
                            if (c.team_id === 'TEAM_A' || idx === 0) {
                                stageName = 'Hitung Awal (Team A)';
                                stageColor = '#16a34a';
                                stageBg = '#f0fdf4';
                            } else {
                                stageName = 'Hitung Awal (Team B)';
                                stageColor = '#0284c7';
                                stageBg = '#f0f9ff';
                            }
                        } else if (c.count_sequence == 2) {
                            stageName = 'Recount 1 (R1)';
                            stageColor = '#d97706';
                            stageBg = '#fffbeb';
                        } else if (c.count_sequence == 3) {
                            stageName = 'Recount 2 (R2)';
                            stageColor = '#dc2626';
                            stageBg = '#fef2f2';
                        } else if (c.count_sequence >= 4) {
                            stageName = 'Recount 3 (R3)';
                            stageColor = '#7e22ce';
                            stageBg = '#faf5ff';
                        }

                        var tr = document.createElement('tr');
                        tr.style.borderBottom = '1px solid #f1f5f9';
                        tr.innerHTML = `
                            <td style="padding: 10px 12px; font-weight: 700;">
                                <span style="background: ${stageBg}; color: ${stageColor}; padding: 3px 8px; border-radius: 4px; border: 1px solid ${stageColor}33; font-size: 0.78rem;">
                                    ${stageName}
                                </span>
                            </td>
                            <td style="padding: 10px 12px;">
                                <div style="font-weight: 700; color: #1e293b; font-size: 0.85rem;">
                                    <i class="fas fa-user-circle" style="color: #64748b; margin-right: 4px;"></i>
                                    ${c.counted_by_name}
                                </div>
                            </td>
                            <td style="padding: 10px 12px;">
                                <span style="background: #f1f5f9; color: #475569; padding: 2px 6px; border-radius: 4px; font-size: 0.72rem; font-weight: 600;">
                                    ${c.counted_by_role}
                                </span>
                            </td>
                            <td style="padding: 10px 12px; text-align: right; font-weight: 600; color: #334155;">${c.input_karton}</td>
                            <td style="padding: 10px 12px; text-align: right; font-weight: 600; color: #334155;">${c.input_pcs}</td>
                            <td style="padding: 10px 12px; text-align: right; font-weight: 800; font-size: 0.95rem; color: #0f172a;">${c.count_qty}</td>
                            <td style="padding: 10px 12px; color: #64748b; font-size: 0.78rem;">
                                <i class="far fa-clock" style="margin-right: 3px;"></i>${c.counted_at}
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                }

                loading.style.display = 'none';
                content.style.display = 'block';
            })
            .catch(function(err) {
                loading.style.display = 'none';
                errorBox.innerText = err.message || 'Terjadi kesalahan saat memuat data.';
                errorBox.style.display = 'block';
            });
    }

    function closeAuditDetailModal() {
        document.getElementById('auditDetailModal').style.display = 'none';
    }
    
    // Close dropdown & modal when clicking outside
    window.onclick = function(event) {
        if (!event.target.matches('.fa-download') && !event.target.matches('button') && !event.target.closest('button[onclick="toggleExportDropdown()"]')) {
            var dropdowns = document.getElementsByClassName("dropdown-content");
            var dropdown = document.getElementById('exportDropdown');
            if (dropdown && dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            }
        }
        var auditModal = document.getElementById('auditDetailModal');
        if (event.target === auditModal) {
            closeAuditDetailModal();
        }
    }
</script>

<!-- R3 Assignment Modal -->
<div id="assignR3Modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; width: 500px; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="background: #ef4444; padding: 16px; color: white; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.1rem;">Assign to R3 (Auditor)</h3>
            <button type="button" onclick="closeAssignR3Modal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; padding: 0;">&times;</button>
        </div>
        <div style="padding: 20px;">
            <p style="font-size: 0.9rem; color: #475569; margin-bottom: 15px;">
                Anda akan memberikan tugas Recount 3 (Final Audit) untuk item yang dicentang kepada Auditor berikut:
            </p>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #334155; margin-bottom: 8px;">Pilih Auditor (User):</label>
                <select name="r3_assigned_to" id="r3_assigned_to" form="bulkR3Form" required style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; outline: none; font-size: 0.9rem;">
                    <option value="">-- Pilih User --</option>
                    @php
                        $users = \App\Models\User::where('is_active', 1)->get();
                    @endphp
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                    @endforeach
                </select>
            </div>
            <div style="text-align: right;">
                <button type="button" onclick="closeAssignR3Modal()" style="background: #cbd5e1; color: #334155; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-right: 10px;">Batal</button>
                <button type="submit" form="bulkR3Form" style="background: #ef4444; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer;">Assign Sekarang</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Jejak Audit & Detail Input (Team A, Team B s/d Recount 3) -->
<div id="auditDetailModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 1050; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; width: 100%; max-width: 900px; max-height: 90vh; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); display: flex; flex-direction: column;">
        
        <!-- Modal Header -->
        <div style="background: #0f172a; padding: 18px 24px; color: white; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(14, 165, 233, 0.2); border: 1px solid rgba(14, 165, 233, 0.4); display: flex; align-items: center; justify-content: center; color: #38bdf8;">
                    <i class="fas fa-eye" style="font-size: 1.1rem;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                        Jejak Audit & Detail Hasil Opname
                    </h3>
                    <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 2px;">
                        Lokasi: <span id="auditModalTitleBin" style="color: #38bdf8; font-weight: 700;">-</span> | SKU: <span id="auditModalTitleSku" style="color: #f1f5f9; font-weight: 600; font-family: monospace;">-</span>
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                <a id="auditModalFullPageBtn" href="#" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.1); color: #e2e8f0; padding: 6px 12px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; text-decoration: none; border: 1px solid rgba(255,255,255,0.2); transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                    <i class="fas fa-external-link-alt"></i> Buka Halaman Penuh
                </a>
                <button type="button" onclick="closeAuditDetailModal()" style="background: none; border: none; color: #94a3b8; font-size: 1.6rem; cursor: pointer; padding: 0 4px; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">&times;</button>
            </div>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div style="padding: 24px; overflow-y: auto; flex: 1;">
            
            <!-- Loading Indicator -->
            <div id="auditModalLoading" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px; gap: 14px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2.2rem; color: #0ea5e9;"></i>
                <div style="color: #64748b; font-weight: 600; font-size: 0.92rem;">Memuat jejak audit fisik dari database...</div>
            </div>

            <!-- Error Box -->
            <div id="auditModalError" style="display: none; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 16px;"></div>

            <!-- Main Content Container -->
            <div id="auditModalContent" style="display: none;">
                
                <!-- Card Detail Produk -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0;">
                        <div>
                            <div style="font-size: 0.72rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Deskripsi Produk:</div>
                            <h4 id="auditProductName" style="margin: 2px 0 0 0; color: #0f172a; font-size: 1.15rem; font-weight: 800;">-</h4>
                            <div style="font-family: monospace; font-size: 0.85rem; color: #64748b; margin-top: 2px;">
                                SKU: <strong id="auditProductSku" style="color: #0284c7;">-</strong>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 0.72rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Status Rekonsiliasi:</div>
                            <span id="auditKpiStatus" style="display: inline-block; background: #10b981; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; margin-top: 4px;">
                                -
                            </span>
                        </div>
                    </div>

                    <!-- 4 Kolom Spesifikasi Field Database -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; font-size: 0.85rem;">
                        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px;">
                            <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Kategori Produk</div>
                            <div style="font-weight: 700; color: #0284c7; margin-top: 2px;">
                                <span id="auditProductCategory" style="background: #e0f2fe; padding: 2px 6px; border-radius: 4px;">-</span>
                            </div>
                        </div>

                        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px;">
                            <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Kemasan (Packname)</div>
                            <div style="font-weight: 700; color: #166534; margin-top: 2px;">
                                <span id="auditProductPackname" style="background: #dcfce7; padding: 2px 6px; border-radius: 4px;">-</span>
                            </div>
                        </div>

                        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px;">
                            <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700;">UOM (Isi per Kemasan)</div>
                            <div id="auditProductUom" style="font-weight: 700; color: #0f172a; margin-top: 2px;">-</div>
                        </div>

                        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px;">
                            <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Principal / Company</div>
                            <div style="font-weight: 700; color: #334155; margin-top: 2px;">
                                <span id="auditProductPrincipal">-</span> (<span id="auditProductCompany">-</span>)
                            </div>
                        </div>

                        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; grid-column: 1 / -1;">
                            <div style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 700;">Barcode Satuan / Carton Code</div>
                            <div id="auditProductBarcode" style="font-family: monospace; font-weight: 600; color: #334155; margin-top: 2px;">-</div>
                        </div>
                    </div>
                </div>

                <!-- Card Ringkasan Qty -->
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; margin-bottom: 20px;">
                    <div style="font-size: 0.78rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 10px;">
                        <i class="fas fa-calculator" style="color: #0ea5e9;"></i> Perbandingan Kuantitas Hasil Opname:
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(95px, 1fr)); gap: 8px; text-align: center;">
                        <div style="background: #f8fafc; border-radius: 8px; padding: 8px; border: 1px solid #e2e8f0;">
                            <div style="font-size: 0.68rem; color: #64748b; font-weight: 700;">WMS QTY</div>
                            <div id="auditKpiSystem" style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-top: 2px;">0</div>
                        </div>
                        <div style="background: #f0fdf4; border-radius: 8px; padding: 8px; border: 1px solid #bbf7d0;">
                            <div style="font-size: 0.68rem; color: #166534; font-weight: 700;">TEAM A</div>
                            <div id="auditKpiTeamA" style="font-size: 1.15rem; font-weight: 800; color: #15803d; margin-top: 2px;">-</div>
                        </div>
                        <div style="background: #f0f9ff; border-radius: 8px; padding: 8px; border: 1px solid #bae6fd;">
                            <div style="font-size: 0.68rem; color: #0369a1; font-weight: 700;">TEAM B</div>
                            <div id="auditKpiTeamB" style="font-size: 1.15rem; font-weight: 800; color: #0284c7; margin-top: 2px;">-</div>
                        </div>
                        <div style="background: #fffbeb; border-radius: 8px; padding: 8px; border: 1px solid #fde68a;">
                            <div style="font-size: 0.68rem; color: #92400e; font-weight: 700;">RECOUNT 1</div>
                            <div id="auditKpiR1" style="font-size: 1.15rem; font-weight: 800; color: #d97706; margin-top: 2px;">-</div>
                        </div>
                        <div style="background: #fef2f2; border-radius: 8px; padding: 8px; border: 1px solid #fecaca;">
                            <div style="font-size: 0.68rem; color: #991b1b; font-weight: 700;">RECOUNT 2</div>
                            <div id="auditKpiR2" style="font-size: 1.15rem; font-weight: 800; color: #dc2626; margin-top: 2px;">-</div>
                        </div>
                        <div style="background: #faf5ff; border-radius: 8px; padding: 8px; border: 1px solid #e9d5ff;">
                            <div style="font-size: 0.68rem; color: #6b21a8; font-weight: 700;">RECOUNT 3</div>
                            <div id="auditKpiR3" style="font-size: 1.15rem; font-weight: 800; color: #7e22ce; margin-top: 2px;">-</div>
                        </div>
                        <div style="background: #0f172a; color: white; border-radius: 8px; padding: 8px;">
                            <div style="font-size: 0.68rem; color: #94a3b8; font-weight: 700;">FINAL</div>
                            <div id="auditKpiFinal" style="font-size: 1.15rem; font-weight: 800; color: #38bdf8; margin-top: 2px;">-</div>
                        </div>
                        <div style="background: #f8fafc; border-radius: 8px; padding: 8px; border: 1px solid #e2e8f0;">
                            <div style="font-size: 0.68rem; color: #475569; font-weight: 700;">SELISIH</div>
                            <div id="auditKpiVariance" style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-top: 2px;">0</div>
                        </div>
                    </div>
                </div>

                <!-- Card Timeline Jejak Input User -->
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h5 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-history" style="color: #6366f1;"></i> Jejak Riwayat Input (User & Waktu Input):
                        </h5>
                        <span style="font-size: 0.75rem; color: #64748b;">Mulai dari Team A, Team B s/d Recount 3</span>
                    </div>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.82rem;">
                            <thead>
                                <tr style="background: #f8fafc; color: #475569; text-align: left; font-size: 0.75rem; text-transform: uppercase;">
                                    <th style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0;">Tahap Hitung</th>
                                    <th style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0;">User Penginput</th>
                                    <th style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0;">Role</th>
                                    <th style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0; text-align: right;">Karton</th>
                                    <th style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0; text-align: right;">Pcs</th>
                                    <th style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0; text-align: right;">Total Qty</th>
                                    <th style="padding: 10px 12px; border-bottom: 1px solid #e2e8f0;">Waktu Input</th>
                                </tr>
                            </thead>
                            <tbody id="auditTrailTableBody">
                                <!-- Populated dynamically by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Footer -->
        <div style="background: #f8fafc; padding: 14px 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 12px;">
            <button type="button" onclick="closeAuditDetailModal()" style="background: #64748b; color: white; border: none; padding: 8px 18px; border-radius: 8px; font-weight: 600; font-size: 0.88rem; cursor: pointer;">
                Tutup
            </button>
        </div>
    </div>
</div>
@endpush

