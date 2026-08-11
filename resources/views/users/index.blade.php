@extends('layouts.app')

@section('title', 'List Users')

@section('content')
<div class="top-bar">
    <h1 class="page-title">List Users</h1>
</div>

<div class="content-panel">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 1px solid #eef2f8;">
                <th style="padding: 12px 8px;">ID</th>
                <th style="padding: 12px 8px;">Name</th>
                <th style="padding: 12px 8px;">Email</th>
                <th style="padding: 12px 8px;">Role</th>
                <th style="padding: 12px 8px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr style="border-bottom: 1px solid #eef2f8;">
                <td style="padding: 12px 8px;">{{ $user->user_id }}</td>
                <td style="padding: 12px 8px;">{{ $user->name }}</td>
                <td style="padding: 12px 8px;">{{ $user->email }}</td>
                <td style="padding: 12px 8px;">{{ $user->role }}</td>
                <td style="padding: 12px 8px;">
                    @if($user->is_active)
                        <span style="color: green;">Active</span>
                    @else
                        <span style="color: red;">Inactive</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding: 12px 8px; text-align: center;">No users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
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
