@extends('layouts.app')
@section('title', 'Rekonsiliasi Hasil Opname')
@section('page_title', 'Hasil Rekonsiliasi Opname')

@section('page_actions')
@if(isset($activeSessions) && $activeSessions->count() > 0)
<form action="{{ route('sodc.results.index') }}" method="GET" style="display: inline-flex; align-items: center; gap: 10px;">
    <label style="color: #64748b; font-weight: 600; font-size: 0.85rem; margin: 0;">Pilih Sesi:</label>
    <select name="session_id" onchange="this.form.submit()" style="padding: 6px 14px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; font-size: 0.85rem; font-weight: 500; color: #334155; min-width: 180px; cursor: pointer; outline: none; transition: all 0.2s;">
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
    <div style="background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-exclamation-triangle"></i> {{ $error }}
    </div>
@else
    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden;">
        <!-- Header -->
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; flex-wrap: wrap; gap: 10px;">
            <h3 style="margin: 0; color: #0f172a; font-size: 1.1rem; font-weight: 600;">Data Rekonsiliasi</h3>
            
            <form method="GET" action="{{ route('sodc.results.index') }}" style="display: flex; gap: 10px;">
                <select name="status" onchange="this.form.submit()" style="padding: 8px 14px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; outline: none; background: #fff; color: #334155; cursor: pointer; min-width: 160px;">
                    <option value="">Semua Status</option>
                    <option value="MATCH" {{ request('status') == 'MATCH' ? 'selected' : '' }}>MATCH</option>
                    <option value="RECOUNT_1" {{ request('status') == 'RECOUNT_1' ? 'selected' : '' }}>Butuh RECOUNT 1</option>
                    <option value="RECOUNT_2" {{ request('status') == 'RECOUNT_2' ? 'selected' : '' }}>Butuh RECOUNT 2</option>
                    <option value="FINAL" {{ request('status') == 'FINAL' ? 'selected' : '' }}>FINAL</option>
                    <option value="UNCOUNTED" {{ request('status') == 'UNCOUNTED' ? 'selected' : '' }}>UNCOUNTED</option>
                </select>
            </form>
        </div>

        <form id="bulkRecountForm" method="POST" action="{{ route('sodc.results.bulk_dispatch') }}">
            @csrf
            
            <!-- Info Bar -->
            <div style="padding: 12px 20px; background: #fffbeb; border-bottom: 1px solid #fde68a; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div style="font-size: 0.85rem; color: #b45309;">
                    <i class="fas fa-info-circle me-1"></i> Centang hasil yang butuh hitung ulang (Recount), lalu klik tombol di sebelah kanan untuk mengirim tugas ke Tim Recount.
                </div>
                <button type="submit" class="btn btn-sm btn-warning" style="font-weight: 600; font-size: 0.8rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 8px 16px; border: none; border-radius: 6px; background: #f59e0b; color: #fff; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#d97706'" onmouseout="this.style.background='#f59e0b'" onclick="return confirm('Yakin ingin mengirim tugas recount ke semua anggota Tim Recount Global?')">
                    <i class="fas fa-paper-plane me-1"></i> Kirim ke Tim Recount
                </button>
            </div>

            <!-- Table -->
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 1100px; font-size: 0.85rem;">
                    <thead>
                        <tr style="background: #f1f5f9; text-align: left; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 12px 16px; width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAllRecount" style="cursor: pointer; width: 16px; height: 16px;">
                            </th>
                            <th style="padding: 12px 16px; font-weight: 600; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Bin / SKU</th>
                            <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">WMS Qty</th>
                            <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Team A</th>
                            <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Team B</th>
                            <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Recount 1</th>
                            <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Recount 2</th>
                            <th style="padding: 12px 16px; text-align: right; font-weight: 600; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Final Qty</th>
                            <th style="padding: 12px 16px; text-align: center; font-weight: 600; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                            <th style="padding: 12px 16px; text-align: center; font-weight: 600; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Aksi</th>
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
                                
                                if ($isMatch) { $badgeColor = '#10b981'; }
                                if ($isFinal) { $badgeColor = '#3b82f6'; }
                                if ($isR1) { $badgeColor = '#f59e0b'; $badgeText = 'RECOUNT 1'; }
                                if ($isR2) { $badgeColor = '#ef4444'; $badgeText = 'RECOUNT 2'; }
                                if (str_starts_with($res->result_status, 'WAITING')) { $badgeColor = '#8b5cf6'; }
                                
                                $isNyasar = (float)$res->system_qty == 0;
                            @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9; {{ $isNyasar ? 'background-color: #fef9c3;' : '' }} transition: background 0.15s;" onmouseover="this.style.background='{{ $isNyasar ? '#fef3a0' : '#f8fafc' }}'" onmouseout="this.style.background='{{ $isNyasar ? '#fef9c3' : '' }}'">
                                <td style="padding: 12px 16px; text-align: center; vertical-align: middle;">
                                    <input type="checkbox" name="result_ids[]" value="{{ $res->id }}" class="recount-checkbox" 
                                        {{ ($isR1 || $isR2) ? '' : 'disabled' }} style="cursor: pointer; width: 16px; height: 16px;">
                                </td>
                                <td style="padding: 12px 16px; vertical-align: middle;">
                                    <div style="font-weight: 700; color: #0ea5e9; font-size: 0.9rem;">{{ $res->referenceDetail->bin_code ?? '-' }}</div>
                                    <div style="font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace; font-weight: 600; font-size: 0.85rem; margin-top: 2px;">
                                        {{ $res->referenceDetail->sku_code ?? '-' }}
                                        @if($isNyasar)
                                            <span title="Barang ini tidak ada di data awal WMS untuk Bin ini" style="font-size: 0.6rem; background: #64748b; color: white; padding: 2px 8px; border-radius: 4px; margin-left: 6px; font-family: sans-serif; font-weight: 700; text-transform: uppercase;">NON-WMS</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 3px;">
                                        <strong style="color: #334155;">{{ Str::limit($res->referenceDetail->product->sku_name ?? 'Unknown Product', 40) }}</strong><br>
                                        UOM: {{ $res->referenceDetail->product->uom ?? '-' }} | Pack: {{ $res->referenceDetail->product->packname ?? '-' }}
                                    </div>
                                </td>
                                <td style="padding: 12px 16px; text-align: right; font-weight: 600; vertical-align: middle;">{{ (float)$res->system_qty }}</td>
                                <td style="padding: 12px 16px; text-align: right; vertical-align: middle;">{{ is_null($res->team_a_qty) ? '-' : (float)$res->team_a_qty }}</td>
                                <td style="padding: 12px 16px; text-align: right; vertical-align: middle;">{{ is_null($res->team_b_qty) ? '-' : (float)$res->team_b_qty }}</td>
                                <td style="padding: 12px 16px; text-align: right; font-weight: bold; color: #d97706; vertical-align: middle;">
                                    {{ is_null($res->recount1_qty) ? '-' : (float)$res->recount1_qty }}
                                </td>
                                <td style="padding: 12px 16px; text-align: right; font-weight: bold; color: #b91c1c; vertical-align: middle;">
                                    {{ is_null($res->recount2_qty) ? '-' : (float)$res->recount2_qty }}
                                </td>
                                <td style="padding: 12px 16px; text-align: right; font-weight: 800; font-size: 0.95rem; color: #0f172a; vertical-align: middle;">
                                    {{ is_null($res->final_qty) ? '-' : (float)$res->final_qty }}
                                </td>
                                <td style="padding: 12px 16px; text-align: center; vertical-align: middle;">
                                    <span style="background: {{ $badgeColor }}; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; display: inline-block;">
                                        {{ $badgeText }}
                                    </span>
                                </td>
                                <td style="padding: 12px 16px; text-align: center; vertical-align: middle;">
                                    @if($isR1 || $isR2)
                                        <button type="button" onclick="openRecountModal({{ $res->id }}, {{ $isR1 ? 1 : 2 }}, '{{ $res->referenceDetail->bin_code }}', '{{ $res->referenceDetail->sku_code }}')" style="background: #0f172a; color: white; border: none; padding: 6px 14px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#1e293b'" onmouseout="this.style.background='#0f172a'">
                                            Input R{{ $isR1 ? 1 : 2 }}
                                        </button>
                                    @else
                                        <span style="color: #cbd5e1;">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 50px 20px; color: #64748b;">
                                    <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 10px; color: #cbd5e1;"></i>
                                    Tidak ada data yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
        
        <!-- Footer Pagination -->
        <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
            {{ $results->links() }}
        </div>
    </div>
