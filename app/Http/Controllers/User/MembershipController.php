<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\WalletTransaction;
use App\Models\MembershipPackage; // Tambahkan import model
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MembershipController extends Controller
{
    public function index() {
        // Mengambil semua paket aktif dari database
        $packages = MembershipPackage::where('is_active', true)->get();
        return Inertia::render('User/Membership/Index', ['packages' => $packages]);
    }

    public function buy(Request $request)
    {
        // Cari paket berdasarkan ID di database (bukan dari array $this->plans lagi)
        $package = MembershipPackage::where('is_active', true)->find($request->plan_id);
        if (!$package) return back()->with('error', 'Paket tidak valid');

        $user = auth()->user();

        // LOGIKA PEMBAYARAN VIA WALLET
        if ($request->payment_method === 'wallet') {
            if ($user->balance < $package->price) {
                return back()->with('error', 'Saldo dompet tidak mencukupi.');
            }

            DB::transaction(function () use ($user, $package) {
                $user->decrement('balance', $package->price);
                
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $package->price,
                    'description' => 'Aktivasi Premium: ' . $package->name,
                    'status' => 'success'
                ]);

                $currentExpiry = $user->isMember() ? Carbon::parse($user->membership_expires_at) : now();
                $user->update([
                    'membership_expires_at' => $currentExpiry->addDays($package->duration_days)
                ]);
            });

            return redirect()->route('dashboard')->with('success', 'Akses ' . $package->name . ' berhasil diaktifkan!');
        }

        // LOGIKA PEMBAYARAN VIA GATEWAY
        $invoice = 'MEMB-' . strtoupper(Str::random(10));
        
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'tryout_id' => null,
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

        return redirect()->route('checkout.show', $transaction->id);
    }
}