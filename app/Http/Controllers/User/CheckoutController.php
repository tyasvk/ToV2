<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction; 
use App\Models\Tryout;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Midtrans\Config;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    /**
     * PROSES 1: MENYIMPAN TRANSAKSI (STORE)
     * Ini dijalankan saat tombol "Lanjut Pembayaran" diklik.
     */
    public function store(Request $request, Tryout $tryout)
    {
        // 1. Validasi Input
        $request->validate([
            'emails' => 'array', // List email teman
            'payment_method' => 'string'
        ]);

        $user = auth()->user();

        // 2. HITUNG HARGA TOTAL (PENTING UNTUK KOLEKTIF)
        // Jumlah = 1 (Diri sendiri) + Jumlah teman
        $jumlahPeserta = 1 + count($request->emails ?? []);
        $totalHarga = $tryout->price * $jumlahPeserta;

        // 3. Buat Reference Unik
        $reference = 'TRX-' . $user->id . '-' . time() . '-' . strtoupper(Str::random(4));

        // 4. SIMPAN KE DATABASE
        // Pastikan model Transaction Anda punya kolom 'total_amount' dan 'details'
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'tryout_id' => $tryout->id,
            'reference' => $reference,
            'total_amount' => $totalHarga, // <--- INI KUNCINYA (Harga Kolektif)
            'status' => 'pending',
            'payment_method' => $request->payment_method ?? 'midtrans',
            'snap_token' => null,
            'details' => json_encode($request->emails ?? []) // Simpan email teman
        ]);

        // 5. REDIRECT KE HALAMAN CHECKOUT
        // Kita arahkan ke route 'checkout.show' dengan membawa ID TRANSAKSI yang baru dibuat
        return redirect()->route('checkout.show', $transaction->id);
    }

    /**
     * PROSES 2: MENAMPILKAN HALAMAN BAYAR (SHOW)
     * URL: /checkout/{id}
     */
    public function show($id)
    {
        // 1. Cari Transaksi berdasarkan ID (Bukan ID Tryout)
        $transaction = Transaction::with('tryout')->findOrFail($id);

        // 2. Validasi Pemilik (Security Check)
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action. Transaksi ini bukan milik Anda.');
        }

        // 3. Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');

        // 4. Generate Snap Token (Jika belum ada)
        if (empty($transaction->snap_token)) {
            $params = [
                'transaction_details' => [
                    'order_id' => $transaction->reference,
                    'gross_amount' => (int) $transaction->total_amount, // AMBIL DARI DATABASE
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                ],
                'item_details' => [
                    [
                        'id' => $transaction->tryout_id,
                        'price' => (int) $transaction->total_amount, // Harga Total Paket
                        'quantity' => 1,
                        'name' => "Paket Tryout (Kolektif)",
                    ]
                ]
            ];

            try {
                $snapToken = Snap::getSnapToken($params);
                $transaction->snap_token = $snapToken;
                $transaction->save();
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        // 5. Kirim data ke Vue
        return Inertia::render('User/Checkout/Show', [
            'transaction' => $transaction,
            'snapToken' => $transaction->snap_token,
            'midtransClientKey' => config('services.midtrans.client_key')
        ]);
    }
}