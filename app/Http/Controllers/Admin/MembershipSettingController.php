<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MembershipSettingController extends Controller
{
    public function index()
    {
        // Ambil harga dari database, default: 0 jika belum ada
        $priceSetting = Setting::firstOrCreate(
            ['key' => 'membership_price'],
            ['value' => '0']
        );

        return Inertia::render('Admin/Membership/Index', [
            'currentPrice' => (int) $priceSetting->value
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'price' => 'required|numeric|min:0'
        ]);

        Setting::updateOrCreate(
            ['key' => 'membership_price'],
            ['value' => $request->price]
        );

        return back()->with('message', 'Harga membership berhasil diperbarui!');
    }
}