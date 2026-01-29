<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Midtrans\Config;
use Midtrans\Snap;

class WalletController extends Controller
{
    public function index()
    {
        return Inertia::render('User/Wallet/Index', [
            'balance' => auth()->user()->balance,
            'transactions' => WalletTransaction::where('user_id', auth()->id())->latest()->get(),
            // Kirim Client Key untuk frontend
            'midtrans_client_key' => config('services.midtrans.client_key'), 
        ]);
    }

    public function topUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
        ]);

        $user = auth()->user();
        $orderId = 'TOPUP-' . time() . '-' . $user->id;

        // 1. Simpan Transaksi Pending
        WalletTransaction::create([
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => $request->amount,
            'description' => 'Top Up Saldo #' . $orderId,
            'status' => 'pending',
            'proof_payment' => $orderId, // Simpan Order ID sementara di sini
        ]);

        // 2. Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $request->amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
        ];

        try {
            // 3. Dapatkan Snap Token
            $snapToken = Snap::getSnapToken($params);
            
            // 4. Kirim Token ke Frontend via Flash Session
            return back()->with('snapToken', $snapToken);
            
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Method Baru: Bayar Transaksi Pending dari History
     */
    public function payPending(WalletTransaction $transaction)
    {
        // 1. Validasi Kepemilikan & Status
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }
        if ($transaction->status !== 'pending') {
            return back()->withErrors(['message' => 'Transaksi ini sudah tidak pending.']);
        }

        // 2. Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // 3. Gunakan Order ID yang lama (disimpan di proof_payment)
        // Midtrans pintar: jika order_id sama dan belum dibayar, dia kembalikan token yang sama.
        $params = [
            'transaction_details' => [
                'order_id' => $transaction->proof_payment, 
                'gross_amount' => (int) $transaction->amount,
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            
            // Kirim token kembali ke frontend
            return back()->with('snapToken', $snapToken);
            
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Error Midtrans: ' . $e->getMessage()]);
        }
    }
}