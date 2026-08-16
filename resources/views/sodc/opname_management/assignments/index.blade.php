@extends('layouts.app')

@section('title', 'Team Assignments - SODC')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        border-radius: 1rem;
    }
    .accordion-button:not(.collapsed) {
        background-color: #f8fafc;
        color: #0f172a;
        box-shadow: none;
    }
    .accordion-button:focus {
        box-shadow: none;
    }
    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 0.375rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800" style="font-weight: 700; letter-spacing: -0.5px;">Gudang & Lorong Assignments</h2>
            <p class="text-muted mb-0">Tentukan pengguna mana yang menjadi Team A dan Team B per Lorong/Gudang.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="background: #ecfdf5; color: #065f46;">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$session)
        <div class="glass-card p-5 text-center">
            <div class="mb-3">
                <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
            </div>
            <h4 class="text-gray-800 font-weight-bold">Tidak ada Sesi Aktif/Draft</h4>
            <p class="text-muted">Silakan buat Sesi Opname terlebih dahulu sebelum membagi tugas.</p>
            <a href="{{ route('sodc.sessions.index') }}" class="btn btn-primary mt-2">Buat Sesi</a>
        </div>
    @else
        <div class="glass-card p-4 mb-4">
            <h5 class="font-weight-bold text-primary mb-1"><i class="fas fa-play-circle me-2"></i> Sesi Aktif: {{ $session->session_code }}</h5>
            <span class="badge bg-dark">{{ $session->mode }} MODE</span>
        </div>

        <div class="glass-card p-4 mb-4" style="border-left: 4px solid #f59e0b;">
            <h5 class="font-weight-bold text-warning mb-3"><i class="fas fa-users-cog me-2"></i> Konfigurasi Tim Recount Global</h5>
            <p class="text-muted small">Anggota tim yang di-set di bawah ini akan bertugas khusus untuk melakukan hitung ulang (Recount 1 dan Recount 2). Ketika Anda melempar tugas recount dari halaman hasil, tugas tersebut akan otomatis masuk ke aplikasi Flutter mereka semua.</p>
            
            <form action="{{ route('sodc.assignments.store_recount_team') }}" method="POST">
                @csrf
                <input type="hidden" name="session_id" value="{{ $session->id }}">
                <div class="row align-items-end">
                    <div class="col-md-10">
                        @php
                            $teamRecount = isset($assignmentsMap['GLOBAL_RECOUNT']) ? collect($assignmentsMap['GLOBAL_RECOUNT'])->pluck('user_id')->toArray() : [];
                        @endphp
                        <label class="form-label font-weight-bold">Anggota Tim Recount</label>
                        <select name="team_recount_users[]" class="form-select select2-multiple" multiple="multiple" style="width: 100%;">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ in_array($user->id, $teamRecount) ? 'selected' : '' }}>
                                    {{ $user->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mt-3 mt-md-0">
                        <button type="submit" class="btn btn-warning text-dark font-weight-bold w-100"><i class="fas fa-save me-1"></i> Simpan Tim</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="glass-card p-4">
            <table class="table table-bordered align-middle">
                <thead style="background-color: #f1f5f9;">
                    <tr>
                        <th style="width: 30%">Gudang / Lorong</th>
                        <th style="width: 10%; text-align:center;">Bins/SKU</th>
                        <th style="width: 25%">Team A</th>
                        <th style="width: 25%">Team B</th>
                        <th style="width: 10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Grouping data up to 4 levels
                        $groupedData = collect($warehouses)->groupBy(['warehouse_id', 'zone', 'level', 'ganjil_genap']);
                    @endphp

                    @foreach($groupedData as $wId => $zones)
                        <!-- GUDANG ROW -->
                        @php $whSlug = Str::slug($wId); @endphp
                        <tr style="background-color: #e2e8f0; font-weight: bold;">
                            <td colspan="5" class="py-3">
                                <a class="text-dark text-decoration-none d-flex align-items-center" data-bs-toggle="collapse" href="#collapseWh_{{ $whSlug }}" role="button" aria-expanded="false">
                                    <i class="fas fa-warehouse me-2 text-primary"></i> 
                                    <span style="font-size: 1.1rem;">GUDANG: {{ $wId }}</span>
                                </a>
                            </td>
                        </tr>

                        <tbody class="collapse show" id="collapseWh_{{ $whSlug }}">
                            @foreach($zones as $zName => $levels)
                                <!-- ZONA ROW -->
                                @php $zSlug = $whSlug . '_' . Str::slug($zName); @endphp
                                <tr style="background-color: #f1f5f9; font-weight: bold;">
                                    <td colspan="5" class="py-2 ps-4">
                                        <a class="text-dark text-decoration-none d-flex align-items-center" data-bs-toggle="collapse" href="#collapseZn_{{ $zSlug }}" role="button" aria-expanded="false">
                                            <i class="fas fa-map-marked-alt me-2 text-info"></i> ZONA: {{ $zName }}
                                        </a>
                                    </td>
                                </tr>

                                <tbody class="collapse show" id="collapseZn_{{ $zSlug }}">
                                    @foreach($levels as $lName => $pos)
                                        <!-- LEVEL ROW -->
                                        @php $lSlug = $zSlug . '_' . Str::slug($lName); @endphp
                                        <tr style="background-color: #f8fafc; font-weight: 600;">
                                            <td colspan="5" class="py-2 ps-5 border-bottom-0">
                                                <a class="text-secondary text-decoration-none d-flex align-items-center" data-bs-toggle="collapse" href="#collapseLv_{{ $lSlug }}" role="button" aria-expanded="false">
                                                    <i class="fas fa-layer-group me-2"></i> LEVEL: {{ $lName }}
                                                </a>
                                            </td>
                                        </tr>

                                        <tbody class="collapse show" id="collapseLv_{{ $lSlug }}">
                                            @foreach($pos as $pName => $aisles)
                                                <!-- POSISI (GANJIL/GENAP) ROW -->
                                                @php $pSlug = $lSlug . '_' . Str::slug($pName); @endphp
                                                <tr style="background-color: #ffffff;">
                                                    <td colspan="5" class="py-2" style="padding-left: 4.5rem !important;">
                                                        <a class="text-muted text-decoration-none d-flex align-items-center" data-bs-toggle="collapse" href="#collapsePos_{{ $pSlug }}" role="button" aria-expanded="false">
                                                            <i class="fas fa-arrows-alt-h me-2"></i> POSISI: {{ $pName }}
                                                            <span class="ms-2 badge bg-light text-dark border">{{ count($aisles) }} Lorong</span>
                                                        </a>
                                                    </td>
                                                </tr>

                                                <tbody class="collapse show" id="collapsePos_{{ $pSlug }}">
                                                    @foreach($aisles as $aisle)
                                                        <!-- LORONG ROW (ASSIGNMENT FORM) -->
                                                        @php 
                                                            $rowKey = $wId . '_' . $aisle->aisle;
                                                            $teamA = isset($assignmentsMap[$rowKey]['TEAM_A']) ? collect($assignmentsMap[$rowKey]['TEAM_A'])->pluck('user_id')->toArray() : [];
                                                            $teamB = isset($assignmentsMap[$rowKey]['TEAM_B']) ? collect($assignmentsMap[$rowKey]['TEAM_B'])->pluck('user_id')->toArray() : [];
                                                        @endphp
                                                        <tr>
                                                            <td style="padding-left: 6rem !important;">
                                                                <i class="fas fa-boxes me-2 text-primary"></i> <strong>Lorong: {{ $aisle->aisle }}</strong>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge bg-light text-dark border">{{ $aisle->total_bins }} Bins</span><br>
                                                                <small class="text-muted">{{ $aisle->total_sku }} SKU</small>
                                                            </td>
                                                            <form action="{{ route('sodc.assignments.store') }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="session_id" value="{{ $session->id }}">
                                                                <input type="hidden" name="warehouse_id" value="{{ $wId }}">
                                                                <input type="hidden" name="aisle" value="{{ $aisle->aisle }}">
                                                                
                                                                <td>
                                                                    <select name="team_a_users[]" class="form-select select2-multiple" multiple="multiple" style="width: 100%;">
                                                                        @foreach($users as $user)
                                                                            <option value="{{ $user->id }}" {{ in_array($user->id, $teamA) ? 'selected' : '' }}>
                                                                                {{ $user->full_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <select name="team_b_users[]" class="form-select select2-multiple" multiple="multiple" style="width: 100%;">
                                                                        @foreach($users as $user)
                                                                            <option value="{{ $user->id }}" {{ in_array($user->id, $teamB) ? 'selected' : '' }}>
                                                                                {{ $user->full_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <button type="submit" class="btn btn-success btn-sm w-100"><i class="fas fa-save"></i> Save</button>
                                                                </td>
                                                            </form>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            @endforeach
                                        </tbody>
                                    @endforeach
                                </tbody>
                            @endforeach
                        </tbody>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<!-- jQuery required for Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-multiple').select2({
            placeholder: "Pilih nama user...",
            allowClear: true
        });
    });
</script>
@endpush