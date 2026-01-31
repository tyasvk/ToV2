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
     */
    public function store(Request $request, Tryout $tryout)
    {
        $request->validate([
            'emails' => 'array', 
            'payment_method' => 'string'
        ]);

        $user = auth()->user();

        // 1. Hitung Detail Harga & Jumlah
        $jumlahPeserta = 1 + count($request->emails ?? []);
        $totalHarga = $tryout->price * $jumlahPeserta;

        // 2. Buat Invoice Code Unik
        $invoiceCode = 'TRX-' . $user->id . '-' . time() . '-' . strtoupper(Str::random(4));

        // 3. SIMPAN KE DATABASE (Sesuai nama kolom di Migration Anda)
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'tryout_id' => $tryout->id,
            
            // Perbaikan Nama Kolom:
            'invoice_code' => $invoiceCode,       // Database: invoice_code
            'amount' => $totalHarga,              // Database: amount
            'unit_price' => $tryout->price,       // Database: unit_price (Wajib)
            'qty' => $jumlahPeserta,              // Database: qty (Wajib)
            
            'status' => 'pending',
            'snap_token' => null,
            'participants_data' => $request->emails ?? [] // Database: participants_data
        ]);

        return redirect()->route('checkout.show', $transaction->id);
    }

    /**
     * PROSES 2: MENAMPILKAN HALAMAN BAYAR (SHOW)
     */
    public function show($id)
    {
        $transaction = Transaction::with('tryout')->findOrFail($id);

        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action. Transaksi ini bukan milik Anda.');
        }

        // Midtrans Config
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');

        if (empty($transaction->snap_token)) {
            $params = [
                'transaction_details' => [
                    'order_id' => $transaction->invoice_code, // Gunakan invoice_code
                    'gross_amount' => (int) $transaction->amount, // Gunakan amount
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                ],
                'item_details' => [
                    [
                        'id' => $transaction->tryout_id,
                        'price' => (int) $transaction->amount, 
                        'quantity' => 1, // Dianggap 1 bundle transaksi
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

        return Inertia::render('User/Checkout/Show', [
            'transaction' => $transaction,
            'snapToken' => $transaction->snap_token,
            'midtransClientKey' => config('services.midtrans.client_key')
        ]);
    }
    
    /**
     * Callback Internal (Opsional)
     */
    public function callbackInternal(Request $request)
    {
        return redirect()->route('tryout.my')->with('success', 'Pembayaran sedang diproses.');
    }
}