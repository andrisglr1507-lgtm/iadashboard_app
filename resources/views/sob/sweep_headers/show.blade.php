@extends('layouts.app')
@section('title', 'Detail Titik Sisir ' . $header->tag_number)

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold">
                <i class="ri-price-tag-3-line text-primary me-2"></i>Rincian Titik Sisir: {{ $header->tag_number }}
            </h4>
            <p class="text-muted mb-0 small">
                Area: <b>{{ $header->zone->zone_name ?? '-' }}</b> • Penanda Fisik: <b>{{ $header->location_marker ?? '-' }}</b>
            </p>
        </div>
        <a href="{{ route('sob.sweep_headers.index', ['session_id' => $header->session_id]) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ri-arrow-left-line me-1"></i>Kembali
        </a>
    </div>

    <!-- Ringkasan Komparasi -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="ri-scales-3-line text-primary me-2"></i>Hasil Komparasi Team A vs Team B di Tag Ini</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase fw-semibold">
                        <tr>
                            <th class="ps-4 py-3">Produk / SKU</th>
                            <th>Kondisi</th>
                            <th class="text-center">Qty Team A</th>
                            <th class="text-center">Qty Team B</th>
                            <th class="text-center">Selisih</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Qty Disahkan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($header->tagResults as $tr)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $tr->product->product_name ?? $tr->product_id }}</div>
                                    <small class="text-muted font-monospace">SKU: {{ $tr->product_id }}</small>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $tr->stock_condition }}</span></td>
                                <td class="text-center fw-bold text-primary">{{ $tr->team_a_qty ?? '-' }}</td>
                                <td class="text-center fw-bold text-info">{{ $tr->team_b_qty ?? '-' }}</td>
                                <td class="text-center font-monospace">{{ $tr->diff_qty ?? '0' }}</td>
                                <td class="text-center">
                                    @if($tr->is_match)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">MATCH</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">MISMATCH</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold text-success">{{ $tr->final_approved_qty }} Pcs</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada hasil komparasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Riwayat Log Scan Fisik -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="ri-history-line text-secondary me-2"></i>Log Scan Fisik Masuk</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-4 py-2">Waktu</th>
                            <th>Tim</th>
                            <th>Petugas</th>
                            <th>SKU</th>
                            <th>Barcode</th>
                            <th>Qty Karton</th>
                            <th>Qty Pcs</th>
                            <th>Total Pcs</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($header->counts as $c)
                            <tr>
                                <td class="ps-4 small text-muted">{{ date('H:i:s', strtotime($c->created_at)) }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $c->team_role }}</span>
                                </td>
                                <td>{{ $c->counter->name ?? '-' }}</td>
                                <td class="font-monospace fw-semibold">{{ $c->product_id }}</td>
                                <td class="font-monospace small text-muted">{{ $c->scanned_barcode ?? '-' }}</td>
                                <td>{{ $c->qty_karton }}</td>
                                <td>{{ $c->qty_pcs }}</td>
                                <td class="fw-bold text-dark">{{ $c->final_qty }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada log scan fisik.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
