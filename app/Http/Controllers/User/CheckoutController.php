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
    public function show(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        if (in_array($transaction->status, ['paid', 'success'])) {
            return redirect()->route('tryout.adidaya')->with('success', 'Transaksi ini sudah dibayar.');
        }

        $transaction->load(['tryout']); 

        // Mapping Nama Nusantara
        $planMapping = [
            'sprint flash'  => 'Prawira',
            'mastery plan'  => 'Wiranata',
            'ultimate pass' => 'Mahapatih',
            'standard pro'  => 'Satria',
        ];

        $rawPlanName = $transaction->metadata['plan_name'] ?? '';
        $lookupKey = strtolower(trim($rawPlanName));
        $nusantaraName = $planMapping[$lookupKey] ?? $rawPlanName;

        $itemName = $transaction->tryout?->title 
            ?? $nusantaraName 
            ?? $transaction->description 
            ?? 'Paket Belajar';

        if (empty($transaction->snap_token)) {
            $this->initMidtrans();
            $params = [
                'transaction_details' => [
                    'order_id' => $transaction->invoice_code,
                    'gross_amount' => (int) $transaction->amount,
                ],
                'item_details' => [
                    [
                        'id' => $transaction->id,
                        'price' => (int) $transaction->amount,
                        'quantity' => 1,
                        'name' => "Nusantara: " . substr($itemName, 0, 39),
                    ]
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                ],
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
                'invoice_code' => $transaction->invoice_code,
                'snap_token' => $transaction->snap_token,
                'description' => $itemName,
                'tryout_id' => $transaction->tryout_id, // Untuk filter Dompet di Vue
            ],
            'user_balance' => auth()->user()->balance,
        ]);
    }

public function process(Request $request, Transaction $transaction)
{
    $request->validate(['payment_method' => 'required|in:wallet,midtrans']);
    
    // Ambil user terbaru langsung dari database
    $user = User::find(auth()->id());

    if ($request->payment_method === 'wallet') {
        if ($user->balance < $transaction->amount) {
            return back()->withErrors(['message' => 'Saldo dompet tidak mencukupi.']);
        }

        try {
            DB::beginTransaction();

            // 1. Potong Saldo
            $user->balance = $user->balance - $transaction->amount;
            
            // 2. Update Status Transaksi
            $transaction->status = 'paid';
            $transaction->payment_method = 'wallet';
            $transaction->save();

            // 3. AKTIVASI MEMBERSHIP (LOGIKA DIPERKUAT)
            // Cek jika ini transaksi membership (bukan beli tryout satuan)
            if ($transaction->tryout_id === null) {
                
                // Ambil durasi dari metadata, default ke 30 hari jika gagal baca
                $days = isset($transaction->metadata['days']) ? (int)$transaction->metadata['days'] : 30;
                
                // Tentukan tanggal mulai (Start Date)
                $startDate = now();
                if ($user->membership_expires_at && $user->membership_expires_at > now()) {
                    $startDate = $user->membership_expires_at;
                }

                // Set Tanggal Baru
                $user->membership_expires_at = $startDate->addDays($days);
            }

            // 4. SIMPAN USER (SANGAT PENTING)
            $user->save();

            // 5. Catat Wallet Transaction
            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $transaction->amount,
                'description' => 'Pembayaran ' . ($transaction->description ?? 'Membership'),
                'status' => 'success'
            ]);

            DB::commit();

            // 6. Redirect Paksa ke Dashboard agar Frontend Refresh
            return redirect()->route('dashboard')->with('success', 'Aktivasi Berhasil! Status Anda sudah aktif.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['message' => 'Gagal Aktivasi: ' . $e->getMessage()]);
        }
    }

    return back()->with('success', 'Instruksi pembayaran dibuat.');
}

    private function initMidtrans()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }
}