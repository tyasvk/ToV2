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
        $package->update($request->validate([
            'price' => 'required|numeric',
            'duration_days' => 'required|integer'
        ]));
        return back()->with('message', 'Paket berhasil diperbarui!');
    }
}