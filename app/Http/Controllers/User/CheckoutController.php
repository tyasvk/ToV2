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

// app/Http/Controllers/User/CheckoutController.php

public function show(Transaction $transaction)
{
    if ($transaction->user_id !== auth()->id()) abort(403);

    $user = User::find(auth()->id());
    $itemName = $this->resolveItemName($transaction);

    // Redirect jika sudah lunas
    if (in_array($transaction->status, ['paid', 'success'])) {
        return redirect()->route('dashboard')->with('success', "Akses $itemName Aktif!");
    }

    $transaction->load(['tryout']); 

    // Hitung Biaya Admin Midtrans (Agar Terima Bersih)
    $fee = ceil($transaction->amount * 0.007 * 1.11); // QRIS Fee 0.7% + PPN 11%
    $totalToPay = (int) ($transaction->amount + $fee);
    
    // Simpan total_amount ke DB agar sinkron
    $transaction->update(['total_amount' => $totalToPay]);

    if (empty($transaction->snap_token)) {
        $this->initMidtrans();
        $params = [
            'transaction_details' => [
                'order_id' => $transaction->invoice_code . '-' . time(), // Unik ID
                'gross_amount' => $totalToPay
            ],
            'item_details' => [
                ['id' => $transaction->id, 'price' => (int) $transaction->amount, 'quantity' => 1, 'name' => substr($itemName, 0, 30)],
                ['id' => 'FEE', 'price' => (int) $fee, 'quantity' => 1, 'name' => 'Biaya Layanan']
            ],
            'customer_details' => ['first_name' => $user->name, 'email' => $user->email],
        ];
        try {
            $snapToken = Snap::getSnapToken($params);
            $transaction->update(['snap_token' => $snapToken]);
        } catch (\Exception $e) {}
    }

    return Inertia::render('User/Checkout/Show', [
        'transaction' => [
            'id' => $transaction->id, 
            'amount' => $transaction->amount, 
            'total_amount' => $transaction->total_amount, // PERBAIKAN: Masukkan data ini
            'invoice_code' => $transaction->invoice_code,
            'snap_token' => $transaction->snap_token, 
            'description' => $itemName, 
            'tryout_id' => $transaction->tryout_id,
        ],
        'user_balance' => $user->balance,
    ]);
}

    public function process(Request $request, Transaction $transaction)
    {
        $request->validate(['payment_method' => 'required|in:wallet,midtrans']);
        
        if ($request->payment_method === 'wallet') {
            $user = User::find(auth()->id());
            if ($user->balance < $transaction->amount) {
                return back()->withErrors(['message' => 'Saldo tidak cukup.']);
            }

            DB::transaction(function () use ($user, $transaction) {
                $user->decrement('balance', $transaction->amount);
                $transaction->update(['status' => 'paid', 'payment_method' => 'wallet']);
                WalletTransaction::create([
                    'user_id' => $user->id, 'type' => 'debit', 'amount' => $transaction->amount,
                    'description' => 'Pembayaran ' . $this->resolveItemName($transaction), 'status' => 'success'
                ]);
            });
            return redirect()->route('dashboard')->with('success', 'Akses Aktif!');
        }
        return back();
    }

    private function initMidtrans() {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true; 
        Config::$is3ds = true;
    }
}