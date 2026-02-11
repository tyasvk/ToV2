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
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckoutController extends Controller
{
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

    /**
     * Hitung biaya tambahan agar Merchant "Terima Bersih"
     * Estimasi QRIS: 0.7% MDR + PPN 11% dari biaya tersebut.
     */
    private function calculateTotalWithFee($baseAmount)
    {
        $feePercentage = 0.007; // MDR QRIS 0.7%
        $taxPercentage = 0.11;  // PPN 11% dari MDR

        $adminFee = $baseAmount * $feePercentage;
        $vat = $adminFee * $taxPercentage;
        
        return (int) ceil($baseAmount + $adminFee + $vat);
    }

    public function show(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) abort(403);

        $user = User::find(auth()->id());
        $itemName = $this->resolveItemName($transaction);

        // Jika sudah lunas, arahkan ke dashboard
        if (in_array($transaction->status, ['paid', 'success'])) {
            return redirect()->route('dashboard')->with('success', "Akses $itemName Telah Aktif!");
        }

        $transaction->load(['tryout']); 

        // Generasi Snap Token
        if (empty($transaction->snap_token)) {
            try {
                $this->initMidtrans();

                // Hitung total bayar agar merchant terima bersih
                $totalToPay = $this->calculateTotalWithFee($transaction->amount);
                
                // Update total_amount di database
                $transaction->update(['total_amount' => $totalToPay]);

                $params = [
                    'transaction_details' => [
                        'order_id' => $transaction->invoice_code . '-' . time(), // Unique ID untuk Sandbox
                        'gross_amount' => $totalToPay
                    ],
                    'item_details' => [
                        [
                            'id' => 'P-' . $transaction->id, 
                            'price' => (int) $transaction->amount, 
                            'quantity' => 1, 
                            'name' => "Nusantara: " . substr($itemName, 0, 30)
                        ],
                        [
                            'id' => 'FEE-QRIS',
                            'price' => (int) ($totalToPay - $transaction->amount),
                            'quantity' => 1,
                            'name' => 'Biaya Layanan & PPN'
                        ]
                    ],
                    'customer_details' => [
                        'first_name' => auth()->user()->name, 
                        'email' => auth()->user()->email
                    ],
                    // HANYA QRIS
                    'enabled_payments' => ['qris'],
                ];

                $snapToken = Snap::getSnapToken($params);
                $transaction->update(['snap_token' => $snapToken]);
            } catch (\Exception $e) {
                Log::error('Midtrans Error: ' . $e->getMessage());
            }
        }

        return Inertia::render('User/Checkout/Show', [
            'transaction' => [
                'id' => $transaction->id, 
                'amount' => $transaction->amount, 
                'total_amount' => $transaction->total_amount ?? $transaction->amount,
                'invoice_code' => $transaction->invoice_code,
                'snap_token' => $transaction->snap_token, 
                'description' => $itemName, 
                'tryout_id' => $transaction->tryout_id,
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
        Config::$serverKey = config('services.midtrans.server_key'); //
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true; 
        Config::$is3ds = true;
    }
}