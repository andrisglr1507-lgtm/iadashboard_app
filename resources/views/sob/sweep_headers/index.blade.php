@extends('layouts.app')
@section('title', 'Tracking Titik Sisir / Tags (SOB)')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold"><i class="ri-radar-line text-primary me-2"></i>Tracking Titik Sisir / Stiker Tag (SOB)</h4>
            <p class="text-muted mb-0 small">Pantau status fisik setiap stiker tag yang disisir oleh Team A dan Team B secara real-time.</p>
        </div>
    </div>

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
                                {{ $s->session_code }} - {{ $s->session_name }}
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
                            <th class="ps-4 py-3">No Tag (Stiker)</th>
                            <th>Area / Zona</th>
                            <th>Penanda Fisik</th>
                            <th>Team A Status</th>
                            <th>Team B Status</th>
                            <th>Hasil Komparasi</th>
                            <th>Jumlah Item Scan</th>
                            <th class="text-end pe-4">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($headers as $h)
                            <tr>
                                <td class="ps-4 fw-bold font-monospace text-primary">
                                    <i class="ri-price-tag-3-line me-1"></i>{{ $h->tag_number }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $h->zone->zone_name ?? '-' }}</span>
                                </td>
                                <td class="small text-muted">{{ $h->location_marker ?? '-' }}</td>
                                <td>
                                    @if($h->status_team_a == 'COMPLETED')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="ri-check-line me-1"></i>SELESAI ({{ $h->teamAUser->name ?? '-' }})
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border">PENDING</span>
                                    @endif
                                </td>
                                <td>
                                    @if($h->status_team_b == 'COMPLETED')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">
                                            <i class="ri-check-line me-1"></i>SELESAI ({{ $h->teamBUser->name ?? '-' }})
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border">PENDING</span>
                                    @endif
                                </td>
                                <td>
                                    @if($h->comparison_status == 'MATCH')
                                        <span class="badge bg-success text-white px-2 py-1"><i class="ri-checkbox-circle-fill me-1"></i>MATCH</span>
                                    @elseif($h->comparison_status == 'MISMATCH')
                                        <span class="badge bg-danger text-white px-2 py-1"><i class="ri-error-warning-fill me-1"></i>MISMATCH</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">MENUNGGU B</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $h->counts_count ?? 0 }} Barcode</span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('sob.sweep_headers.show', $h->id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="ri-eye-line me-1"></i>Lihat Rincian
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="ri-radar-line fs-2 d-block mb-2 text-secondary"></i>
                                    Belum ada data stiker tag yang disisir pada sesi ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($headers->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $headers->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
