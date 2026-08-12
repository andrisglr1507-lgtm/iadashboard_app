@extends('layouts.app')

@section('title', 'List Users')

@section('content')
<div class="top-bar">
    <h1 class="page-title">List Users</h1>
</div>

<div class="content-panel">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2 style="font-size: 1.1rem; font-weight: 600; color: var(--dark);">User Management</h2>
        <a href="{{ route('users.create') }}" class="btn-primary" style="padding: 10px 20px; font-size: 0.85rem;">
            <i class="fas fa-plus"></i> Add New User
        </a>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Emp. Code</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td style="font-weight: 600; color: var(--primary);">{{ $user->employee_code }}</td>
                    <td>{{ $user->full_name }}</td>
                    <td>{{ $user->username }}</td>
                    <td>
                        <span style="background: var(--bg); padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; color: var(--dark);">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td>
                        @if($user->is_active)
                            <span style="background: #ecfdf5; color: #10b981; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">Active</span>
                        @else
                            <span style="background: #fef2f2; color: #ef4444; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">Inactive</span>
                        @endif
                    </td>
                    <td style="text-align: right; display: flex; justify-content: flex-end; gap: 8px;">
                        <a href="{{ route('users.edit', $user->id) }}" class="action-btn" title="Edit User">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn" style="color: #ef4444;" title="Delete User">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 30px; text-align: center; color: var(--text-secondary);">
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

@push('styles')
<style>
    .content-panel {
        background: white;
        border-radius: 24px;
        padding: 24px;
        margin-top: 28px;
        border: 1px solid #eef2f8;
    }
</style>
@endpush
