<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\WalletTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    private function resolveItemName($transaction)
    {
        $planMapping = [
            'sprint flash'  => 'Prawira',
            'mastery plan'  => 'Wiranata',
            'ultimate pass' => 'Mahapatih',
            'standard pro'  => 'Satria',
        ];
        $rawPlanName = $transaction->metadata['plan_name'] ?? '';
        $nusantaraName = $planMapping[strtolower(trim($rawPlanName))] ?? $rawPlanName;
        return $transaction->tryout?->title ?? $nusantaraName ?? $transaction->description ?? 'Paket Belajar';
    }

    public function show(Transaction $transaction)
    {
        if ($transaction->user_id !== auth()->id()) abort(403);

        $user = User::find(auth()->id());
        $itemName = $this->resolveItemName($transaction);

        if (in_array($transaction->status, ['paid', 'success'])) {
            return redirect()->route('dashboard')->with('success', "Akses $itemName Aktif!");
        }

        $transaction->load(['tryout']); 

        $jumlahOrang = $transaction->metadata['jumlah_orang'] ?? 1;
        $tokenAfiliasi = $transaction->metadata['token_afiliasi'] ?? null;
        
        $hargaDasar = $transaction->metadata['base_price'] ?? $transaction->amount;
        $totalHarga = $hargaDasar * $jumlahOrang;
        $persenDiskon = 0;

        if ($jumlahOrang == 2) $persenDiskon = 0.10;
        elseif ($jumlahOrang == 3) $persenDiskon = 0.15;
        elseif ($jumlahOrang == 4) $persenDiskon = 0.20;
        elseif ($jumlahOrang >= 5) $persenDiskon = 0.25;

        $hargaSetelahGrup = $totalHarga - ($totalHarga * $persenDiskon);
        $potonganToken = 0;

        if ($tokenAfiliasi) {
            $pemilikToken = User::where('affiliate_code', trim($tokenAfiliasi))->first();
            
            if ($pemilikToken) {
                if ($pemilikToken->id !== $user->id) {
                    $potonganToken = 2000;
                    
                    if (!$transaction->referrer_id) {
                        $transaction->update(['referrer_id' => $pemilikToken->id]);
                    }
                } else {
                    session()->flash('error', 'Anda tidak dapat menggunakan kode afiliasi Anda sendiri.');
                    $tokenAfiliasi = null; 
                }
            } else {
                session()->flash('error', 'Kode voucher atau token afiliasi tidak valid / tidak ditemukan.');
                $tokenAfiliasi = null;
            }
        }

        $finalTagihan = $hargaSetelahGrup - $potonganToken;
        
        if ($transaction->amount != $finalTagihan) {
            $transaction->update(['amount' => $finalTagihan]);
        }

        $fee = ceil($finalTagihan * 0.007 * 1.11); 
        $totalToPay = (int) ($finalTagihan + $fee);
        
        $transaction->update(['total_amount' => $totalToPay]);

        if (empty($transaction->snap_token) || $tokenAfiliasi === null) {
            $this->initMidtrans();
            $params = [
                'transaction_details' => [
                    'order_id' => $transaction->invoice_code . '-' . time(),
                    'gross_amount' => $totalToPay
                ],
                'item_details' => [
                    ['id' => $transaction->id, 'price' => (int) $finalTagihan, 'quantity' => 1, 'name' => substr($itemName, 0, 30)],
                    ['id' => 'FEE', 'price' => (int) $fee, 'quantity' => 1, 'name' => 'Biaya Layanan']
                ],
                'customer_details' => ['first_name' => $user->name, 'email' => $user->email],
            ];
            try {
                $snapToken = Snap::getSnapToken($params);
                $transaction->update(['snap_token' => $snapToken]);
            } catch (\Exception $e) {}
        }

        return Inertia::render('User/Checkout/Show', [
            'transaction' => [
                'id' => $transaction->id, 
                'amount' => $transaction->amount, 
                'total_amount' => $transaction->total_amount,
                'invoice_code' => $transaction->invoice_code,
                'snap_token' => $transaction->snap_token, 
                'description' => $itemName, 
                'tryout_id' => $transaction->tryout_id,
            ],
            'user_balance' => $user->balance,
        ]);
    }

    public function process(Request $request, Transaction $transaction)
    {
        $request->validate(['payment_method' => 'required|in:wallet,midtrans']);
        
        if ($request->payment_method === 'wallet') {
            $user = User::find(auth()->id());
            if ($user->balance < $transaction->amount) {
                return back()->withErrors(['message' => 'Saldo tidak cukup.']);
            }

            DB::transaction(function () use ($user, $transaction) {
                $user->decrement('balance', $transaction->amount);
                
                $transaction->update(['status' => 'paid', 'payment_method' => 'wallet']);
                
                WalletTransaction::create([
                    'user_id' => $user->id, 
                    'type' => 'debit', 
                    'amount' => $transaction->amount,
                    'description' => 'Pembayaran ' . $this->resolveItemName($transaction), 
                    'status' => 'success'
                ]);

                // ==========================================================
                // SKEMA AMAN: EVALUASI TRANSAKSI PERTAMA BERDASARKAN ID TERLAMA
                // ==========================================================
                $firstPaidTxId = Transaction::where('user_id', $user->id)
                    ->whereIn('status', ['paid', 'success'])
                    ->orderBy('created_at', 'asc')
                    ->value('id');

                if ($firstPaidTxId == $transaction->id && $user->referred_by) {
                    $upline = User::find($user->referred_by);
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

                if (!$transaction->tryout_id || (isset($transaction->metadata['type']) && $transaction->metadata['type'] === 'membership')) {
                    $daysToAdd = $transaction->metadata['days'] ?? $transaction->metadata['duration_days'] ?? 30; 

                    $currentExpiry = ($user->membership_expires_at && Carbon::parse($user->membership_expires_at)->isFuture()) 
                        ? Carbon::parse($user->membership_expires_at) 
                        : Carbon::now();

                    $user->update([
                        'membership_expires_at' => $currentExpiry->addDays((int)$daysToAdd)
                    ]);
                }
            });
            
            return redirect()->route('dashboard')->with('success', 'Akses Premium Aktif!');
        }
        return back();
    }

    private function initMidtrans() {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true; 
        Config::$is3ds = true;
    }
}