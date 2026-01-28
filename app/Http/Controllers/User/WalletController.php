<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Midtrans\Config; // Import Midtrans
use Midtrans\Snap;   // Import Midtrans

class WalletController extends Controller
{
    public function index()
    {
        return Inertia::render('User/Wallet/Index', [
            'balance' => auth()->user()->balance,
            'transactions' => WalletTransaction::where('user_id', auth()->id())
                ->latest()
                ->get(),
            // Kirim Client Key ke frontend
            'midtrans_client_key' => config('services.midtrans.client_key'), 
        ]);
    }

    public function topUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
        ]);

        $user = auth()->user();
        
        // 1. Buat kode order unik
        $orderId = 'TOPUP-' . time() . '-' . $user->id;

        // 2. Simpan Transaksi ke Database
        $transaction = WalletTransaction::create([
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => $request->amount,
            'description' => 'Top Up Saldo #' . $orderId,
            'status' => 'pending', 
            'proof_payment' => $orderId, // Kita pakai kolom ini untuk simpan Order ID Midtrans sementara
        ]);

        // 3. Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // 4. Buat Parameter Snap
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

        // 5. Dapatkan Snap Token
        try {
            $snapToken = Snap::getSnapToken($params);
            
            // Kembalikan token ke frontend menggunakan flash session
            return back()->with('snapToken', $snapToken);
            
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Gagal terhubung ke gateway pembayaran: ' . $e->getMessage()]);
        }
    }
}