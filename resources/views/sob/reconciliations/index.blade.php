@extends('layouts.app')
@section('title', 'Rekonsiliasi Stok Cabang (SOB)')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold"><i class="ri-file-chart-line text-primary me-2"></i>Rekonsiliasi Stok Cabang (SOB)</h4>
            <p class="text-muted mb-0 small">Perbandingan Total Fisik Cabang dari seluruh titik sisir vs Saldo Buku Sistem WMS/ERP.</p>
        </div>
        @if($activeSessionId)
            <form action="{{ route('sob.reconciliations.generate', $activeSessionId) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                    <i class="ri-calculator-line me-1"></i>Kompilasi Ulang Rekonsiliasi
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Sesi -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="fw-semibold small text-muted">Sesi Opname:</label>
                </div>
                <div class="col-md-4">
                    <select name="session_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($sessions as $s)
                            <option value="{{ $s->id }}" {{ $activeSessionId == $s->id ? 'selected' : '' }}>
                                {{ $s->session_code }} - {{ $s->session_name }} ({{ $s->branch_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase fw-semibold">
                        <tr>
                            <th class="ps-4 py-3">Kode SKU</th>
                            <th>Nama Barang</th>
                            <th class="text-center">Total Fisik (Sisir)</th>
                            <th class="text-center">Saldo Sistem</th>
                            <th class="text-center">Selisih Qty</th>
                            <th class="text-center">Status Varian</th>
                            <th class="text-end">Nilai Selisih (Rp)</th>
                            <th class="text-center">Status Approval</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reconciliations as $rec)
                            <tr>
                                <td class="ps-4 font-monospace fw-bold text-dark">{{ $rec->product_id }}</td>
                                <td>{{ $rec->product->product_name ?? '-' }}</td>
                                <td class="text-center fw-bold text-primary">{{ number_format($rec->physical_qty) }}</td>
                                <td class="text-center fw-bold text-secondary">{{ number_format($rec->system_qty) }}</td>
                                <td class="text-center font-monospace fw-bold {{ $rec->diff_qty > 0 ? 'text-success' : ($rec->diff_qty < 0 ? 'text-danger' : '') }}">
                                    {{ $rec->diff_qty > 0 ? '+'.number_format($rec->diff_qty) : number_format($rec->diff_qty) }}
                                </td>
                                <td class="text-center">
                                    @if($rec->variance_status == 'MATCH')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">MATCH</span>
                                    @elseif($rec->variance_status == 'OVER')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">OVER (LEBIH)</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">SHORT (KURANG)</span>
                                    @endif
                                </td>
                                <td class="text-end font-monospace">
                                    Rp {{ number_format($rec->total_diff_value, 2, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($rec->status == 'APPROVED')
                                        <span class="badge bg-success text-white">APPROVED</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">DRAFT</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @if($rec->status != 'APPROVED')
                                        <form action="{{ route('sob.reconciliations.approve', $rec->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm" onclick="return confirm('Setujui rekonsiliasi item ini?')">
                                                <i class="ri-check-line"></i> Approve
                                            </button>
                                        </form>
                                    @else
                                        <span class="small text-muted">Disetujui {{ $rec->approver->name ?? '' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="ri-calculator-line fs-2 d-block mb-2 text-secondary"></i>
                                    Belum ada data rekonsiliasi. Silakan klik tombol <b>Kompilasi Ulang Rekonsiliasi</b> di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($reconciliations->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $reconciliations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
