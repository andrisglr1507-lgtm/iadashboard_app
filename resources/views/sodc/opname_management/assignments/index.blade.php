@extends('layouts.app')

@section('title', 'Team Assignments - SODC')

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        border-radius: 1rem;
    }
    .bin-card {
        transition: all 0.2s ease;
        border: 1px solid #e2e8f0;
    }
    .bin-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        border-color: #3b82f6;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800" style="font-weight: 700; letter-spacing: -0.5px;">Team Assignments</h2>
            <p class="text-muted mb-0">Assign Bins to Opname Teams for the active session.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="background: #ecfdf5; color: #065f46;">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="background: #fef2f2; color: #991b1b;">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(!$session)
        <div class="glass-card p-5 text-center">
            <div class="mb-3">
                <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
            </div>
            <h4 class="text-gray-800 font-weight-bold">Tidak ada Sesi Aktif</h4>
            <p class="text-muted">Silakan buka Sesi Opname terlebih dahulu sebelum membagi tugas.</p>
            <a href="{{ route('sodc.sessions.index') }}" class="btn btn-primary mt-2">Buka Sesi</a>
        </div>
    @else
        <div class="glass-card p-4 mb-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="font-weight-bold text-primary mb-1"><i class="fas fa-play-circle me-2"></i> Sesi Aktif: {{ $session->session_code }}</h5>
                    <span class="badge bg-dark">{{ $session->mode }} MODE</span>
                    <span class="badge bg-secondary ms-1">{{ count($zones) }} Zona Target</span>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach($zones as $zone)
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="card bin-card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0 font-weight-bold text-gray-800" style="font-family: monospace;">Zona {{ $zone->zone }}</h5>
                        </div>
                        <div class="mb-3 text-muted" style="font-size: 0.85rem;">
                            <div><i class="fas fa-boxes me-2"></i>{{ $zone->total_bins }} Bins</div>
                            <div><i class="fas fa-tags me-2"></i>{{ $zone->total_sku }} SKU Target</div>
                        </div>
                        
                        <!-- Simple form to assign a team to this zone -->
                        <form action="{{ route('sodc.assignments.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="session_id" value="{{ $session->id }}">
                            <input type="hidden" name="zone" value="{{ $zone->zone }}">
                            
                            <div class="mb-2">
                                <select name="team_id" class="form-select form-select-sm" required style="border-radius: 8px;">
                                    <option value="">-- Pilih Tim --</option>
                                    @foreach($teams as $team)
                                        <option value="{{ $team->id }}">{{ $team->team_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100" style="border-radius: 8px; font-weight: 600;">
                                <i class="fas fa-plus me-1"></i> Assign
                            </button>
                        </form>
                        
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection