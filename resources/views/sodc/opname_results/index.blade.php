@extends('layouts.app')
@section('title', 'Rekonsiliasi Hasil Opname')
@section('page_title', 'Hasil Rekonsiliasi Opname')

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
        <div style="padding: 16px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 style="margin: 0; color: #0f172a; font-size: 1.1rem;">Data Rekonsiliasi</h3>
            
            <form method="GET" action="{{ route('sodc.results.index') }}" style="display: flex; gap: 10px;">
                <select name="status" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; outline: none;">
                    <option value="">Semua Status</option>
                    <option value="MATCH" {{ request('status') == 'MATCH' ? 'selected' : '' }}>MATCH</option>
                    <option value="RECOUNT_1" {{ request('status') == 'RECOUNT_1' ? 'selected' : '' }}>Butuh RECOUNT 1</option>
                    <option value="RECOUNT_2" {{ request('status') == 'RECOUNT_2' ? 'selected' : '' }}>Butuh RECOUNT 2</option>
                    <option value="FINAL" {{ request('status') == 'FINAL' ? 'selected' : '' }}>FINAL</option>
                    <option value="UNCOUNTED" {{ request('status') == 'UNCOUNTED' ? 'selected' : '' }}>UNCOUNTED</option>
                </select>
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table class="premium-table" style="width: 100%; border-collapse: collapse; min-width: 1000px;">
                <thead>
                    <tr style="background: #f1f5f9; text-align: left; font-size: 0.8rem; color: #475569;">
                        <th style="padding: 12px 16px;">Bin / SKU</th>
                        <th style="padding: 12px 16px; text-align: right;">WMS Qty</th>
                        <th style="padding: 12px 16px; text-align: right;">Team A</th>
                        <th style="padding: 12px 16px; text-align: right;">Team B</th>
                        <th style="padding: 12px 16px; text-align: right;">Recount 1</th>
                        <th style="padding: 12px 16px; text-align: right;">Recount 2</th>
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
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 700; color: #0ea5e9;">{{ $res->referenceDetail->bin_code ?? '-' }}</div>
                                <div style="font-family: monospace; font-weight: 600;">
                                    {{ $res->referenceDetail->sku_code ?? '-' }}
                                    @if($isNyasar)
                                        <span style="font-size: 0.65rem; background: #ef4444; color: white; padding: 2px 6px; border-radius: 4px; margin-left: 6px; font-family: sans-serif; font-weight: 800;">BARANG NYASAR</span>
                                    @endif
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b;">
                                    <strong style="color: #334155;">{{ Str::limit($res->referenceDetail->product->product_name ?? 'Unknown Product', 40) }}</strong><br>
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
                            <td style="padding: 12px 16px; text-align: right; font-weight: 800; font-size: 0.95rem; color: #0f172a;">
                                {{ is_null($res->final_qty) ? '-' : (float)$res->final_qty }}
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <span style="background: {{ $badgeColor }}; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase;">
                                    {{ $badgeText }}
                                </span>
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                @if($isR1 || $isR2)
                                    <button type="button" onclick="openRecountModal({{ $res->id }}, {{ $isR1 ? 1 : 2 }}, '{{ $res->referenceDetail->bin_code }}', '{{ $res->referenceDetail->sku_code }}')" style="background: #0f172a; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">
                                        Input R{{ $isR1 ? 1 : 2 }}
                                    </button>
                                @else
                                    <span style="color: #cbd5e1;">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: #64748b;">Tidak ada data yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 16px; border-top: 1px solid #e2e8f0; background: #f8fafc;" class="pagination-wrapper">
            {{ $results->links() }}
        </div>
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
</script>
@endpush
