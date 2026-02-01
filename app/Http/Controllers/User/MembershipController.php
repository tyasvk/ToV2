<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MembershipController extends Controller
{
    // Daftar Paket Membership
    private $plans = [
        ['id' => '7_days', 'name' => 'Member Mingguan', 'days' => 7, 'price' => 25000],
        ['id' => '30_days', 'name' => 'Member Bulanan', 'days' => 30, 'price' => 75000],
        ['id' => '90_days', 'name' => 'Member 3 Bulan', 'days' => 90, 'price' => 150000],
        ['id' => '1_year', 'name' => 'Member Tahunan', 'days' => 365, 'price' => 500000],
    ];

    public function index()
    {
        return Inertia::render('User/Membership/Index', [
            'plans' => $this->plans,
            'current_membership' => auth()->user()->membership_expires_at,
            'is_member' => auth()->user()->isMember()
        ]);
    }

    public function buy(Request $request)
    {
        $plan = collect($this->plans)->firstWhere('id', $request->plan_id);
        if (!$plan) return back()->with('error', 'Paket tidak valid');

        $user = auth()->user();

        if ($request->payment_method === 'wallet') {
            if ($user->balance < $plan['price']) {
                return back()->with('error', 'Saldo tidak cukup');
            }

            DB::transaction(function () use ($user, $plan) {
                // Potong Saldo
                $user->decrement('balance', $plan['price']);
                
                // Catat Transaksi Wallet
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $plan['price'],
                    'description' => 'Pembelian ' . $plan['name'],
                    'status' => 'success'
                ]);

                // Update Masa Aktif User
                $currentExpiry = $user->isMember() ? $user->membership_expires_at : now();
                $user->update([
                    'membership_expires_at' => $currentExpiry->addDays($plan['days'])
                ]);
            });

            return redirect()->route('dashboard')->with('success', 'Membership berhasil diaktifkan!');
        }

        // Logic Midtrans (Jika menggunakan QRIS/Bank Transfer)
        $invoice = 'MEMB-' . strtoupper(Str::random(10));
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'invoice_code' => $invoice,
            'amount' => $plan['price'],
            'description' => 'Pembelian ' . $plan['name'],
            'status' => 'pending',
            'metadata' => ['days' => $plan['days']] // Simpan durasi di metadata
        ]);

        return redirect()->route('checkout.show', $transaction->id);
    }
}