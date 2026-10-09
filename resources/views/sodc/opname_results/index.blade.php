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
    <div style="background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; padding: 14px 16px; border-radius: 10px; margin-bottom: 16px;">
        <i class="fas fa-exclamation-triangle"></i> {{ $error }}
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
                        <th style="padding: 12px 16px; text-align: center;">Aksi</th>
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
                                @php
                                    $showInputR1 = $isR1;
                                    $showInputR2 = $isR2;
                                    $showInputR3 = false;
                                    // Deteksi R3 FAILED (Misal: sudah diisi R2, tapi beda dengan semua, dan admin buat assignment R3)
                                    if (($hasR3 ?? false) && $res->result_status == 'RECOUNT' && !is_null($res->recount2_qty) && is_null($res->recount3_qty ?? null)) {
                                        $showInputR3 = true;
                                    }
                                @endphp
                                @if($showInputR1 || $showInputR2 || $showInputR3)
                                    @php $currentLevel = $showInputR3 ? 3 : ($showInputR2 ? 2 : 1); @endphp
                                    <button type="button" onclick="openRecountModal({{ $res->id }}, {{ $currentLevel }}, '{{ $res->referenceDetail->bin_code }}', '{{ $res->referenceDetail->sku_code }}')" style="background: #0f172a; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">
                                        Input R{{ $currentLevel }}
                                    </button>
                                @else
                                    <span style="color: #cbd5e1;">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($hasR3 ?? false) ? 11 : 10 }}" style="text-align: center; padding: 40px; color: #64748b;">Tidak ada data yang ditemukan.</td>
                        </tr>
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
    
    // Close dropdown when clicking outside
    window.onclick = function(event) {
        if (!event.target.matches('.fa-download') && !event.target.matches('button') && !event.target.closest('button[onclick="toggleExportDropdown()"]')) {
            var dropdowns = document.getElementsByClassName("dropdown-content");
            var dropdown = document.getElementById('exportDropdown');
            if (dropdown && dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            }
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
@endpush
