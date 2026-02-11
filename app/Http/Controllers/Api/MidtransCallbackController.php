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
use Illuminate\Support\Facades\DB; // PERBAIKAN 1: Wajib di-import
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

        /**
         * PERBAIKAN 2: Jika Anda menggunakan timestamp di order_id (misal: INV-123-171234),
         * kita ambil bagian depannya saja untuk mencari di database.
         */
        $invoiceCode = explode('-', $orderIdMidtrans)[0];

        // 1. Cek Transaksi Top Up Wallet
        $walletTx = WalletTransaction::where('proof_payment', $orderIdMidtrans)->first();
        if ($walletTx) {
            return $this->handleWalletTopUp($walletTx, $transactionStatus);
        }

        // 2. Cek Transaksi Pembelian (Tryout atau Membership)
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
                $transaction->update(['status' => 'paid']);

                // Komisi Afiliasi
                if ($transaction->referrer_id && $transaction->affiliate_commission > 0) {
                    User::find($transaction->referrer_id)->increment('affiliate_balance', $transaction->affiliate_commission);
                }

                /**
                 * PERBAIKAN 3: Logika Aktivasi Membership
                 * Kita cek apakah tryout_id kosong (ciri khas transaksi membership di sistem Anda)
                 */
                if (!$transaction->tryout_id) {
                    $user = User::find($transaction->user_id);
                    
                    // Gunakan metadata['days'] agar sesuai dengan CheckoutController
                    $daysToAdd = $transaction->metadata['days'] ?? 30;

                    $currentExpiry = ($user->membership_expires_at && Carbon::parse($user->membership_expires_at)->isFuture()) 
                        ? Carbon::parse($user->membership_expires_at) 
                        : now();

                    $user->membership_expires_at = $currentExpiry->addDays($daysToAdd);
                    $user->save();
                    
                    Log::info("Membership Aktif untuk User: {$user->id} selama {$daysToAdd} hari.");
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
            $user->balance += $transaction->amount;
            $user->save();
        } else if (in_array($status, ['cancel', 'deny', 'expire'])) {
            $transaction->update(['status' => 'failed']);
        }
        
        return response()->json(['message' => 'Wallet Top Up processed']);
    }
}