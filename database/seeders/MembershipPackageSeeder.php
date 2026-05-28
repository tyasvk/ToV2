<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MembershipPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run(): void
{
    $packages = [
        ['name' => 'Bronze', 'price' => 50000, 'duration_days' => 30],
        ['name' => 'Silver', 'price' => 100000, 'duration_days' => 60],
        ['name' => 'Gold', 'price' => 150000, 'duration_days' => 90],
        ['name' => 'Platinum', 'price' => 300000, 'duration_days' => 365],
    ];
    foreach ($packages as $pkg) { \App\Models\MembershipPackage::create($pkg); }
}
}
