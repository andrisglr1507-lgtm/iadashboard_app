@extends('layouts.app')

@section('title', 'Kelola Anggota Tim - SODC')
@section('page_title', 'Kelola Anggota Tim')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; margin-bottom: 20px;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #1e293b;">Anggota Tim: {{ $team->team_name }}</h2>
        <p style="margin: 5px 0 0 0; color: #64748b; font-size: 0.95rem;">Kelola pengguna yang bertugas di tim ini.</p>
    </div>
    <a href="{{ route('sodc.teams.index') }}" class="btn" style="background: #e2e8f0; color: #475569;">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Tim
    </a>
</div>

@if(session('success'))
    <div style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="margin-bottom: 20px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px; border-radius: 8px;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif

<div style="display: flex; gap: 24px; align-items: flex-start;">
    
    <!-- Kolom Tambah Anggota (Kiri) -->
    <div class="content-panel" style="flex: 1; padding: 0;">
        <div class="panel-header" style="border-bottom: 1px solid #f1f5f9; padding: 20px;">
            <h5 style="margin: 0; font-weight: 700; color: #1e293b;"><i class="fas fa-user-plus text-primary" style="margin-right: 8px; color: #3b82f6;"></i> Tambah Anggota</h5>
        </div>
        <div style="padding: 20px;">
            <form action="{{ route('sodc.teams.members.add', $team->id) }}" method="POST">
                @csrf
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #334155;">Pilih User</label>
                    <select name="user_id" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'">
                        <option value="">-- Pilih User --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->full_name }} ({{ $user->employee_code }})</option>
                        @endforeach
                    </select>
                    <small style="display: block; margin-top: 8px; color: #94a3b8; font-size: 0.85rem;">Catatan: Memasukkan user ke tim ini akan mengeluarkannya dari tim lain.</small>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px;">
                    <i class="fas fa-plus"></i> Tambah ke Tim
                </button>
            </form>
        </div>
    </div>

    <!-- Kolom Daftar Anggota (Kanan) -->
    <div class="content-panel" style="flex: 2; padding: 0;">
        <div class="panel-header" style="border-bottom: 1px solid #f1f5f9; padding: 20px;">
            <h5 style="margin: 0; font-weight: 700; color: #1e293b;"><i class="fas fa-users text-success" style="margin-right: 8px; color: #10b981;"></i> Daftar Anggota ({{ count($members) }} Orang)</h5>
        </div>
        <div class="table-container">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th style="border-bottom: 1px solid #e2e8f0;">Employee Code</th>
                        <th style="border-bottom: 1px solid #e2e8f0;">Nama Lengkap</th>
                        <th style="border-bottom: 1px solid #e2e8f0;">Role User</th>
                        <th style="border-bottom: 1px solid #e2e8f0; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $m)
                        <tr>
                            <td style="border-bottom: 1px solid #f1f5f9;">
                                <span style="background: #f1f5f9; color: #334155; padding: 4px 8px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; border: 1px solid #e2e8f0;">
                                    {{ $m->user->employee_code ?? '-' }}
                                </span>
                            </td>
                            <td style="border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #1e293b;">
                                {{ $m->user->full_name ?? '-' }}
                            </td>
                            <td style="border-bottom: 1px solid #f1f5f9; color: #64748b;">
                                {{ $m->user->role ?? '-' }}
                            </td>
                            <td style="border-bottom: 1px solid #f1f5f9; text-align: right;">
                                <form action="{{ route('sodc.teams.members.remove', ['id' => $team->id, 'member_id' => $m->id]) }}" method="POST" onsubmit="return confirm('Keluarkan user ini dari tim?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: #ef4444; font-weight: 600; cursor: pointer; padding: 6px 12px; border-radius: 6px; transition: background 0.2s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='none'">
                                        <i class="fas fa-times"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 40px 20px; color: #94a3b8;">
                                <i class="fas fa-user-slash" style="font-size: 2rem; margin-bottom: 10px; color: #cbd5e1;"></i>
                                <p style="margin: 0; font-weight: 500;">Belum ada anggota di tim ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
