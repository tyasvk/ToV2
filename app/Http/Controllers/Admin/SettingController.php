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
        $announcement = Setting::where('key', 'announcement')->value('value') ?? '';

        return Inertia::render('Admin/Setting/Index', [
            'announcement' => $announcement
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'announcement' => 'nullable|string|max:1000',
        ]);

        Setting::updateOrCreate(
            ['key' => 'announcement'],
            ['value' => $request->announcement]
        );

        return back()->with('success', 'Pengumuman dashboard berhasil diperbarui.');
    }
}