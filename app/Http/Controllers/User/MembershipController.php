<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\MembershipPackage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class MembershipController extends Controller
{
    public function index() {
        // Mengambil semua paket aktif dari database
        $packages = MembershipPackage::where('is_active', true)->get();
        return Inertia::render('User/Membership/Index', ['packages' => $packages]);
    }

    public function buy(Request $request)
    {
        // Cari paket berdasarkan ID di database
        $package = MembershipPackage::where('is_active', true)->find($request->plan_id);
        if (!$package) return back()->with('error', 'Paket tidak valid');

        $user = auth()->user();

        // Buat kode invoice unik
        $invoice = 'MEMB-' . strtoupper(Str::random(10));
        
        // Catat transaksi sebagai "pending", metode pembayaran ditentukan nanti di halaman Checkout
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'tryout_id' => null, // null karena ini pembelian membership, bukan tryout spesifik
            'invoice_code' => $invoice,
            'amount' => $package->price,
            'unit_price' => $package->price,
            'qty' => 1,
            'description' => 'Investasi Belajar: ' . $package->name,
            'status' => 'pending',
            'metadata' => [
                'type' => 'membership',
                'days' => $package->duration_days,
                'plan_name' => $package->name
            ]
        ]);

        // Lempar user ke halaman Checkout
        return redirect()->route('checkout.show', $transaction->id);
    }
}