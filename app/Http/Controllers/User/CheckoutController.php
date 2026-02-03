<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\WalletTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    // Mapping Nama Prawira, Satria, dll
    private function resolveItemName($transaction)
    {
        $planMapping = [
            'sprint flash'  => 'Prawira',
            'mastery plan'  => 'Wiranata',
            'ultimate pass' => 'Mahapatih',
            'standard pro'  => 'Satria',
        ];
        $rawPlanName = $transaction->metadata['plan_name'] ?? '';
        $nusantaraName = $planMapping[strtolower(trim($rawPlanName))] ?? $rawPlanName;
        return $transaction->tryout?->title ?? $nusantaraName ?? $transaction->description ?? 'Paket Belajar';
    }

    public function show(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) abort(403);

        $user = User::find(auth()->id());
        $itemName = $this->resolveItemName($transaction);

        // --- FITUR AUTO-REPAIR ---
        // Jika sudah lunas tapi gembok masih Locked, aktifkan sekarang!
        if (in_array($transaction->status, ['paid', 'success']) && !$transaction->tryout_id) {
            if (!$user->membership_expires_at || $user->membership_expires_at->isPast()) {
                $days = $transaction->metadata['days'] ?? 30;
                $user->update(['membership_expires_at' => now()->addDays($days)]);
            }
            return redirect()->route('dashboard')->with('success', "Akses $itemName Telah Aktif!");
        }

        if (in_array($transaction->status, ['paid', 'success'])) {
            return redirect()->route('tryout.adidaya');
        }

        $transaction->load(['tryout']); 

        if (empty($transaction->snap_token)) {
            $this->initMidtrans();
            $params = [
                'transaction_details' => ['order_id' => $transaction->invoice_code, 'gross_amount' => (int) $transaction->amount],
                'item_details' => [['id' => $transaction->id, 'price' => (int) $transaction->amount, 'quantity' => 1, 'name' => "Nusantara: " . substr($itemName, 0, 39)]],
                'customer_details' => ['first_name' => auth()->user()->name, 'email' => auth()->user()->email],
            ];
            try {
                $snapToken = Snap::getSnapToken($params);
                $transaction->update(['snap_token' => $snapToken]);
            } catch (\Exception $e) {}
        }

        return Inertia::render('User/Checkout/Show', [
            'transaction' => [
                'id' => $transaction->id, 'amount' => $transaction->amount, 'invoice_code' => $transaction->invoice_code,
                'snap_token' => $transaction->snap_token, 'description' => $itemName, 'tryout_id' => $transaction->tryout_id,
            ],
            'user_balance' => auth()->user()->balance,
        ]);
    }

    public function process(Request $request, Transaction $transaction)
    {
        $request->validate(['payment_method' => 'required|in:wallet,midtrans']);
        $user = User::find(auth()->id());
        $itemName = $this->resolveItemName($transaction);

        if ($request->payment_method === 'wallet') {
            if ($user->balance < $transaction->amount) {
                return back()->withErrors(['message' => 'Saldo tidak cukup.']);
            }

            try {
                DB::transaction(function () use ($user, $transaction, $itemName) {
                    $user->decrement('balance', $transaction->amount);
                    $transaction->update(['status' => 'paid', 'payment_method' => 'wallet']);

                    if (!$transaction->tryout_id) {
                        $days = $transaction->metadata['days'] ?? 30;
                        $currentExpiry = $user->membership_expires_at && $user->membership_expires_at->isFuture()
                            ? $user->membership_expires_at : now();
                        $user->update(['membership_expires_at' => $currentExpiry->addDays($days)]);
                    }

                    WalletTransaction::create([
                        'user_id' => $user->id, 'type' => 'debit', 'amount' => $transaction->amount,
                        'description' => 'Pembayaran ' . $itemName, 'status' => 'success'
                    ]);
                });

                return redirect()->route('dashboard')->with('success', "Akses $itemName Aktif!");
            } catch (\Exception $e) {
                return back()->withErrors(['message' => 'Gagal aktivasi.']);
            }
        }
        return back();
    }

    private function initMidtrans() {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true; Config::$is3ds = true;
    }
}