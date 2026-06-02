<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransCallbackController extends Controller
{
 public function handle(Request $request)
    {
        // 1. Catat payload mentah dari Midtrans ke laravel.log
        Log::info('--- WEBHOOK MIDTRANS MASUK ---');
        Log::info($request->all());

        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        try {
            $notif = new Notification();
            Log::info("Status Transaksi Midtrans: " . $notif->transaction_status);
        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage());
            return response()->json(['message' => 'Invalid Notification'], 400);
        }

        $transactionStatus = $notif->transaction_status;
        $orderIdMidtrans = $notif->order_id;
        
        Log::info("Mencari Order ID: " . $orderIdMidtrans);

        $invoiceCode = explode('-', $orderIdMidtrans)[0];

        $walletTx = WalletTransaction::where('proof_payment', $orderIdMidtrans)->first();
        if ($walletTx) {
            Log::info("Ketemu di WalletTransaction. Memproses Top Up...");
            return $this->handleWalletTopUp($walletTx, $transactionStatus);
        }

        $tx = Transaction::where('invoice_code', $invoiceCode)->first();
        if ($tx) {
            Log::info("Ketemu di Transaction biasa. Memproses Pembelian...");
            return $this->handleGeneralPurchase($tx, $transactionStatus);
        }

        Log::warning('Data Transaksi Tidak Ditemukan di Database!');
        return response()->json(['message' => 'Transaction not found'], 404);
    }

    /**
     * Helper untuk memproses komisi pendaftaran Rp 2.500 ke Upline
     * BERDASARKAN PENGECEKAN RIWAYAT SALDO (ANTI GAGAL / ANTI DOBEL)
     */
    private function processRegistrationCommission($buyer)
    {
        if (empty($buyer->referred_by)) return;

        $upline = User::where('id', $buyer->referred_by)
                      ->orWhere('affiliate_code', $buyer->referred_by)
                      ->first();

        if (!$upline) return;

        // Deskripsi unik yang mengikat nama dan ID pembeli untuk mencegah komisi ganda
        $descriptionTarget = "Komisi pendaftaran (User Aktif) dari: {$buyer->name} - ID:{$buyer->id}";

        // Cek apakah Upline SUDAH PERNAH menerima komisi ini sebelumnya
        $hasReceivedCommission = WalletTransaction::where('user_id', $upline->id)
            ->where('type', 'commission')
            ->where('description', $descriptionTarget)
            ->where('status', 'success')
            ->exists();

        // Jika belum pernah dapat, berikan komisinya sekarang
        if (!$hasReceivedCommission) {
            $upline->increment('affiliate_balance', 2500);
            
            WalletTransaction::create([
                'user_id' => $upline->id,
                'amount' => 2500,
                'type' => 'commission', 
                'description' => $descriptionTarget,
                'status' => 'success'
            ]);
            
            Log::info("Midtrans: Komisi Pendaftaran Rp 2.500 masuk ke Upline ID: {$upline->id} dari Downline ID: {$buyer->id}");
        }
    }

    private function handleGeneralPurchase($transaction, $status)
    {
        if ($transaction->status == 'paid') return response()->json(['message' => 'Already processed']);

        if ($status == 'capture' || $status == 'settlement') {
            DB::transaction(function () use ($transaction) {
                // 1. Ubah status transaksi menjadi paid
                $transaction->update(['status' => 'paid']);

                $buyer = User::find($transaction->user_id);

                // ----------------------------------------------------------
                // ATURAN 1: KOMISI PENDAFTARAN AFILIASI 
                // ----------------------------------------------------------
                $this->processRegistrationCommission($buyer);

                // ----------------------------------------------------------
                // ATURAN 2: KOMISI KODE VOUCHER / TOKEN VIA MIDTRANS (Rp 2.000)
                // ----------------------------------------------------------
                if (!empty($transaction->referrer_id)) {
                    $referrer = User::where('id', $transaction->referrer_id)
                                    ->orWhere('affiliate_code', $transaction->referrer_id)
                                    ->first();

                    if ($referrer) {
                        $referrer->increment('affiliate_balance', 2000);
                        
                        WalletTransaction::create([
                            'user_id' => $referrer->id,
                            'amount' => 2000,
                            'type' => 'commission',
                            'description' => "Komisi penggunaan kode voucher dari: {$buyer->name}",
                            'status' => 'success'
                        ]);
                        
                        Log::info("Midtrans: Komisi Voucher Rp 2.000 masuk ke Referrer ID: {$referrer->id}");
                    }
                }

                // ----------------------------------------------------------
                // 3. LOGIKA MEMBERSHIP / TRYOUT
                // ----------------------------------------------------------
                if (!$transaction->tryout_id) {
                    $daysToAdd = $transaction->metadata['days'] ?? 30; 

                    $currentExpiry = ($buyer->membership_expires_at && Carbon::parse($buyer->membership_expires_at)->isFuture()) 
                        ? Carbon::parse($buyer->membership_expires_at) 
                        : now();

                    $buyer->membership_expires_at = $currentExpiry->addDays($daysToAdd);
                    $buyer->save();
                }
            });
        } else if (in_array($status, ['cancel', 'deny', 'expire'])) {
            $transaction->update(['status' => 'failed']);
        }
        
        return response()->json(['message' => 'Purchase processed']);
    }

    private function handleWalletTopUp($transaction, $status)
    {
        if ($transaction->status == 'success') return response()->json(['message' => 'Already processed']);

        if ($status == 'capture' || $status == 'settlement') {
            DB::transaction(function () use ($transaction) {
                // 1. Update status top up dompet menjadi success
                $transaction->update(['status' => 'success']);
                
                // 2. Tambahkan saldo utama user
                $user = User::find($transaction->user_id);
                $user->increment('balance', $transaction->amount);

                // ----------------------------------------------------------
                // ATURAN 1: KOMISI PENDAFTARAN AFILIASI VIA TOP UP DOMPET
                // ----------------------------------------------------------
                $this->processRegistrationCommission($user);
            });
        } else if (in_array($status, ['cancel', 'deny', 'expire'])) {
            $transaction->update(['status' => 'failed']);
        }
        
        return response()->json(['message' => 'Wallet Top Up processed']);
    }
}