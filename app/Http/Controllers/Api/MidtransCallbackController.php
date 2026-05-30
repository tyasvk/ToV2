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
                $transaction->update(['status' => 'paid']);

                $buyer = User::find($transaction->user_id);

                // ==========================================================
                // SKEMA AMAN MIDTRANS: EVALUASI TRANSAKSI PERTAMA BERDASARKAN ID TERLAMA
                // ==========================================================
                $firstPaidTxId = Transaction::where('user_id', $buyer->id)
                    ->whereIn('status', ['paid', 'success'])
                    ->orderBy('created_at', 'asc')
                    ->value('id');

                if ($firstPaidTxId == $transaction->id && $buyer->referred_by) {
                    $upline = User::find($buyer->referred_by);
                    if ($upline) {
                        // Pemilik kode mendapat Rp 2.500 (Pendaftaran) + Rp 2.000 (Tryout Pertama) = Rp 4.500
                        $upline->increment('affiliate_balance', 4500);
                    }
                } else {
                    // Pembelian kedua dan seterusnya: Komisi Rp 2.000 masuk ke pemilik token transaksi
                    if ($transaction->referrer_id) {
                        $referrer = User::find($transaction->referrer_id);
                        if ($referrer) {
                            $referrer->increment('affiliate_balance', 2000);
                        }
                    }
                }

                if (!$transaction->tryout_id) {
                    $daysToAdd = $transaction->metadata['days'] ?? 30; 

                    $currentExpiry = ($buyer->membership_expires_at && Carbon::parse($buyer->membership_expires_at)->isFuture()) 
                        ? Carbon::parse($buyer->membership_expires_at) 
                        : now();

                    $buyer->membership_expires_at = $currentExpiry->addDays($daysToAdd);
                    $buyer->save();
                    
                    Log::info("Membership user {$buyer->id} aktif via Midtrans selama {$daysToAdd} hari.");
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