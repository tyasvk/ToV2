<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Midtrans\Config;
use Midtrans\Snap;

// PASTIKAN KE-4 MODEL INI DI-IMPORT:
use App\Models\Voucher; 
use App\Models\Transaction; 
use App\Models\User;

// PASTIKAN 2 FACADES INI JUGA DI-IMPORT:
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{

public function index()
    {
        $userId = auth()->id();

        // 1. Cek dan ubah transaksi pending yang usianya lebih dari 3 jam menjadi gagal (failed)
        WalletTransaction::where('user_id', $userId)
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subHours(3))
            ->update(['status' => 'failed']);

        // 2. Ambil data transaksi yang sudah ter-update
        $transactions = WalletTransaction::where('user_id', $userId)->latest()->get();

        return Inertia::render('User/Wallet/Index', [
            'balance' => (float) auth()->user()->balance, // <--- TAMBAHKAN (float) DISINI
            'transactions' => $transactions,
            'midtrans_client_key' => config('services.midtrans.client_key'), 
            'snapToken' => session('snapToken'), 
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

 public function claimVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = auth()->user();
        $code = strtoupper($request->code);

        $voucher = Voucher::where('code', $code)->where('is_used', false)->first();

        if (!$voucher) {
            return redirect()->back()->withErrors(['code' => 'Kode voucher tidak valid atau sudah digunakan.']);
        }

        DB::transaction(function () use ($voucher, $user) {
            // 1. Tandai voucher sudah dipakai
            $voucher->update([
                'is_used' => true,
                'used_by' => $user->id,
                'used_at' => now(),
            ]);

            // 2. Tambah saldo utama user
            $user->increment('balance', $voucher->amount);

            // 3. Catat di tabel WalletTransaction agar terhitung sebagai transaksi sukses
            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $voucher->amount,
                'type' => 'topup', // atau 'in' (sesuaikan dengan tipe topup dompet Anda)
                'description' => "Top Up via Voucher Code: {$voucher->code}",
                'status' => 'success'
            ]);

            // -----------------------------------------------------------------
            // CEK KOMISI PENDAFTARAN AFILIASI (Menggunakan Riwayat Upline)
            // -----------------------------------------------------------------
            if (!empty($user->referred_by)) {
                $upline = User::where('id', $user->referred_by)
                              ->orWhere('affiliate_code', $user->referred_by)
                              ->first();

                if ($upline) {
                    // Deskripsi ini HARUS SAMA PERSIS dengan di MidtransCallbackController
                    $descriptionTarget = "Komisi pendaftaran (User Aktif) dari: {$user->name} - ID:{$user->id}";

                    // Cek riwayat dompet upline, apakah sudah pernah dapat dari pengguna ini?
                    $hasReceivedCommission = WalletTransaction::where('user_id', $upline->id)
                        ->where('type', 'commission')
                        ->where('description', $descriptionTarget)
                        ->where('status', 'success')
                        ->exists();

                    if (!$hasReceivedCommission) {
                        $upline->increment('affiliate_balance', 2500);
                        
                        WalletTransaction::create([
                            'user_id' => $upline->id,
                            'amount' => 2500,
                            'type' => 'commission', 
                            'description' => $descriptionTarget,
                            'status' => 'success'
                        ]);
                        
                        Log::info("Klaim Voucher: Komisi Pendaftaran Rp 2.500 masuk ke Upline ID: {$upline->id} dari Downline ID: {$user->id}");
                    }
                }
            }
        });

        return redirect()->back()->with('success', "Berhasil klaim voucher! Saldo sebesar Rp " . number_format($voucher->amount, 0, ',', '.') . " telah ditambahkan.");
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

        // 3. BUAT ORDER ID BARU UNTUK MENGHINDARI ERROR "ORDER_ID SUDAH DIGUNAKAN"
        // Kita tambahkan suffix '-R' dan timestamp agar Midtrans menganggap ini order valid yang baru
        $newOrderId = $transaction->proof_payment . '-R' . time(); 

        $params = [
            'transaction_details' => [
                'order_id' => $newOrderId, // Gunakan ID Baru
                'gross_amount' => (int) $transaction->amount,
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            
            // 4. Update order_id (proof_payment) lama di database dengan yang baru
            $transaction->update(['proof_payment' => $newOrderId]);

            // Kirim token kembali ke frontend
            return back()->with('snapToken', $snapToken);
            
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Error Midtrans: ' . $e->getMessage()]);
        }
    }
}