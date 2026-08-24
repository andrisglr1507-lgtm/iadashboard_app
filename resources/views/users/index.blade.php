@extends('layouts.app')

@section('title', 'List Users')
@section('page_title', 'Master User')

@section('content')
@if(session('success'))
    <div style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div class="content-panel" style="margin-top: 20px;">
    <div class="panel-header">
        <h3 class="panel-title">Daftar User</h3>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah User
            </a>
        </div>
    </div>

    <div class="table-container">
        <table class="premium-table">
            <thead>
                <tr>
                    <th style="border-bottom: 1px solid #e2e8f0;">Emp. Code</th>
                    <th style="border-bottom: 1px solid #e2e8f0;">Full Name</th>
                    <th style="border-bottom: 1px solid #e2e8f0;">Username</th>
                    <th style="border-bottom: 1px solid #e2e8f0;">Role</th>
                    <th style="border-bottom: 1px solid #e2e8f0;">Status</th>
                    <th style="border-bottom: 1px solid #e2e8f0;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td style="font-weight: 600; color: #0369a1; border-bottom: 1px solid #f1f5f9;">{{ $user->employee_code }}</td>
                    <td style="border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #334155;">{{ $user->full_name }}</td>
                    <td style="border-bottom: 1px solid #f1f5f9;">{{ $user->username }}</td>
                    <td style="border-bottom: 1px solid #f1f5f9;">
                        <span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; border: 1px solid #e2e8f0;">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td style="border-bottom: 1px solid #f1f5f9;">
                        @if($user->is_active)
                            <span style="background: #ecfdf5; color: #10b981; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600;">Active</span>
                        @else
                            <span style="background: #fef2f2; color: #ef4444; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600;">Inactive</span>
                        @endif
                    </td>
                    <td style="border-bottom: 1px solid #f1f5f9;">
                        <a href="{{ route('users.edit', $user->id) }}" style="color: #0284c7; margin-right: 10px; text-decoration: none;" title="Edit User">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background:none; border:none; color: #ef4444; cursor:pointer; font-size: inherit;" title="Delete User">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 30px; text-align: center; color: #94a3b8;">
                        <i class="fas fa-users" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 12px; display: block;"></i>
                        No users found in the system.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
