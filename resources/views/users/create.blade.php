@extends('layouts.app')

@section('title', 'Tambah User')
@section('page_title', 'Tambah User')

@section('content')
<div class="content-panel" style="padding: 24px; margin-top: 20px;">
    
    @if ($errors->any())
        <div style="background: #fef2f2; border: 1px solid #ef4444; color: #b91c1c; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="display: flex; gap: 40px; flex-wrap: wrap;">
        <!-- Kolom Kiri: Form -->
        <div style="flex: 1; min-width: 300px;">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #334155;">Employee Code <span style="color:red;">*</span></label>
                    <input type="text" name="employee_code" value="{{ old('employee_code') }}" required
                           style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'" placeholder="e.g. EMP-001">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #334155;">Full Name <span style="color:red;">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required
                           style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'" placeholder="e.g. John Doe">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #334155;">Username <span style="color:red;">*</span></label>
                    <input type="text" name="username" value="{{ old('username') }}" required
                           style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'" placeholder="e.g. johndoe">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #334155;">Role <span style="color:red;">*</span></label>
                    <select name="role" required style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s; background: white;" onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'">
                        <option value="SUPER_ADMIN" {{ old('role') == 'SUPER_ADMIN' ? 'selected' : '' }}>SUPER_ADMIN</option>
                        <option value="ADMIN" {{ old('role') == 'ADMIN' ? 'selected' : '' }}>ADMIN</option>
                        <option value="HEAD_OPNAME" {{ old('role') == 'HEAD_OPNAME' ? 'selected' : '' }}>HEAD_OPNAME</option>
                        <option value="AUDITOR" {{ old('role') == 'AUDITOR' ? 'selected' : '' }}>AUDITOR</option>
                    </select>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #334155;">Password <span style="color:red;">*</span></label>
                    <input type="password" name="password" required minlength="6"
                           style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#3874ff'" onblur="this.style.borderColor='#cbd5e1'" placeholder="Min. 6 characters">
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; cursor: pointer; gap: 8px; color: #334155;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width: 20px; height: 20px; cursor: pointer;">
                        <span style="font-weight: 600;">Status Aktif</span>
                    </label>
                </div>

                <div style="margin-top: 10px;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 24px; width: 100%; justify-content: center;">
                        <i class="fas fa-save"></i> Simpan Data User
                    </button>
                </div>
            </form>
        </div>

        <!-- Kolom Kanan: Ilustrasi -->
        <div style="flex: 1; display: flex; align-items: center; justify-content: center; min-width: 200px; background: #f8fafc; border-radius: 16px; border: 1px dashed #cbd5e1;">
            <img src="{{ asset('images/user.png') }}" alt="User Illustration" style="max-width: 50%; height: auto; border-radius: 12px;">
        </div>
    </div>
</div>
@endsection
