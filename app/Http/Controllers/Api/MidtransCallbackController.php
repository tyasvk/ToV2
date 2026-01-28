<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransCallbackController extends Controller
{
    public function handle(Request $request)
    {
        // Konfigurasi Midtrans
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
        $orderId = $notif->order_id; // Ini Order ID yang kita buat di WalletController

        // Cari transaksi di DB berdasarkan Order ID (disimpan di proof_payment)
        $transaction = WalletTransaction::where('proof_payment', $orderId)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Jika sudah success, jangan diproses lagi
        if ($transaction->status == 'success') {
            return response()->json(['message' => 'Transaction already processed'], 200);
        }

        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            // 1. Update Status Transaksi
            $transaction->update(['status' => 'success']);

            // 2. Tambah Saldo User
            $user = User::find($transaction->user_id);
            $user->balance += $transaction->amount;
            $user->save();

        } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $transaction->update(['status' => 'failed']);
        } else if ($transactionStatus == 'pending') {
            $transaction->update(['status' => 'pending']);
        }

        return response()->json(['message' => 'Callback processed']);
    }
}