@endif

<!-- Recount Modal -->
<div id="recountModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; width: 420px; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3); animation: modalSlideIn 0.25s ease;">
        <div style="background: #0f172a; padding: 16px 20px; color: white; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600;">
                <i class="fas fa-calculator me-2"></i> Input Hitung Ulang
            </h3>
            <button type="button" onclick="closeRecountModal()" style="background: none; border: none; color: white; font-size: 1.8rem; cursor: pointer; padding: 0; line-height: 1; opacity: 0.7; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">&times;</button>
        </div>
        <form method="POST" action="{{ route('sodc.results.input_recount') }}">
            @csrf
            <div style="padding: 24px 20px;">
                <input type="hidden" name="result_id" id="modalResultId">
                <input type="hidden" name="level" id="modalLevel">
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.3px;">Bin / SKU</label>
                    <div id="modalProductInfo" style="font-weight: 700; color: #0f172a; font-size: 1rem; background: #f8fafc; padding: 10px 14px; border-radius: 8px; border: 1px solid #e2e8f0;">-</div>
                </div>

                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.3px;">Hasil Qty (Recount <span id="modalLevelText"></span>)</label>
                    <input type="number" step="0.01" name="recount_qty" required style="width: 100%; padding: 10px 14px; border: 2px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 1rem; box-sizing: border-box; transition: border-color 0.2s;" onfocus="this.style.borderColor='#0ea5e9'" onblur="this.style.borderColor='#e2e8f0'" placeholder="Masukkan jumlah hasil recount">
                </div>
            </div>
            <div style="padding: 16px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: right; display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeRecountModal()" style="padding: 8px 20px; border: 1px solid #e2e8f0; background: white; color: #475569; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='white'">Batal</button>
                <button type="submit" style="padding: 8px 20px; border: none; background: #0ea5e9; color: white; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#0284c7'" onmouseout="this.style.background='#0ea5e9'">
                    <i class="fas fa-save me-1"></i> Simpan Recount
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-30px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    /* Hover effect for table rows */
    .table-row-hover:hover {
        background-color: #f8fafc !important;
    }
    
    /* Custom scrollbar for table */
    .table-wrapper::-webkit-scrollbar {
        height: 8px;
    }
    .table-wrapper::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .table-wrapper::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .table-wrapper::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

@push('styles')
<style>
    .pagination-wrapper svg { width: 1.25rem; height: 1.25rem; }
    .pagination-wrapper nav > div { margin-top: 10px; }
    .pagination-wrapper p { font-size: 0.875rem; color: #64748b; }
</style>
@endpush

@push('scripts')
<script>
    function openRecountModal(resultId, level, binCode, skuCode) {
        document.getElementById('modalResultId').value = resultId;
        document.getElementById('modalLevel').value = level;
        document.getElementById('modalLevelText').innerText = level;
        document.getElementById('modalProductInfo').innerText = binCode + ' - ' + skuCode;
        
        const modal = document.getElementById('recountModal');
        modal.style.display = 'flex';
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }

    function closeRecountModal() {
        const modal = document.getElementById('recountModal');
        modal.style.display = 'none';
        // Restore body scroll
        document.body.style.overflow = '';
    }

    // Select All checkbox
    document.getElementById('selectAllRecount').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.recount-checkbox:not([disabled])');
        for (let checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeRecountModal();
        }
    });

    // Close modal on backdrop click
    document.getElementById('recountModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRecountModal();
        }
    });
</script>
@endpush