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
use Carbon\Carbon; // <--- TAMBAHKAN BARIS INI

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
            // 1. Potong Saldo User
            $user->decrement('balance', $transaction->amount);
            
            // 2. Update Status Transaksi Utama
            $transaction->update(['status' => 'paid', 'payment_method' => 'wallet']);
            
            // 3. Catat ke Riwayat Mutasi Dompet
            WalletTransaction::create([
                'user_id' => $user->id, 
                'type' => 'debit', 
                'amount' => $transaction->amount,
                'description' => 'Pembayaran ' . $this->resolveItemName($transaction), 
                'status' => 'success'
            ]);

            // PENTING: Tambahkan Logika Aktivasi Masa Aktif Membership di sini
            // Jika transaksi tidak memiliki tryout_id, berarti ini adalah paket membership
            if (!$transaction->tryout_id || (isset($transaction->metadata['type']) && $transaction->metadata['type'] === 'membership')) {
                
                // Ambil jumlah hari dari metadata paket yang dibeli
                $daysToAdd = $transaction->metadata['days'] ?? $transaction->metadata['duration_days'] ?? 30; 

                // Tentukan tanggal mulai penambahan
                // Jika user masih punya paket aktif (isFuture), akumulasikan dari tanggal expired lama.
                // Jika sudah hangus atau member gratis, mulai dari hari ini (now).
                $currentExpiry = ($user->membership_expires_at && Carbon::parse($user->membership_expires_at)->isFuture()) 
                    ? Carbon::parse($user->membership_expires_at) 
                    : Carbon::now();

                // Perbarui data user di database
                $user->update([
                    'membership_expires_at' => $currentExpiry->addDays((int)$daysToAdd)
                ]);
            }
        });
        
        return redirect()->route('dashboard')->with('success', 'Akses Premium Aktif!');
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