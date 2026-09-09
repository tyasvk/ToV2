<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Midtrans\Config;
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

        // KUNCI SOLUSI OCTANE:
        // Kita bypass 'new Notification()' dan langsung pakai $request bawaan Laravel
        $notif = (object) $request->all();

        try {
            if (!isset($notif->order_id)) {
                throw new \Exception("Order ID kosong atau payload tidak valid dari Octane");
            }
            Log::info("Status Transaksi Midtrans: " . $notif->transaction_status);
        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage());
            return response()->json(['message' => 'Invalid Notification'], 400);
        }

        $transactionStatus = $notif->transaction_status;
        $orderIdMidtrans = $notif->order_id;
        
        Log::info("Mencari Order ID: " . $orderIdMidtrans);

        // ==========================================
        // KODE MEMISAHKAN TIMESTAMP DARI INVOICE
        // ==========================================
        $parts = explode('-', $orderIdMidtrans);
        
        // Jika ada lebih dari 1 bagian (berarti ada strip/hyphen), buang bagian paling belakang (timestamp)
        if (count($parts) > 1) {
            array_pop($parts); 
        }
        
        // Gabungkan kembali sisa teksnya (Misal: MEMB-ABCDEFGHIJ)
        $invoiceCode = implode('-', $parts);
        // ==========================================

        // CEK 1: Apakah ini transaksi Top Up Dompet?
        // Top Up mencari berdasarkan Order ID lengkap (proof_payment)
        $walletTx = WalletTransaction::where('proof_payment', $orderIdMidtrans)->first();
        if ($walletTx) {
            Log::info("Ketemu di WalletTransaction. Memproses Top Up...");
            return $this->handleWalletTopUp($walletTx, $transactionStatus);
        }

        // CEK 2: Apakah ini transaksi Pembelian Membership/Tryout?
        // Pembelian produk mencari berdasarkan invoice_code tanpa timestamp
        $tx = Transaction::where('invoice_code', $invoiceCode)->first();
        if ($tx) {
            Log::info("Ketemu di Transaction biasa. Memproses Pembelian...");
            return $this->handleGeneralPurchase($tx, $transactionStatus);
        }

        Log::warning('Data Transaksi Tidak Ditemukan di Database! Order ID: ' . $orderIdMidtrans . ' (Mencari Invoice: ' . $invoiceCode . ')');
        return response()->json(['message' => 'Transaction not found'], 404);
    }

    private function handleGeneralPurchase($transaction, $status)
    {
        if (in_array($transaction->status, ['paid', 'success'])) {
            return response()->json(['message' => 'Already processed']);
        }

        if ($status == 'capture' || $status == 'settlement') {
            DB::transaction(function () use ($transaction) {
                // 1. Ubah status transaksi menjadi paid
                $transaction->update(['status' => 'paid', 'payment_method' => 'midtrans']);

                // Jika bundling, ubah status anak-anaknya juga
                if ($transaction->type === 'bundling') {
                    Transaction::where('invoice_code', 'LIKE', $transaction->invoice_code . '-%')
                        ->update(['status' => 'paid', 'payment_method' => 'midtrans']);
                }

                $buyer = User::find($transaction->user_id);
                if (!$buyer) return;

                // ----------------------------------------------------------
                // 2. LOGIKA MEMBERSHIP / TRYOUT
                // ----------------------------------------------------------
                $metadata = is_string($transaction->metadata) ? json_decode($transaction->metadata, true) : $transaction->metadata;
                
                if (isset($metadata['type']) && $metadata['type'] === 'membership') {
                    $daysToAdd = $metadata['days'] ?? 30; 
                    
                    $currentExpiry = ($buyer->membership_expires_at && Carbon::parse($buyer->membership_expires_at)->isFuture()) 
                        ? Carbon::parse($buyer->membership_expires_at) 
                        : now();
                        
                    $buyer->membership_expires_at = $currentExpiry->addDays($daysToAdd);
                    $buyer->save();
                    
                    Log::info("DEBUG MEMBERSHIP: Berhasil aktivasi paket {$metadata['plan_name']} untuk user {$buyer->id} via Midtrans.");
                }

                // ----------------------------------------------------------
                // 3. LOGIKA KOMISI AFILIASI (DAFTAR & VOUCHER) VIA MIDTRANS
                // ----------------------------------------------------------
                Log::info("DEBUG KOMISI MIDTRANS: Memproses transaksi {$transaction->id} untuk user {$buyer->id}");

                // Hitung total *pembelian produk* sukses (status: paid/success) yang memiliki tryout_id
                $totalProductPurchases = Transaction::where('user_id', $buyer->id)
                    ->whereIn('status', ['paid', 'success'])
                    ->whereNotNull('tryout_id') 
                    ->count();

                Log::info("DEBUG KOMISI MIDTRANS: Total Pembelian Produk (Berbayar) = {$totalProductPurchases}");

                // KOMISI 1: PENDAFTARAN / PEMBELIAN PERTAMA (Rp 2.500)
                $komisiPendaftaran = 0;
                $uplinePendaftaran = null;

                if ($totalProductPurchases === 1 && !empty($buyer->referred_by)) {
                    $uplinePendaftaran = User::where('id', $buyer->referred_by)
                                  ->orWhere('affiliate_code', $buyer->referred_by)
                                  ->first();

                    if ($uplinePendaftaran) {
                        $komisiPendaftaran = 2500;
                    }
                }

                // KOMISI 2: PENGGUNAAN VOUCHER (Rp 2.000)
                $komisiVoucher = 0;
                $uplineVoucher = null;

                if (!empty($transaction->referrer_id)) {
                    $uplineVoucher = User::where('id', $transaction->referrer_id)
                                         ->orWhere('affiliate_code', $transaction->referrer_id)
                                         ->first();
                    if ($uplineVoucher) {
                        $komisiVoucher = 2000;
                    }
                }

                // EKSEKUSI TRANSFER SALDO KOMISI
                if ($uplinePendaftaran && $uplineVoucher && $uplinePendaftaran->id === $uplineVoucher->id) {
                    // Skenario Double Komisi (Rp 4.500)
                    $totalKomisi = $komisiPendaftaran + $komisiVoucher; 
                    $uplinePendaftaran->increment('affiliate_balance', $totalKomisi);
                    
                    WalletTransaction::create([
                        'user_id' => $uplinePendaftaran->id,
                        'amount' => $totalKomisi,
                        'type' => 'credit',
                        'description' => "Komisi ekstra (Daftar & Voucher) via Midtrans dari: {$buyer->name}",
                        'status' => 'success'
                    ]);
                    Log::info("DEBUG KOMISI MIDTRANS: SUKSES transfer Rp{$totalKomisi} ke Upline ID {$uplinePendaftaran->id}");
                } else {
                    // Skenario Terpisah atau Hanya Dapat Salah Satu
                    if ($komisiPendaftaran > 0 && $uplinePendaftaran) {
                        $uplinePendaftaran->increment('affiliate_balance', $komisiPendaftaran);
                        WalletTransaction::create([
                            'user_id' => $uplinePendaftaran->id,
                            'amount' => $komisiPendaftaran,
                            'type' => 'credit',
                            'description' => "Komisi pembelian pertama via Midtrans dari referal: {$buyer->name}",
                            'status' => 'success'
                        ]);
                        Log::info("DEBUG KOMISI MIDTRANS: SUKSES transfer Daftar Rp{$komisiPendaftaran} ke Upline ID {$uplinePendaftaran->id}");
                    }

                    if ($komisiVoucher > 0 && $uplineVoucher) {
                        $uplineVoucher->increment('affiliate_balance', $komisiVoucher);
                        WalletTransaction::create([
                            'user_id' => $uplineVoucher->id,
                            'amount' => $komisiVoucher,
                            'type' => 'credit',
                            'description' => "Komisi penggunaan voucher via Midtrans dari: {$buyer->name}",
                            'status' => 'success'
                        ]);
                        Log::info("DEBUG KOMISI MIDTRANS: SUKSES transfer Voucher Rp{$komisiVoucher} ke Upline ID {$uplineVoucher->id}");
                    }
                }
            });

        } else if (in_array($status, ['cancel', 'deny', 'expire'])) {
            $transaction->update(['status' => 'failed']);
            if ($transaction->type === 'bundling') {
                Transaction::where('invoice_code', 'LIKE', $transaction->invoice_code . '-%')
                    ->update(['status' => 'failed']);
            }
        }
        
        return response()->json(['message' => 'Purchase processed']);
    }

    private function handleWalletTopUp($transaction, $status)
    {
        if ($transaction->status == 'success') {
            return response()->json(['message' => 'Already processed']);
        }

        if ($status == 'capture' || $status == 'settlement') {
            DB::transaction(function () use ($transaction) {
                // 1. Update status top up dompet menjadi success
                $transaction->update(['status' => 'success']);
                
                // 2. Tambahkan saldo utama user
                $user = User::find($transaction->user_id);
                if ($user) {
                    $user->increment('balance', $transaction->amount);
                    Log::info("Top Up Berhasil: Saldo user {$user->id} bertambah Rp{$transaction->amount} via Midtrans.");
                }
            });
        } else if (in_array($status, ['cancel', 'deny', 'expire'])) {
            $transaction->update(['status' => 'failed']);
        }
        
        return response()->json(['message' => 'Wallet Top Up processed']);
    }
}