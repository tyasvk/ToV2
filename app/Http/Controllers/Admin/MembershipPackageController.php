<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipPackage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MembershipPackageController extends Controller
{
    public function index()
    {
        $packages = MembershipPackage::latest()->get();
        return Inertia::render('Admin/Membership/Packages', [
            'packages' => $packages,
        ]);
    }

    // Method untuk Menyimpan Paket Baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            // Tambahkan validasi lain jika Anda punya kolom tambahan seperti description
        ]);

        MembershipPackage::create($request->all());

        return redirect()->back()->with('success', 'Paket membership berhasil ditambahkan.');
    }

    // Method untuk Menghapus Paket
    public function destroy(MembershipPackage $membershipPackage)
    {
        $membershipPackage->delete();

        return redirect()->back()->with('success', 'Paket membership berhasil dihapus.');
    }
}