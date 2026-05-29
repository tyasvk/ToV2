<?php

namespace App\Http\Controllers\Admin;

use App\Models\MembershipPackage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class MembershipPackageController extends Controller
{
    public function index() {
        return Inertia::render('Admin/Membership/Packages', [
            'packages' => MembershipPackage::all()
        ]);
    }

    public function update(Request $request, $id) {
        $package = MembershipPackage::findOrFail($id);
        
        // PEMBARUAN: Tambahkan 'name' ke dalam kriteria validasi request
        $package->update($request->validate([
            'name'          => 'required|string|max:255',
            'price'         => 'required|numeric',
            'duration_days' => 'required|integer'
        ]));
        
        return back()->with('message', 'Paket berhasil diperbarui!');
    }
}