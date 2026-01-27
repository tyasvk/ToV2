<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
public function run(): void
{
    // 1. Buat Role (Jika belum ada)
    $adminRole = \Spatie\Permission\Models\Role::create(['name' => 'admin']);
    $userRole = \Spatie\Permission\Models\Role::create(['name' => 'user']);

    // 2. Buat User Admin
    $admin = \App\Models\User::create([
        'name' => 'Administrator',
        'email' => 'admin@test.com',
        'password' => bcrypt('admin123'), // Passwordnya: admin123
    ]);

    // 3. Tempelkan Role ke User
    $admin->assignRole($adminRole);
}
}
