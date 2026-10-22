@extends('layouts.app')

@section('title', 'Team Assignments (Bulk/Lorong)')
@section('page_title', 'Master Penugasan - Mode Lorong')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        min-height: 42px;
    }
    .lorong-row:hover {
        background-color: #f8fafc;
    }
</style>
@endpush

@section('content')
@if(session('success'))
    <div class="alert alert-success mb-4" style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div class="content-panel" style="margin-top: 20px;">
    @if(!$session)
        <x-empty-state 
            title="Tidak Ada Sesi Opname yang Aktif atau Draft"
            description="Silakan buat atau aktifkan sesi opname terlebih dahulu sebelum membagi tugas assignment tim."
            :actionUrl="route('sodc.sessions.index')"
            actionText="Buka Manajemen Session"
            actionIcon="fas fa-calendar-alt"
        />
    @else
        <!-- TABS HEADER -->
        <style>
            .custom-tabs {
                border-bottom: 2px solid #E6E9ED;
                margin-bottom: 20px;
                display: flex;
            }
            .custom-tabs .nav-link {
                color: #73879C;
                border: none;
                border-bottom: 3px solid transparent;
                border-radius: 0;
                padding: 12px 24px;
                font-weight: 600;
                font-size: 0.95rem;
                transition: all 0.2s;
                background: transparent !important;
                margin-bottom: -2px; /* Pull down to cover the bottom border */
            }
            .custom-tabs .nav-link:hover {
                color: #2A3F54;
                border-bottom-color: #D9DEE4;
            }
            .custom-tabs .nav-link.active {
                color: #1ABB9C !important;
                border-bottom-color: #1ABB9C !important;
            }
        </style>
        
        <ul class="nav custom-tabs" id="assignmentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="team-ab-tab" data-bs-toggle="pill" data-bs-target="#team-ab" type="button" role="tab" aria-controls="team-ab" aria-selected="true">
                    <i class="fas fa-users me-2"></i> Pengaturan Area (Tim Penghitung)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="recount-tab" data-bs-toggle="pill" data-bs-target="#recount" type="button" role="tab" aria-controls="recount" aria-selected="false">
                    <i class="fas fa-users-cog me-2"></i> Tim Recount Global
                </button>
            </li>
        </ul>

        <div class="tab-content" id="assignmentTabsContent">
            <!-- TAB: TEAM A & B -->
            <div class="tab-pane fade show active" id="team-ab" role="tabpanel" aria-labelledby="team-ab-tab">
                
                <!-- Toggle Breakdown Level -->
                <div style="background: #ffffff; padding: 15px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 20px;">
                    <h6 style="margin: 0; font-weight: 700; color: #475569;"><i class="fas fa-layer-group me-2"></i>Level Pengelompokan:</h6>
                    <div class="btn-group shadow-sm">
                        <a href="?breakdown=warehouse" class="btn btn-sm {{ request('breakdown', 'warehouse') == 'warehouse' ? 'btn-primary' : 'btn-outline-secondary' }}">Hanya Gudang</a>
                        <a href="?breakdown=zone" class="btn btn-sm {{ request('breakdown') == 'zone' ? 'btn-primary' : 'btn-outline-secondary' }}">Per Zona</a>
                        <a href="?breakdown=ganjil_genap" class="btn btn-sm {{ request('breakdown') == 'ganjil_genap' ? 'btn-primary' : 'btn-outline-secondary' }}">Per Sisi (Ganjil/Genap)</a>
                    </div>
                </div>

                <form method="POST" action="{{ route('sodc.assignments_bulk.store') }}">
            @csrf
            <input type="hidden" name="session_id" value="{{ $session->id }}">

            <!-- Form Penugasan di Atas -->
            <div style="background: #f1f5f9; padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #e2e8f0;">
                <h5 style="font-size: 1rem; font-weight: 700; margin-bottom: 15px; color: #1e293b;"><i class="fas fa-users"></i> Set Tim untuk Lorong yang Dicentang</h5>
                <div class="row">
                    <div class="{{ $session->mode == 'DOUBLE' ? 'col-md-5' : 'col-md-10' }}">
                        <label class="form-label font-weight-bold text-dark mb-2">
                            {{ $session->mode == 'DOUBLE' ? 'Team A' : 'Tim Penghitung (Single)' }}
                        </label>
                        <select name="team_a_users[]" class="form-select select2-multiple" multiple="multiple" style="width: 100%;">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($session->mode == 'DOUBLE')
                    <div class="col-md-5">
                        <label class="form-label font-weight-bold text-dark mb-2">Team B</label>
                        <select name="team_b_users[]" class="form-select select2-multiple" multiple="multiple" style="width: 100%;">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-2" style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn btn-primary w-100" style="height: 42px; font-weight: 600;">
                            <i class="fas fa-save"></i> Terapkan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabel Pemilihan Lorong -->
            <div class="table-container">
                <table class="premium-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="checkAll" style="transform: scale(1.2);">
                            </th>
                            <th>Warehouse</th>
                            <th>Level Area</th>
                            <th style="text-align: right;">Total Bin</th>
                            <th style="text-align: right;">Total SKU</th>
                            <th>{{ $session->mode == 'DOUBLE' ? 'Current Team A' : 'Tim Penugasan' }}</th>
                            @if($session->mode == 'DOUBLE')
                                <th>Current Team B</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($aisles as $aisle)
                            @php
                                $key = $aisle->warehouse_id . '|' . $aisle->assign_key;
                                $teamA = isset($assignmentsMap[$key]['TEAM_A']) ? $assignmentsMap[$key]['TEAM_A'] : [];
                                $teamB = isset($assignmentsMap[$key]['TEAM_B']) ? $assignmentsMap[$key]['TEAM_B'] : [];
                            @endphp
                            <tr class="lorong-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="text-align: center; padding: 12px;">
                                    <input type="checkbox" name="selected_aisles[]" value="{{ $key }}" class="aisle-checkbox" style="transform: scale(1.2);">
                                </td>
                                <td style="font-weight: 600; color: #0f172a;">{{ $aisle->warehouse_code }}</td>
                                <td style="font-weight: 700; color: #0284c7; font-size: 1.05rem;">
                                    <span style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; color: #334155;">{{ $aisle->display_level }}</span>
                                </td>
                                <td style="text-align: right; font-weight: bold;">{{ $aisle->total_bins }}</td>
                                <td style="text-align: right;">{{ $aisle->total_sku }}</td>
                                <td>
                                    @if(count($teamA) > 0)
                                        @foreach($teamA as $a)
                                            <span style="display: inline-block; background: #e0f2fe; color: #0284c7; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; margin-bottom: 2px;">{{ $a->user->full_name ?? 'Unknown' }}</span>
                                        @endforeach
                                    @else
                                        <span style="color: #94a3b8; font-style: italic; font-size: 0.8rem;">Belum ada</span>
                                    @endif
                                </td>
                                @if($session->mode == 'DOUBLE')
                                <td>
                                    @if(count($teamB) > 0)
                                        @foreach($teamB as $b)
                                            <span style="display: inline-block; background: #fce7f3; color: #be185d; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; margin-bottom: 2px;">{{ $b->user->full_name ?? 'Unknown' }}</span>
                                        @endforeach
                                    @else
                                        <span style="color: #94a3b8; font-style: italic; font-size: 0.8rem;">Belum ada</span>
                                    @endif
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 30px; color: #64748b;">
                                    Tidak ada data Lorong yang terdeteksi dari Master Reference pada Sesi ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </div> <!-- End Tab Team A&B -->

    <!-- TAB: RECOUNT -->
    <div class="tab-pane fade" id="recount" role="tabpanel" aria-labelledby="recount-tab">
        <div class="glass-card p-5 mb-5" style="border-left: 5px solid #f59e0b; background: linear-gradient(to right, #fffbeb, #ffffff);">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 48px; height: 48px;">
                    <i class="fas fa-users-cog fs-5"></i>
                </div>
                <div>
                    <h5 class="font-weight-bold mb-1" style="color: #b45309;">Konfigurasi Tim Recount Global</h5>
                    <p class="text-muted small mb-0" style="max-width: 800px;">Anggota tim yang di-set di bawah ini akan bertugas khusus untuk melakukan hitung ulang (Recount 1 dan Recount 2). Ketika Anda melempar tugas recount dari halaman hasil, tugas tersebut akan otomatis masuk ke aplikasi Flutter mereka semua.</p>
                </div>
            </div>
            
            <form action="{{ route('sodc.assignments.store_recount_team') }}" method="POST" class="mt-4 p-4 bg-white rounded-4 shadow-sm border border-warning-subtle">
                @csrf
                <input type="hidden" name="session_id" value="{{ $session->id }}">
                <div class="row align-items-end">
                    <div class="col-md-10">
                        @php
                            $recountTeam = isset($assignmentsMap['RECOUNT']['TEAM_RECOUNT']) ? $assignmentsMap['RECOUNT']['TEAM_RECOUNT'] : [];
                        @endphp
                        <div class="form-group mb-0">
                            <label class="fw-bold mb-2 text-dark"><i class="fas fa-user-shield me-2 text-warning"></i> Pilih Anggota Tim Recount:</label>
                            <select name="recount_users[]" class="form-select select2-multiple" multiple="multiple" style="width: 100%;">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ in_array($user->id, $recountTeam) ? 'selected' : '' }}>
                                        {{ $user->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-warning w-100 fw-bold shadow-sm" style="height: 42px;">
                            <i class="fas fa-save me-1"></i> Save Team
                        </button>
                    </div>
                </div>
                <small class="text-muted mt-3 d-block"><i class="fas fa-info-circle me-1"></i> Tim recount tidak terikat pada lorong tertentu. Mereka bisa mengakses semua lorong yang butuh dihitung ulang.</small>
            </form>
        </div>
    </div> <!-- End Tab Recount -->
</div> <!-- End Tab Content -->
    @endif
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-multiple').select2({
            placeholder: "Pilih petugas...",
            allowClear: true
        });

        // Check All logic
        $('#checkAll').on('change', function() {
            $('.aisle-checkbox').prop('checked', $(this).is(':checked'));
        });

        // Update Check All if all checkboxes are manually checked
        $('.aisle-checkbox').on('change', function() {
            if ($('.aisle-checkbox:checked').length === $('.aisle-checkbox').length) {
                $('#checkAll').prop('checked', true);
            } else {
                $('#checkAll').prop('checked', false);
            }
        });
    });
</script>
@endpush
