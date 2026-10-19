@extends('layouts.app')

@section('title', 'List Users')
@section('page_title', 'Master Data User')

@php
    $svgUserCog  = file_exists(public_path('svg/Daftar_Team/user-cog.svg'))  ? file_get_contents(public_path('svg/Daftar_Team/user-cog.svg'))  : '<i class="fas fa-user-cog"></i>';
    $svgUserPlus = file_exists(public_path('svg/Daftar_Team/user-plus.svg')) ? file_get_contents(public_path('svg/Daftar_Team/user-plus.svg')) : '<i class="fas fa-plus"></i>';
    $svgPencil   = file_exists(public_path('svg/Daftar_Team/pencil.svg'))     ? file_get_contents(public_path('svg/Daftar_Team/pencil.svg'))     : '<i class="fas fa-edit"></i>';
    $svgTrash    = file_exists(public_path('svg/Daftar_Team/trash-x.svg'))    ? file_get_contents(public_path('svg/Daftar_Team/trash-x.svg'))    : '<i class="fas fa-trash"></i>';
@endphp

@push('styles')
<style>
    .btn svg {
        width: 18px;
        height: 18px;
        stroke-width: 2;
        flex-shrink: 0;
    }
    .table-action-link,
    .table-action-delete {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        font-weight: 500;
        transition: opacity 0.2s;
    }
    .table-action-link:hover,
    .table-action-delete:hover {
        opacity: 0.8;
    }
    .table-action-link svg,
    .table-action-delete svg {
        width: 17px;
        height: 17px;
        stroke-width: 2;
        flex-shrink: 0;
    }
    .panel-title svg {
        width: 22px;
        height: 22px;
        stroke-width: 2;
        flex-shrink: 0;
    }
    .empty-state-svg svg {
        width: 44px;
        height: 44px;
        stroke-width: 1.5;
        color: #cbd5e1;
    }
</style>
@endpush

@section('content')
@if(session('success'))
    <div style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px; border-radius: 8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div class="content-panel" style="margin-top: 20px;">
    <div class="panel-header">
        <h3 class="panel-title" style="display: flex; align-items: center; gap: 8px;">
            <span style="display: inline-flex; align-items: center; color: var(--phoenix-primary, #1ABB9C);">
                {!! $svgUserCog !!}
            </span>
            Daftar User
        </h3>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="{{ route('users.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                {!! $svgUserPlus !!} Tambah User
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
                        <a href="{{ route('users.edit', $user->id) }}" class="table-action-link" style="color: #0284c7; margin-right: 12px;" title="Edit User">
                            {!! $svgPencil !!} Edit
                        </a>
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="table-action-delete" style="background:none; border:none; color: #ef4444; cursor:pointer; font-size: inherit; padding: 0;" title="Delete User">
                                {!! $svgTrash !!} Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 30px; text-align: center; color: #94a3b8;">
                        <div class="empty-state-svg" style="margin-bottom: 12px; display: flex; justify-content: center;">
                            {!! $svgUserCog !!}
                        </div>
                        No users found in the system.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
