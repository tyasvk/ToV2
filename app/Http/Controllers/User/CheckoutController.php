<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Tryout;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Midtrans\Snap;
use Midtrans\Config;

class CheckoutController extends Controller
{
    /**
     * Menampilkan halaman checkout berdasarkan ID Transaksi.
     * Mendukung transaksi Tryout dan Membership.
     */
    public function show(Transaction $transaction)
    {
        // 1. Validasi Keamanan: Pastikan pemilik transaksi adalah user yang login
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
        }

        // 2. Jika transaksi sudah berhasil, arahkan ke halaman yang sesuai
        if (in_array($transaction->status, ['paid', 'success'])) {
            $route = $transaction->tryout_id ? 'tryout.my' : 'dashboard';
            return redirect()->route($route)->with('success', 'Transaksi ini sudah berhasil dibayar.');
        }

        // 3. Tentukan Nama Item (Tryout atau Membership)
        $itemName = '';
        if ($transaction->tryout_id) {
            // Jika transaksi Tryout
            $transaction->load('tryout');
            $itemName = $transaction->tryout->title;
        } else {
            // Jika transaksi Membership (tryout_id adalah null)
            $itemName = $transaction->description ?? 'Membership Premium';
        }

        // 4. Generate Midtrans Snap Token jika belum ada
        if (empty($transaction->snap_token)) {
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
                'item_details' => [
                    [
                        'id' => $transaction->id,
                        'price' => (int) $transaction->amount,
                        'quantity' => 1,
                        'name' => $itemName,
                    ]
                ]
            ];

            try {
                $snapToken = Snap::getSnapToken($params);
                $transaction->update(['snap_token' => $snapToken]);
            } catch (\Exception $e) {
                // Biarkan snap_token kosong jika gagal, frontend akan menangani error
            }
        }

        // 5. Render ke Inertia dengan prop yang dibutuhkan
        return Inertia::render('User/Checkout/Show', [
            'transaction' => $transaction,
            'item_name'   => $itemName, // Dikirim secara eksplisit agar Vue tidak bingung
            'snap_token'  => $transaction->snap_token
        ]);
    }

    /**
     * Callback internal jika Anda menggunakan update status manual dari frontend
     */
    public function callbackInternal(Request $request)
    {
        $transaction = Transaction::where('invoice_code', $request->order_id)->firstOrFail();
        
        if ($request->status === 'success') {
            $transaction->update(['status' => 'paid']);
            // Logic tambahan (seperti aktivasi membership) sebaiknya dilakukan di Midtrans Webhook
        }

        return response()->json(['message' => 'Status updated']);
    }
}