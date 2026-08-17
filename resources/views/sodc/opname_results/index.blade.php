@extends('layouts.app')
@section('title', 'Rekonsiliasi Hasil Opname')
@section('page_title', 'Hasil Rekonsiliasi Opname')

@section('page_actions')
@if(isset($activeSessions) && $activeSessions->count() > 0)
<form action="{{ route('sodc.results.index') }}" method="GET" class="d-inline-flex align-items-center gap-2">
    <label class="mb-0 text-secondary fw-semibold small">Pilih Sesi:</label>
    <select name="session_id" onchange="this.form.submit()" class="form-select form-select-sm" style="width: auto; min-width: 180px;">
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
    <div class="alert alert-danger d-flex align-items-center" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> {{ $error }}
    </div>
@else
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 text-dark">Data Rekonsiliasi</h5>
            
            <form method="GET" action="{{ route('sodc.results.index') }}" class="d-flex gap-2">
                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" style="min-width: 160px;">
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
            
            <div class="alert alert-warning rounded-0 mb-0 d-flex justify-content-between align-items-center py-2 px-3" style="border-left: none; border-right: none;">
                <div class="small text-warning-emphasis">
                    <i class="fas fa-info-circle me-1"></i> Centang hasil yang butuh hitung ulang (Recount), lalu klik tombol di sebelah kanan untuk mengirim tugas ke Tim Recount.
                </div>
                <button type="submit" class="btn btn-warning btn-sm fw-semibold shadow-sm" onclick="return confirm('Yakin ingin mengirim tugas recount ke semua anggota Tim Recount Global?')">
                    <i class="fas fa-paper-plane me-1"></i> Kirim ke Tim Recount
                </button>
            </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0" style="min-width: 1100px;">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 40px;">
                            <input type="checkbox" id="selectAllRecount" class="form-check-input">
                        </th>
                        <th>Bin / SKU</th>
                        <th class="text-end">WMS Qty</th>
                        <th class="text-end">Team A</th>
                        <th class="text-end">Team B</th>
                        <th class="text-end">Recount 1</th>
                        <th class="text-end">Recount 2</th>
                        <th class="text-end">Final Qty</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $res)
                        @php
                            $isMatch = $res->result_status == 'MATCH';
                            $isFinal = in_array($res->result_status, ['FINAL', 'APPROVED']);
                            $isR1 = $res->result_status == 'RECOUNT' && is_null($res->recount1_qty);
                            $isR2 = $res->result_status == 'RECOUNT' && !is_null($res->recount1_qty) && is_null($res->recount2_qty);
                            
                            $badgeClass = 'bg-secondary'; // default UNCOUNTED
                            $badgeText = $res->result_status;
                            
                            if ($isMatch) { $badgeClass = 'bg-success'; }
                            if ($isFinal) { $badgeClass = 'bg-primary'; }
                            if ($isR1) { $badgeClass = 'bg-warning text-dark'; $badgeText = 'RECOUNT 1'; }
                            if ($isR2) { $badgeClass = 'bg-danger'; $badgeText = 'RECOUNT 2'; }
                            if (str_starts_with($res->result_status, 'WAITING')) { $badgeClass = 'bg-purple'; }
                            
                            $isNyasar = (float)$res->system_qty == 0;
                        @endphp
                        <tr class="{{ $isNyasar ? 'table-warning' : '' }} align-middle">
                            <td class="text-center">
                                <input type="checkbox" name="result_ids[]" value="{{ $res->id }}" class="form-check-input recount-checkbox" 
                                    {{ ($isR1 || $isR2) ? '' : 'disabled' }}>
                            </td>
                            <td>
                                <div class="fw-bold text-primary">{{ $res->referenceDetail->bin_code ?? '-' }}</div>
                                <div>
                                    <span class="font-monospace fw-semibold">{{ $res->referenceDetail->sku_code ?? '-' }}</span>
                                    @if($isNyasar)
                                        <span class="badge bg-dark ms-1" title="Barang ini tidak ada di data awal WMS untuk Bin ini" style="font-size: 0.6rem;">NON-WMS</span>
                                    @endif
                                </div>
                                <div class="small text-secondary">
                                    <strong class="text-dark">{{ Str::limit($res->referenceDetail->product->sku_name ?? 'Unknown Product', 40) }}</strong><br>
                                    UOM: {{ $res->referenceDetail->product->uom ?? '-' }} | Pack: {{ $res->referenceDetail->product->packname ?? '-' }}
                                </div>
                            </td>
                            <td class="text-end fw-semibold">{{ (float)$res->system_qty }}</td>
                            <td class="text-end">{{ is_null($res->team_a_qty) ? '-' : (float)$res->team_a_qty }}</td>
                            <td class="text-end">{{ is_null($res->team_b_qty) ? '-' : (float)$res->team_b_qty }}</td>
                            <td class="text-end fw-semibold text-warning">
                                {{ is_null($res->recount1_qty) ? '-' : (float)$res->recount1_qty }}
                            </td>
                            <td class="text-end fw-semibold text-danger">
                                {{ is_null($res->recount2_qty) ? '-' : (float)$res->recount2_qty }}
                            </td>
                            <td class="text-end fw-bold fs-6 text-dark">
                                {{ is_null($res->final_qty) ? '-' : (float)$res->final_qty }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $badgeClass }} px-3 py-2" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                    {{ $badgeText }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($isR1 || $isR2)
                                    <button type="button" onclick="openRecountModal({{ $res->id }}, {{ $isR1 ? 1 : 2 }}, '{{ $res->referenceDetail->bin_code }}', '{{ $res->referenceDetail->sku_code }}')" class="btn btn-dark btn-sm fw-semibold">
                                        Input R{{ $isR1 ? 1 : 2 }}
                                    </button>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-secondary">
                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                Tidak ada data yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </form>
        <div class="card-footer bg-light">
            {{ $results->links() }}
        </div>
    </div>
