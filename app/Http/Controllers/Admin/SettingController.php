<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        // Ambil pengaturan berdasarkan key
        $announcement = Setting::where('key', 'announcement')->value('value') ?? '';
        $manualTotalUsers = Setting::where('key', 'manual_total_users')->value('value') ?? 0;

        return Inertia::render('Admin/Setting/Index', [
            'announcement' => $announcement,
            'manual_total_users' => (int) $manualTotalUsers
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'announcement' => 'nullable|string|max:1000',
            'manual_total_users' => 'nullable|integer|min:0',
        ]);

        // Simpan Pengumuman
        Setting::updateOrCreate(
            ['key' => 'announcement'],
            ['value' => $request->announcement]
        );

        // Simpan Manipulasi Total User
        Setting::updateOrCreate(
            ['key' => 'manual_total_users'],
            ['value' => $request->manual_total_users ?? 0]
        );

        return back()->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}