@extends('layouts.app')
@section('title', 'Hasil Komparasi Tag (A vs B)')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold"><i class="ri-compare-arrows-line text-primary me-2"></i>Hasil Komparasi Tag (Team A vs Team B)</h4>
            <p class="text-muted mb-0 small">Validasi kesesuaian fisik antara penyisir Team A dan verifikator Team B serta penyelesaian recount.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="fw-semibold small text-muted">Sesi:</label>
                </div>
                <div class="col-md-3">
                    <select name="session_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($sessions as $s)
                            <option value="{{ $s->id }}" {{ $activeSessionId == $s->id ? 'selected' : '' }}>
                                {{ $s->session_code }} - {{ $s->session_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto ms-md-3">
                    <label class="fw-semibold small text-muted">Status:</label>
                </div>
                <div class="col-auto">
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('sob.tag_results.index', ['session_id' => $activeSessionId]) }}" class="btn btn-outline-secondary {{ empty($filterStatus) ? 'active' : '' }}">Semua</a>
                        <a href="{{ route('sob.tag_results.index', ['session_id' => $activeSessionId, 'status' => 'MATCH']) }}" class="btn btn-outline-success {{ $filterStatus == 'MATCH' ? 'active' : '' }}">Match</a>
                        <a href="{{ route('sob.tag_results.index', ['session_id' => $activeSessionId, 'status' => 'MISMATCH']) }}" class="btn btn-outline-danger {{ $filterStatus == 'MISMATCH' ? 'active' : '' }}">Mismatch (Selisih)</a>
                    </div>
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
                            <th class="ps-4 py-3">Tag Stiker</th>
                            <th>Area / Zona</th>
                            <th>SKU & Nama Barang</th>
                            <th class="text-center">Team A</th>
                            <th class="text-center">Team B</th>
                            <th class="text-center">Selisih</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Recount</th>
                            <th class="text-center">Angka Sah</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $r)
                            <tr>
                                <td class="ps-4 font-monospace fw-bold text-dark">{{ $r->tag_number }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $r->header->zone->zone_name ?? '-' }}</span></td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $r->product->product_name ?? $r->product_id }}</div>
                                    <small class="text-muted font-monospace">{{ $r->product_id }}</small>
                                </td>
                                <td class="text-center fw-bold text-primary">{{ $r->team_a_qty ?? '-' }}</td>
                                <td class="text-center fw-bold text-info">{{ $r->team_b_qty ?? '-' }}</td>
                                <td class="text-center font-monospace {{ ($r->diff_qty ?? 0) != 0 ? 'text-danger fw-bold' : '' }}">
                                    {{ $r->diff_qty ?? '0' }}
                                </td>
                                <td class="text-center">
                                    @if($r->is_match)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">MATCH</span>
                                    @else
                                        <span class="badge bg-danger text-white">SELISIH</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($r->recount_qty !== null)
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-bold">{{ $r->recount_qty }} Pcs</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold text-success">{{ $r->final_approved_qty }} Pcs</td>
                                <td class="text-end pe-4">
                                    @if(!$r->is_match)
                                        <button type="button" class="btn btn-warning btn-sm text-dark fw-semibold" data-bs-toggle="modal" data-bs-target="#recountModal{{ $r->id }}">
                                            <i class="ri-refresh-line me-1"></i>Input Recount
                                        </button>

                                        <!-- Modal Recount -->
                                        <div class="modal fade text-start" id="recountModal{{ $r->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <form action="{{ route('sob.tag_results.recount') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $r->id }}">
                                                    <div class="modal-content border-0 shadow">
                                                        <div class="modal-header border-bottom">
                                                            <h5 class="modal-title fw-bold text-dark">Recount Tag {{ $r->tag_number }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="small text-muted mb-3">
                                                                Barang: <b>{{ $r->product->product_name ?? $r->product_id }}</b><br>
                                                                Hitungan Team A: <b>{{ $r->team_a_qty }} Pcs</b> vs Team B: <b>{{ $r->team_b_qty }} Pcs</b>.
                                                            </p>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold small">Hasil Hitung Recount (Juri/Auditor) <span class="text-danger">*</span></label>
                                                                <input type="number" name="recount_qty" class="form-control form-control-lg text-center fw-bold" required min="0">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold small">Catatan Pengesahan</label>
                                                                <input type="text" name="approval_notes" class="form-control" placeholder="Alasan selisih / verifikasi fisik...">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-top">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Sahkan Nilai Recount</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-success small"><i class="ri-check-line me-1"></i>Sah</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="ri-checkbox-multiple-line fs-2 d-block mb-2 text-secondary"></i>
                                    Tidak ada data perbandingan pada kriteria ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($results->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $results->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