@endif

<!-- Recount Modal -->
<div id="recountModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Input Hitung Ulang</h5>
                <button type="button" class="btn-close btn-close-white" onclick="closeRecountModal()"></button>
            </div>
            <form method="POST" action="{{ route('sodc.results.input_recount') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="result_id" id="modalResultId">
                    <input type="hidden" name="level" id="modalLevel">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Bin / SKU</label>
                        <div id="modalProductInfo" class="fw-bold fs-6 text-dark p-2 bg-light rounded">-</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-secondary">Hasil Qty (Recount <span id="modalLevelText"></span>)</label>
                        <input type="number" step="0.01" name="recount_qty" required class="form-control" placeholder="Masukkan jumlah hasil recount">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" onclick="closeRecountModal()" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Recount
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .bg-purple {
        background-color: #8b5cf6 !important;
        color: #fff !important;
    }
    .table-striped > tbody > tr:nth-of-type(odd) > * {
        --bs-table-accent-bg: #f8fafc;
    }
    .table-hover > tbody > tr:hover > * {
        --bs-table-hover-bg: #f1f5f9;
    }
    .pagination-wrapper svg { width: 1.25rem; height: 1.25rem; }
    .pagination-wrapper nav > div { margin-top: 10px; }
    .pagination-wrapper p { font-size: 0.875rem; color: #64748b; }
    .font-monospace {
        font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
    }
    .modal-content {
        border: none;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    }
</style>
@endpush

@push('scripts')
<script>
    function openRecountModal(resultId, level, binCode, skuCode) {
        document.getElementById('modalResultId').value = resultId;
        document.getElementById('modalLevel').value = level;
        document.getElementById('modalLevelText').innerText = level;
        document.getElementById('modalProductInfo').innerText = binCode + ' - ' + skuCode;
        
        const modal = new bootstrap.Modal(document.getElementById('recountModal'));
        modal.show();
    }

    function closeRecountModal() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('recountModal'));
        if (modal) modal.hide();
    }

    document.getElementById('selectAllRecount').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.recount-checkbox:not([disabled])');
        for (let checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeRecountModal();
        }
    });
</script>
@endpush