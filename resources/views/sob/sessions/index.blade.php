@extends('layouts.app')
@section('title', 'Sesi Opname Cabang (SOB)')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold"><i class="ri-store-2-line text-primary me-2"></i>Sesi Opname Cabang (SOB)</h4>
            <p class="text-muted mb-0 small">Kelola event stock opname cabang metode menyisir (Single & Double Team).</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#createSessionModal">
            <i class="ri-add-line fs-6"></i> Buat Sesi Baru
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary small text-uppercase fw-semibold">
                        <tr>
                            <th class="ps-4 py-3">Kode Sesi</th>
                            <th>Nama Sesi</th>
                            <th>Cabang</th>
                            <th>Tanggal</th>
                            <th>Mode Hitung</th>
                            <th>Titik Sisir (Tags)</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                            <tr>
                                <td class="ps-4 fw-bold text-dark font-monospace">{{ $session->session_code }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $session->session_name }}</div>
                                    <small class="text-muted">Dibuat oleh: {{ $session->creator->name ?? 'System' }}</small>
                                </td>
                                <td><span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ $session->branch_code }}</span></td>
                                <td>{{ date('d M Y', strtotime($session->session_date)) }}</td>
                                <td>
                                    @if($session->count_mode == 'DOUBLE')
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle fw-semibold">
                                            <i class="ri-team-line me-1"></i>DOUBLE (A & B)
                                        </span>
                                    @else
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle fw-semibold">
                                            <i class="ri-user-line me-1"></i>SINGLE TEAM
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $session->sweep_headers_count ?? 0 }} Titik
                                    </span>
                                </td>
                                <td>
                                    @if($session->status == 'ACTIVE')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="ri-record-circle-line me-1"></i>AKTIF</span>
                                    @elseif($session->status == 'CLOSED')
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">SELESAI</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">DRAFT</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        @if($session->status == 'DRAFT')
                                            <form action="{{ route('sob.sessions.start', $session->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success btn-sm me-1" title="Aktifkan Sesi">
                                                    <i class="ri-play-line"></i> Mulai
                                                </button>
                                            </form>
                                        @elseif($session->status == 'ACTIVE')
                                            <form action="{{ route('sob.sessions.close', $session->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm me-1" title="Tutup Sesi" onclick="return confirm('Tutup sesi opname ini?')">
                                                    <i class="ri-stop-line"></i> Tutup
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('sob.sweep_headers.index', ['session_id' => $session->id]) }}" class="btn btn-light btn-sm border me-1" title="Pantau Sisir">
                                            <i class="ri-radar-line text-primary"></i>
                                        </a>
                                        <a href="{{ route('sob.tag_results.index', ['session_id' => $session->id]) }}" class="btn btn-light btn-sm border me-1" title="Komparasi A/B">
                                            <i class="ri-compare-arrows-line text-info"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="ri-inbox-line fs-2 d-block mb-2 text-secondary"></i>
                                    Belum ada sesi opname cabang. Silakan klik tombol <b>Buat Sesi Baru</b>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($sessions->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $sessions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Create Session -->
<div class="modal fade" id="createSessionModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('sob.sessions.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold"><i class="ri-add-circle-line me-1 text-primary"></i>Buat Sesi Opname Cabang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Kode Cabang <span class="text-danger">*</span></label>
                        <input type="text" name="branch_code" class="form-control text-uppercase" placeholder="Contoh: CAB-BDG / CAB-01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Sesi <span class="text-danger">*</span></label>
                        <input type="text" name="session_name" class="form-control" placeholder="Contoh: Opname Bulanan Cabang Bandung" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                        <input type="date" name="session_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Mode Hitung <span class="text-danger">*</span></label>
                        <select name="count_mode" class="form-select" required>
                            <option value="DOUBLE" selected>DOUBLE TEAM (Team A & Team B - Rekomendasi)</option>
                            <option value="SINGLE">SINGLE TEAM (Hitung Tunggal)</option>
                        </select>
                        <small class="text-muted d-block mt-1">Pada mode DOUBLE, sistem otomatis mencocokkan Qty Team A vs Qty Team B per stiker tag.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Catatan Tambahan</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan instruksi audit lapangan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i>Simpan Sesi</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
