<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['username' => 'admin'],
            [
                'employee_code' => 'EMP-001',
                'full_name' => 'Super Admin',
                'password_hash' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role' => 'ADMIN'
            ]
        );
    }
}
