<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Midtrans\Config;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    /**
     * Menampilkan halaman konfirmasi pembayaran & Generate Snap Token.
     */
    public function show(Tryout $tryout)
    {
        // 1. Validasi Keamanan: Pastikan paket memang berbayar
        if (!$tryout->is_paid || $tryout->price <= 0) {
            return redirect()->route('tryout.index')->with('error', 'Paket ini gratis atau harga belum diatur.');
        }

        // 2. Cek apakah user sudah punya akses (mencegah beli 2 kali)
        $alreadyPurchased = DB::table('purchases')
            ->where('user_id', auth()->id())
            ->where('tryout_id', $tryout->id)
            ->where('status', 'success')
            ->exists();

        if ($alreadyPurchased) {
            return redirect()->route('tryout.show', $tryout->id)->with('message', 'Anda sudah memiliki akses paket ini.');
        }

        // 3. Konfigurasi Midtrans dari config/services.php
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');

        // 4. Buat Order ID unik (Format: TRX-USERID-TRYOUTID-TIMESTAMP)
        $orderId = 'TRX-' . auth()->id() . '-' . $tryout->id . '-' . time();

        // 5. Siapkan Parameter Transaksi
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $tryout->price, // Harus integer & > 0
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ],
            'item_details' => [
                [
                    'id' => $tryout->id,
                    'price' => (int) $tryout->price,
                    'quantity' => 1,
                    'name' => "Akses: " . substr($tryout->title, 0, 40),
                ]
            ],
            // Opsional: Arahkan user setelah bayar di portal Midtrans
            'callbacks' => [
                'finish' => route('tryout.index'),
            ]
        ];

        try {
            // 6. Dapatkan Snap Token
            $snapToken = Snap::getSnapToken($params);

            return Inertia::render('User/Checkout/Show', [
                'tryout' => $tryout,
                'snapToken' => $snapToken,
                'midtransClientKey' => config('services.midtrans.client_key')
            ]);
        } catch (\Exception $e) {
            return redirect()->route('tryout.index')->with('error', 'Gagal terhubung ke Midtrans: ' . $e->getMessage());
        }
    }

    /**
     * Menangani Webhook/Notification dari Midtrans (Asynchronous).
     */
    public function handleNotification(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        
        // 1. Verifikasi Signature Key untuk keamanan
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // 2. Ambil data transaksi
        $transaction = $request->transaction_status;
        $type = $request->payment_type;
        $orderId = $request->order_id;

        // 3. Logika Update Status
        if ($transaction == 'settlement' || $transaction == 'capture') {
            
            // Parsing Order ID untuk mendapatkan User ID & Tryout ID
            $orderData = explode('-', $orderId);
            $userId = $orderData[1];
            $tryoutId = $orderData[2];

            // Berikan Akses (Update/Insert ke tabel purchases)
            DB::table('purchases')->updateOrInsert(
                [
                    'user_id' => $userId, 
                    'tryout_id' => $tryoutId
                ],
                [
                    'status' => 'success',
                    'payment_method' => $type,
                    'amount' => $request->gross_amount,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        return response()->json(['message' => 'Notification processed']);
    }
}