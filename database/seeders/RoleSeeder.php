<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat atau ambil Role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // 2. Tambahkan / Update akun Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'], // Unik berdasarkan email
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'), // Ganti password sesuai kebutuhan
                'email_verified_at' => now(),
            ]
        );
        
        // Pastikan admin memiliki role admin
        if (!$admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }

        // 3. Tambahkan / Update akun User Biasa
        $user = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('user123'),
                'email_verified_at' => now(),
            ]
        );

        // Pastikan user memiliki role user
        if (!$user->hasRole('user')) {
            $user->assignRole($userRole);
        }
    }
}