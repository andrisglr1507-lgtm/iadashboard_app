@extends('layouts.app')

@section('title', 'Add New User')

@section('content')
<div class="top-bar">
    <h1 class="page-title">Add New User</h1>
</div>

<div class="content-panel" style="max-width: 600px; margin: 24px auto;">
    @if ($errors->any())
        <div style="background: #fef2f2; border: 1px solid #ef4444; color: #b91c1c; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.store') }}" method="POST">
        @csrf
        
        <div style="margin-bottom: 16px;">
            <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Employee Code <span style="color:red;">*</span></label>
            <input type="text" name="employee_code" value="{{ old('employee_code') }}" required
                   style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem;" placeholder="e.g. EMP-001">
        </div>

        <div style="margin-bottom: 16px;">
            <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Full Name <span style="color:red;">*</span></label>
            <input type="text" name="full_name" value="{{ old('full_name') }}" required
                   style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem;" placeholder="e.g. John Doe">
        </div>

        <div style="margin-bottom: 16px;">
            <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Username <span style="color:red;">*</span></label>
            <input type="text" name="username" value="{{ old('username') }}" required
                   style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem;" placeholder="e.g. johndoe">
        </div>

        <div style="margin-bottom: 16px;">
            <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Role <span style="color:red;">*</span></label>
            <select name="role" required style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; background: white;">
                <option value="SUPER_ADMIN" {{ old('role') == 'SUPER_ADMIN' ? 'selected' : '' }}>SUPER_ADMIN</option>
                <option value="ADMIN" {{ old('role') == 'ADMIN' ? 'selected' : '' }}>ADMIN</option>
                <option value="HEAD_OPNAME" {{ old('role') == 'HEAD_OPNAME' ? 'selected' : '' }}>HEAD_OPNAME</option>
                <option value="AUDITOR" {{ old('role') == 'AUDITOR' ? 'selected' : '' }}>AUDITOR</option>
            </select>
        </div>

        <div style="margin-bottom: 16px;">
            <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Password <span style="color:red;">*</span></label>
            <input type="password" name="password" required minlength="6"
                   style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem;" placeholder="Min. 6 characters">
        </div>

        <div style="margin-bottom: 24px;">
            <label style="display: flex; align-items: center; cursor: pointer; gap: 8px;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width: 18px; height: 18px;">
                <span style="font-weight: 600; font-size: 0.9rem;">Active Status</span>
            </label>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <a href="{{ route('users.index') }}" class="btn-secondary" style="padding: 10px 20px; font-size: 0.9rem; text-decoration: none; color: #64748b; border: 1px solid #e2e8f0; border-radius: 8px; font-weight: 600;">Cancel</a>
            <button type="submit" class="btn-primary" style="padding: 10px 20px; font-size: 0.9rem;">Create User</button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .content-panel {
        background: white;
        border-radius: 24px;
        padding: 32px;
        border: 1px solid #eef2f8;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
</style>
@endpush
