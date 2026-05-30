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
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        try {
            $notif = new Notification();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid Notification'], 400);
        }

        $transactionStatus = $notif->transaction_status;
        $orderIdMidtrans = $notif->order_id;

        $invoiceCode = explode('-', $orderIdMidtrans)[0];

        $walletTx = WalletTransaction::where('proof_payment', $orderIdMidtrans)->first();
        if ($walletTx) {
            return $this->handleWalletTopUp($walletTx, $transactionStatus);
        }

        $tx = Transaction::where('invoice_code', $invoiceCode)->first();
        if ($tx) {
            return $this->handleGeneralPurchase($tx, $transactionStatus);
        }

        return response()->json(['message' => 'Transaction not found'], 404);
    }

    private function handleGeneralPurchase($transaction, $status)
    {
        if ($transaction->status == 'paid') return response()->json(['message' => 'Already processed']);

        if ($status == 'capture' || $status == 'settlement') {
            DB::transaction(function () use ($transaction) {
                // 1. Ubah status transaksi menjadi paid
                $transaction->update(['status' => 'paid']);

                $buyer = User::find($transaction->user_id);

                // 2. HITUNG TOTAL TRANSAKSI LUNAS
                $totalPaidTransactions = Transaction::where('user_id', $buyer->id)
                    ->whereIn('status', ['paid', 'success'])
                    ->count();

                // ----------------------------------------------------------
                // ATURAN 1: KOMISI PENDAFTARAN AFILIASI VIA MIDTRANS (Rp 2.500)
                // ----------------------------------------------------------
                if ($totalPaidTransactions === 1 && !empty($buyer->referred_by)) {
                    
                    // PERBAIKAN: Cari upline berdasarkan ID atau kode afiliasinya
                    $upline = User::where('id', $buyer->referred_by)
                                  ->orWhere('affiliate_code', $buyer->referred_by)
                                  ->first();

                    if ($upline) {
                        $upline->increment('affiliate_balance', 2500);
                        
                        // Opsional namun penting: Catat riwayat dompet (Sesuaikan kolom dengan migrasi Anda)
                        WalletTransaction::create([
                            'user_id' => $upline->id,
                            'amount' => 2500,
                            'type' => 'commission', // Sesuaikan dengan enum/tipe Anda
                            'description' => "Komisi pendaftaran (User Aktif) dari: {$buyer->name}",
                            'status' => 'success'
                        ]);
                        
                        Log::info("Midtrans: Komisi Pendaftaran Rp 2.500 masuk ke Upline ID: {$upline->id}");
                    }
                }

                // ----------------------------------------------------------
                // ATURAN 2: KOMISI KODE VOUCHER / TOKEN VIA MIDTRANS (Rp 2.000)
                // ----------------------------------------------------------
                if (!empty($transaction->referrer_id)) {
                    
                    // Jika referrer_id di tabel transactions bisa berupa kode, gunakan orWhere juga
                    $referrer = User::where('id', $transaction->referrer_id)
                                    ->orWhere('affiliate_code', $transaction->referrer_id)
                                    ->first();

                    if ($referrer) {
                        $referrer->increment('affiliate_balance', 2000);
                        
                        // Opsional namun penting: Catat riwayat dompet
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
            $transaction->update(['status' => 'success']);
            $user = User::find($transaction->user_id);
            $user->increment('balance', $transaction->amount);
        } else if (in_array($status, ['cancel', 'deny', 'expire'])) {
            $transaction->update(['status' => 'failed']);
        }
        
        return response()->json(['message' => 'Wallet Top Up processed']);
    }
}