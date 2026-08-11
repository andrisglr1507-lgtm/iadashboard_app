@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="top-bar">
    <h1 class="page-title">Dashboard</h1>
    <div class="badge-status">
        <i class="fas fa-circle" style="color:#2a9d8f; font-size: 0.6rem;"></i>
        <span>Welcome back, {{ Auth::user()->name ?? 'User' }}</span>
    </div>
</div>

<div class="card-grid">
    <div class="stat-card">
        <h3><i class="fas fa-eye"></i> Total Views</h3>
        <div class="stat-number">12,432</div>
    </div>
    <div class="stat-card">
        <h3><i class="fas fa-users"></i> Users</h3>
        <div class="stat-number">{{ number_format(rand(1000, 5000)) }}</div>
    </div>
    <div class="stat-card">
        <h3><i class="fas fa-chart-line"></i> Growth</h3>
        <div class="stat-number">+24%</div>
    </div>
</div>

<div class="content-panel">
    <p><strong>✨ Welcome to Classic Dashboard Laravel</strong></p>
    <p>Sidebar dapat di-expand/collapse. Menu dan submenu bertingkat sudah fungsional.</p>
    <hr>
    <p><i class="fas fa-info-circle"></i> Klik item menu di sidebar untuk navigasi.</p>
</div>

<footer>© {{ date('Y') }} {{ config('app.name') }} — Classic Dashboard with Laravel</footer>
@endsection

@push('styles')
<style>

    .card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 24px;
        margin-top: 20px;
    }
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        border: 1px solid #edf2f7;
    }
    .stat-number {
        font-size: 2.2rem;
        font-weight: 700;
        color: #1e4a76;
    }
    .content-panel {
        background: white;
        border-radius: 24px;
        padding: 24px;
        margin-top: 28px;
        border: 1px solid #eef2f8;
    }
    footer {
        font-size: 0.7rem;
        text-align: center;
        margin-top: 30px;
        color: #8ba0b5;
    }
</style>
@endpush