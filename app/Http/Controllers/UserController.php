<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|max:50|unique:users',
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:SUPER_ADMIN,ADMIN,HEAD_OPNAME,AUDITOR',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['password_hash'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        unset($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'employee_code' => 'required|string|max:50|unique:users,employee_code,' . $user->id,
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:users,username,' . $user->id,
            'role' => 'required|string|in:SUPER_ADMIN,ADMIN,HEAD_OPNAME,AUDITOR',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:6'
        ]);

        $validated['is_active'] = $request->has('is_active');

        if (!empty($validated['password'])) {
            $validated['password_hash'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }
        unset($validated['password']);

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
