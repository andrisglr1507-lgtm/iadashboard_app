@extends('layouts.app')
@section('title', 'Master Area & Zona Cabang (SOB)')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold"><i class="ri-map-pin-range-line text-primary me-2"></i>Area & Zona Cabang (SOB)</h4>
            <p class="text-muted mb-0 small">Daftar area fisik cabang yang menjadi jalur penyisiran opname.</p>
        </div>
        <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#createZoneModal">
            <i class="ri-add-line fs-6"></i> Tambah Area / Zona
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
                            <th class="ps-4 py-3">Kode Cabang</th>
                            <th>Kode Zona</th>
                            <th>Nama Area / Zona</th>
                            <th>Tipe Area</th>
                            <th>Keterangan</th>
                            <th>Titik Sisir</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($zones as $zone)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">{{ $zone->branch_code }}</td>
                                <td><span class="badge bg-secondary bg-opacity-10 text-secondary border font-monospace">{{ $zone->zone_code }}</span></td>
                                <td class="fw-semibold text-dark">{{ $zone->zone_name }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $zone->zone_type }}
                                    </span>
                                </td>
                                <td class="small text-muted">{{ $zone->description ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                                        {{ $zone->sweep_headers_count ?? 0 }} Tag
                                    </span>
                                </td>
                                <td>
                                    @if($zone->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">AKTIF</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">NONAKTIF</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <form action="{{ route('sob.zones.destroy', $zone->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus area ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus Area">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="ri-map-pin-line fs-2 d-block mb-2 text-secondary"></i>
                                    Belum ada master area cabang. Silakan klik tombol <b>Tambah Area / Zona</b>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($zones->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $zones->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Create Zone -->
<div class="modal fade" id="createZoneModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('sob.zones.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold"><i class="ri-map-pin-add-line me-1 text-primary"></i>Tambah Area / Zona Cabang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Kode Cabang <span class="text-danger">*</span></label>
                        <input type="text" name="branch_code" class="form-control text-uppercase" placeholder="Contoh: CAB-BDG" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Kode Area/Zona <span class="text-danger">*</span></label>
                        <input type="text" name="zone_code" class="form-control text-uppercase font-monospace" placeholder="Contoh: GUD-01, DISP-A, RET-01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Area Fisik <span class="text-danger">*</span></label>
                        <input type="text" name="zone_name" class="form-control" placeholder="Contoh: Gudang Belakang, Display Toko Tengah" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Kategori Area <span class="text-danger">*</span></label>
                        <select name="zone_type" class="form-select" required>
                            <option value="GUDANG" selected>Gudang Cabang</option>
                            <option value="DISPLAY">Display Toko / Showroom</option>
                            <option value="RETUR_BS">Ruang Retur / Bad Stock</option>
                            <option value="TRANSIT">Area Transit / Bongkar Muat</option>
                            <option value="OTHER">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Deskripsi / Batasan Letak</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Letak fisik lorong/tiang..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i>Simpan Area</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
