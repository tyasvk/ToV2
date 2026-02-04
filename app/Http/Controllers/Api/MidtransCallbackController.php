<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\Transaction; // Tambahkan model Transaction
use App\Models\User;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;
use Carbon\Carbon;

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
        $orderId = $notif->order_id;

        // 1. CEK APAKAH INI TRANSAKSI TOP UP WALLET
        $walletTx = WalletTransaction::where('proof_payment', $orderId)->first();
        if ($walletTx) {
            return $this->handleWalletTopUp($walletTx, $transactionStatus);
        }

        // 2. CEK APAKAH INI TRANSAKSI PEMBELIAN (TRYOUT ATAU MEMBERSHIP)
        $tx = Transaction::where('invoice_code', $orderId)->first();
        if ($tx) {
            return $this->handleGeneralPurchase($tx, $transactionStatus);
        }

        return response()->json(['message' => 'Transaction not found'], 404);
    }

    // Cari method handleGeneralPurchase dan tambahkan logika referrer
private function handleGeneralPurchase($transaction, $status)
{
    if ($transaction->status == 'paid') return response()->json(['message' => 'Already processed']);

    if ($status == 'capture' || $status == 'settlement') {
        DB::transaction(function () use ($transaction) {
            $transaction->update(['status' => 'paid']);

            // TAMBAHKAN INI: Komisi Afiliasi
            if ($transaction->referrer_id && $transaction->affiliate_commission > 0) {
                User::find($transaction->referrer_id)->increment('affiliate_balance', $transaction->affiliate_commission);
            }

            // Logika Membership (Tetap ada)
            if (str_starts_with($transaction->invoice_code, 'MEMB-')) {
                $user = User::find($transaction->user_id);
                $daysToAdd = $transaction->details['membership_days'] ?? 0;
                if ($daysToAdd > 0) {
                    $currentExpiry = ($user->membership_expires_at && Carbon::parse($user->membership_expires_at)->isFuture()) 
                        ? $user->membership_expires_at 
                        : now();
                    $user->membership_expires_at = Carbon::parse($currentExpiry)->addDays($daysToAdd);
                    $user->save();
                }
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