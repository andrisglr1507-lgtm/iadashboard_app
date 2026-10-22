@extends('layouts.app')

@section('title', 'Team Assignments - SODC')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        border-radius: 1rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .glass-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
    }
    .accordion-button:not(.collapsed) {
        background-color: transparent;
        color: inherit;
        box-shadow: none;
    }
    .accordion-button:focus {
        box-shadow: none;
    }
    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 0.5rem;
        border: 1px solid #cbd5e1;
        padding: 4px;
    }
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        min-height: 42px;
    }
    
    /* Table Hierarchy Styles */
    .row-warehouse { background: linear-gradient(90deg, #1e293b, #334155); color: white; }
    .row-zone { background: #e2e8f0; color: #1e293b; border-left: 4px solid #3b82f6; }
    .row-level { background: #f1f5f9; color: #334155; border-left: 4px solid #0ea5e9; }
    .row-posisi { background: #f8fafc; color: #475569; border-left: 4px solid #94a3b8; }
    .row-lorong { background: #ffffff; transition: background 0.2s; }
    .row-lorong:hover { background: #f8fafc; }
    
    .table-custom {
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .table-custom thead th {
        background: #0f172a;
        color: white;
        border: none;
        padding: 1rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        font-size: 0.8rem;
    }
    .table-custom td {
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
    }
    .collapse-icon {
        transition: transform 0.3s ease;
    }
    a[aria-expanded="true"] .collapse-icon {
        transform: rotate(90deg);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="background: #ecfdf5; color: #065f46;">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$session)
        <div class="glass-card" style="padding: 40px 20px;">
            <x-empty-state 
                title="Tidak Ada Sesi Aktif / Draft"
                description="Silakan buat atau pilih Sesi Opname terlebih dahulu sebelum membagi tugas hitung."
                :actionUrl="route('sodc.sessions.index')"
                actionText="Buat / Pilih Sesi"
                actionIcon="fas fa-calendar-plus"
            />
        </div>
    @else
        <div class="glass-card p-4 mb-4" style="display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);">
            <div>
                <p class="text-muted small mb-0 text-uppercase fw-bold">Active Session</p>
                <h4 class="font-weight-bold text-primary mb-0"><i class="fas fa-play-circle me-2"></i> {{ $session->session_code }}</h4>
            </div>
            <span class="badge" style="background: linear-gradient(90deg, #0f172a, #334155); font-size: 0.9rem; padding: 10px 16px; border-radius: 8px;">
                <i class="fas fa-layer-group me-1"></i> {{ $session->mode }} MODE
            </span>
        </div>

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
        <!-- TABS HEADER -->
        <ul class="nav custom-tabs" id="assignmentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="team-ab-tab" data-bs-toggle="pill" data-bs-target="#team-ab" type="button" role="tab" aria-controls="team-ab" aria-selected="true">
                    <i class="fas fa-users me-2"></i> Pengaturan Lorong (Team A & B)
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
                <div class="glass-card p-0 mb-5">
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th style="width: 25%">Gudang / Lorong</th>
                            <th style="width: 15%; text-align:center;">Informasi</th>
                            <th style="width: 25%">Team A (Blind)</th>
                            <th style="width: 25%">Team B (Blind)</th>
                            <th style="width: 10%; text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warehouses as $wh)
                            <!-- GUDANG ROW (ASSIGNMENT FORM) -->
                            @php 
                                $wId = $wh->warehouse_id;
                                $rowKey = $wId;
                                $teamA = isset($assignmentsMap[$rowKey]['TEAM_A']) ? collect($assignmentsMap[$rowKey]['TEAM_A'])->pluck('user_id')->toArray() : [];
                                $teamB = isset($assignmentsMap[$rowKey]['TEAM_B']) ? collect($assignmentsMap[$rowKey]['TEAM_B'])->pluck('user_id')->toArray() : [];
                            @endphp
                            <tr class="row-warehouse">
                                <td class="py-3 px-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 36px; height: 36px;">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div>
                                            <span class="text-muted small d-block" style="line-height: 1;">Gudang</span>
                                            <strong class="text-dark fs-6">{{ $wh->warehouse_code }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-inline-flex flex-column gap-1">
                                        <span class="badge bg-light text-primary border border-primary-subtle rounded-pill px-3">{{ $wh->total_bins }} Bins</span>
                                        <span class="badge bg-light text-secondary border rounded-pill px-3">{{ $wh->total_sku }} SKU</span>
                                    </div>
                                </td>
                                <form action="{{ route('sodc.assignments.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="session_id" value="{{ $session->id }}">
                                    <input type="hidden" name="warehouse_id" value="{{ $wId }}">
                                    
                                    <td class="px-3 align-middle">
                                        <select name="team_a_users[]" class="form-select select2-multiple" multiple="multiple" style="width: 100%;">
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ in_array($user->id, $teamA) ? 'selected' : '' }}>
                                                    {{ $user->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 align-middle">
                                        <select name="team_b_users[]" class="form-select select2-multiple" multiple="multiple" style="width: 100%;">
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ in_array($user->id, $teamB) ? 'selected' : '' }}>
                                                    {{ $user->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-center px-3 align-middle">
                                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3 shadow-sm" style="padding: 8px;">
                                            <i class="fas fa-save me-1"></i> Save
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
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
                                    $teamRecount = isset($assignmentsMap['GLOBAL_RECOUNT']) ? collect($assignmentsMap['GLOBAL_RECOUNT'])->pluck('user_id')->toArray() : [];
                                @endphp
                                <label class="form-label font-weight-bold text-dark mb-2"><i class="fas fa-user-check me-2 text-warning"></i>Pilih Anggota Tim Recount</label>
                                <select name="team_recount_users[]" class="form-select select2-multiple" multiple="multiple" style="width: 100%;">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ in_array($user->id, $teamRecount) ? 'selected' : '' }}>
                                            {{ $user->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mt-3 mt-md-0">
                                <button type="submit" class="btn btn-warning text-dark font-weight-bold w-100 py-2 rounded-3 shadow-sm"><i class="fas fa-save me-1"></i> Simpan Tim</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div> <!-- End Tab Recount -->
        </div> <!-- End Tab Content -->
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