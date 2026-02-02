<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MembershipController extends Controller
{
    /**
     * Daftar Paket Premium Nusantara Adidaya
     * Harga diatur untuk menjaga brand image "High Quality"
     */
    private $plans = [
        ['id' => '7_days', 'name' => 'Sprint Flash', 'days' => 7, 'price' => 49000],
        ['id' => '30_days', 'name' => 'Standard Pro', 'days' => 30, 'price' => 99000],
        ['id' => '90_days', 'name' => 'Mastery Plan', 'days' => 90, 'price' => 199000],
        ['id' => '1_year', 'name' => 'Ultimate Pass', 'days' => 365, 'price' => 299000], // Menarik di kisaran 200rb++
    ];

    public function index()
    {
        return Inertia::render('User/Membership/Index', [
            'plans' => $this->plans,
            'current_membership' => auth()->user()->membership_expires_at,
            'is_member' => auth()->user()->isMember(),
            'user_balance' => auth()->user()->balance
        ]);
    }

    public function buy(Request $request)
    {
        $plan = collect($this->plans)->firstWhere('id', $request->plan_id);
        if (!$plan) return back()->with('error', 'Paket tidak valid');

        $user = auth()->user();

        // LOGIKA PEMBAYARAN VIA WALLET
        if ($request->payment_method === 'wallet') {
            if ($user->balance < $plan['price']) {
                return back()->with('error', 'Saldo dompet tidak mencukupi.');
            }

            DB::transaction(function () use ($user, $plan) {
                $user->decrement('balance', $plan['price']);
                
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $plan['price'],
                    'description' => 'Aktivasi Premium: ' . $plan['name'],
                    'status' => 'success'
                ]);

                $currentExpiry = $user->isMember() ? Carbon::parse($user->membership_expires_at) : now();
                $user->update([
                    'membership_expires_at' => $currentExpiry->addDays($plan['days'])
                ]);
            });

            return redirect()->route('dashboard')->with('success', 'Akses ' . $plan['name'] . ' berhasil diaktifkan!');
        }

        // LOGIKA PEMBAYARAN VIA GATEWAY
        $invoice = 'MEMB-' . strtoupper(Str::random(10));
        
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'tryout_id' => null,
            'invoice_code' => $invoice,
            'amount' => $plan['price'],
            'unit_price' => $plan['price'],
            'qty' => 1,
            'description' => 'Investasi Belajar: ' . $plan['name'],
            'status' => 'pending',
            'metadata' => [
                'type' => 'membership',
                'days' => $plan['days'],
                'plan_name' => $plan['name']
            ]
        ]);

        return redirect()->route('checkout.show', $transaction->id);
    }
}