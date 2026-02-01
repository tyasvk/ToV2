<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
// Jika Anda mengurus logic Snap Token di sini, uncomment baris bawah:
// use Midtrans\Snap;
// use Midtrans\Config;

class CheckoutController extends Controller
{
    /**
     * Menampilkan halaman checkout berdasarkan ID Transaksi.
     * * @param Transaction $transaction
     * @return \Inertia\Response
     */
    public function show(Transaction $transaction)
    {
        // 1. Validasi Keamanan:
        // Pastikan user yang sedang login adalah pemilik transaksi ini.
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
        }

        // 2. Cek Status Pembayaran (Opsional):
        // Jika transaksi sudah dibayar, lempar kembali ke halaman dashboard/history
        if (in_array($transaction->status, ['paid', 'success'])) {
            return redirect()->route('tryout.index')
                ->with('message', 'Transaksi ini sudah berhasil dibayar.');
        }

        // 3. Load Relasi:
        // Kita perlu data 'tryout' (judul, dll) untuk ditampilkan di struk belanja.
        $transaction->load('tryout');

        // 4. (Opsional) Generate Midtrans Snap Token
        // Jika Anda belum men-generate snap_token saat 'create' di TryoutController,
        // Anda bisa melakukannya di sini agar token selalu tersedia saat halaman dibuka.
        /*
        if ($transaction->status === 'pending' && empty($transaction->snap_token)) {
            // Konfigurasi Midtrans
            Config::$serverKey = config('services.midtrans.server_key');
            Config::$isProduction = config('services.midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $transaction->invoice_code,
                    'gross_amount' => (int) $transaction->amount,
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                ],
            ];

            try {
                $snapToken = Snap::getSnapToken($params);
                $transaction->snap_token = $snapToken;
                $transaction->save();
            } catch (\Exception $e) {
                // Handle error jika koneksi ke Midtrans gagal
            }
        }
        */

        // 5. Render ke Inertia
        // Frontend 'User/Checkout/Show' sekarang akan menerima prop 'transaction'
        // yang berisi 'amount' yang benar (bukan 0).
        return Inertia::render('User/Checkout/Show', [
            'transaction' => $transaction,
        ]);
    }
}