@extends('layouts.app')

@section('title', 'Kelola Anggota Tim - SODC')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800" style="font-weight: 700;">Anggota Tim: {{ $team->team_name }}</h2>
            <p class="text-muted mb-0">Kelola pengguna yang bertugas di tim ini.</p>
        </div>
        <a href="{{ route('sodc.teams.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Tim
        </a>
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

    <div class="row">
        <!-- Kolom Tambah Anggota -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white" style="border-bottom: 1px solid #f1f5f9; padding: 20px;">
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-user-plus text-primary me-2"></i> Tambah Anggota</h5>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <form action="{{ route('sodc.teams.members.add', $team->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label font-weight-bold text-gray-700">Pilih User</label>
                            <select name="user_id" class="form-select" required style="border-radius: 8px;">
                                <option value="">-- Pilih User --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->full_name }} ({{ $user->employee_code }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted mt-1 d-block">Catatan: Memasukkan user ke tim ini akan mengeluarkannya dari tim lain.</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" style="border-radius: 8px;">
                            Tambah ke Tim
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Daftar Anggota -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white" style="border-bottom: 1px solid #f1f5f9; padding: 20px;">
                    <h5 class="mb-0 font-weight-bold"><i class="fas fa-users text-success me-2"></i> Daftar Anggota ({{ count($members) }} Orang)</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th style="padding: 15px 20px; font-weight: 600; color: #475569; border-bottom: none;">Employee Code</th>
                                <th style="padding: 15px 20px; font-weight: 600; color: #475569; border-bottom: none;">Nama Lengkap</th>
                                <th style="padding: 15px 20px; font-weight: 600; color: #475569; border-bottom: none;">Role User</th>
                                <th style="padding: 15px 20px; font-weight: 600; color: #475569; border-bottom: none; text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($members as $m)
                                <tr>
                                    <td style="padding: 15px 20px; border-bottom: 1px solid #f1f5f9;">
                                        <span class="badge bg-light text-dark border">{{ $m->user->employee_code ?? '-' }}</span>
                                    </td>
                                    <td style="padding: 15px 20px; border-bottom: 1px solid #f1f5f9; font-weight: 600;">
                                        {{ $m->user->full_name ?? '-' }}
                                    </td>
                                    <td style="padding: 15px 20px; border-bottom: 1px solid #f1f5f9; color: #64748b;">
                                        {{ $m->user->role ?? '-' }}
                                    </td>
                                    <td style="padding: 15px 20px; border-bottom: 1px solid #f1f5f9; text-align: right;">
                                        <form action="{{ route('sodc.teams.members.remove', ['id' => $team->id, 'member_id' => $m->id]) }}" method="POST" onsubmit="return confirm('Keluarkan user ini dari tim?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 6px;">
                                                <i class="fas fa-times"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center" style="padding: 30px; color: #94a3b8;">
                                        <i class="fas fa-user-slash fa-2x mb-2 text-light"></i>
                                        <p class="mb-0">Belum ada anggota di tim ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
