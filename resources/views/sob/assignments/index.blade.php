@extends('layouts.app')
@section('title', 'Penugasan Petugas Sisir (SOB)')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold"><i class="ri-user-shared-line text-primary me-2"></i>Penugasan Petugas Sisir (SOB)</h4>
            <p class="text-muted mb-0 small">Bagi tugas tim (Single, Team A, Team B, atau Recount) ke area cabang.</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#assignModal">
            <i class="ri-user-add-line fs-6"></i> Tugaskan Petugas
        </button>
    </div>

    <!-- Filter Sesi -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="fw-semibold small text-muted">Pilih Sesi Opname:</label>
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
                            <th class="ps-4 py-3">Nama Petugas</th>
                            <th>Area / Zona</th>
                            <th>Peran Tim</th>
                            <th>Status Pengerjaan</th>
                            <th>Waktu Mulai</th>
                            <th>Waktu Selesai</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $a)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $a->user->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $a->user->email ?? '' }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $a->zone->zone_name ?? '-' }}</div>
                                    <small class="text-muted font-monospace">{{ $a->zone->zone_code ?? '' }}</small>
                                </td>
                                <td>
                                    @if($a->team_role == 'TEAM_A')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">TEAM A</span>
                                    @elseif($a->team_role == 'TEAM_B')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle fw-bold">TEAM B</span>
                                    @elseif($a->team_role == 'RECOUNT')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold">RECOUNT / JURI</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">SINGLE</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $a->status }}</span>
                                </td>
                                <td class="small">{{ $a->started_at ? date('H:i', strtotime($a->started_at)) : '-' }}</td>
                                <td class="small">{{ $a->completed_at ? date('H:i', strtotime($a->completed_at)) : '-' }}</td>
                                <td class="text-end pe-4">
                                    <form action="{{ route('sob.assignments.destroy', $a->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Batalkan penugasan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus Penugasan">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="ri-user-unfollow-line fs-2 d-block mb-2 text-secondary"></i>
                                    Belum ada petugas yang ditugaskan pada sesi ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Assign -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('sob.assignments.store') }}" method="POST">
            @csrf
            <input type="hidden" name="session_id" value="{{ $activeSessionId }}">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold"><i class="ri-user-shared-line me-1 text-primary"></i>Tugaskan Petugas ke Area</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Pilih Petugas <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Pilih User --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Area / Zona Sisir <span class="text-danger">*</span></label>
                        <select name="zone_id" class="form-select" required>
                            <option value="">-- Pilih Area --</option>
                            @foreach($zones as $z)
                                <option value="{{ $z->id }}">{{ $z->zone_name }} ({{ $z->zone_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Peran Tim <span class="text-danger">*</span></label>
                        <select name="team_role" class="form-select" required>
                            <option value="TEAM_A" selected>TEAM A (Penyisir Pertama)</option>
                            <option value="TEAM_B">TEAM B (Penyisir Verifikator)</option>
                            <option value="SINGLE">SINGLE TEAM (Hitung Tunggal)</option>
                            <option value="RECOUNT">RECOUNT / JURI</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="ri-check-line me-1"></i>Tugaskan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
