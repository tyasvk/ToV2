<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Models\Tryout; 
use Illuminate\Http\Request;
use Inertia\Inertia;
use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str; 

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

        // Cek apakah token sudah usang (lebih dari 22 jam)
        $isTokenExpired = true;
        if (!empty($transaction->snap_token) && $transaction->updated_at) {
            $isTokenExpired = $transaction->updated_at->diffInHours(now()) >= 22;
        }

        if (empty($transaction->snap_token) || $tokenAfiliasi === null || $isTokenExpired) {
            $this->initMidtrans();
            $params = [
                'transaction_details' => [
                    'order_id' => $transaction->invoice_code . '-' . time(), // Dibuat unik dengan timestamp
                    'gross_amount' => $totalToPay
                ],
                'item_details' => [
                    ['id' => (string) $transaction->id, 'price' => (int) $finalTagihan, 'quantity' => 1, 'name' => substr($itemName, 0, 30)],
                    ['id' => 'FEE', 'price' => (int) $fee, 'quantity' => 1, 'name' => 'Biaya Layanan']
                ],
                'customer_details' => [
                    'first_name' => substr($user->name, 0, 20), 
                    'email' => $user->email
                ],
            ];
            
            try {
                $snapToken = Snap::getSnapToken($params);
                $transaction->update([
                    'snap_token' => $snapToken,
                    'updated_at' => now(), 
                ]);
            } catch (\Exception $e) {
                Log::error('Midtrans Snap Error: ' . $e->getMessage());
            }
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

    public function processBundle(Request $request)
    {
        $request->validate([
            'tryout_ids' => 'required|array|min:3',
            'tryout_ids.*' => 'exists:tryouts,id',
        ], [
            'tryout_ids.min' => 'Anda harus memilih minimal 3 tryout untuk paket bundling.'
        ]);

        $selectedTryouts = Tryout::whereIn('id', $request->tryout_ids)->get();
        $totalPrice = $selectedTryouts->sum('price');
        $invoice = 'INV-' . strtoupper(Str::random(10));

        $parentTransaction = DB::transaction(function () use ($selectedTryouts, $totalPrice, $request, $invoice) {
            
            $transaction = Transaction::create([
                'user_id'        => auth()->id(),
                'invoice_code'   => $invoice,
                'amount'         => $totalPrice, 
                'total_amount'   => $totalPrice,
                'status'         => 'pending',
                'type'           => 'bundling', 
                'payment_method' => $request->payment_method ?? 'transfer',
                'metadata'       => [
                    'is_bundle_parent' => true,
                    'jumlah_orang' => 1,
                    'base_price' => $totalPrice
                ]
            ]);

            foreach ($selectedTryouts as $tryout) {
                Transaction::create([
                    'user_id'        => auth()->id(),
                    'tryout_id'      => $tryout->id,
                    'invoice_code'   => $invoice . '-' . $tryout->id, 
                    'amount'         => 0, 
                    'total_amount'   => 0,
                    'status'         => 'pending',
                    'type'           => 'bundling_item',
                    'payment_method' => $request->payment_method ?? 'transfer',
                    'metadata'       => [
                        'is_bundle_child' => true,
                        'parent_id' => $transaction->id
                    ]
                ]);
            }

            return $transaction;
        });

        return redirect()->route('checkout.show', $parentTransaction->id)
            ->with('success', 'Berhasil membuat pesanan bundling!');
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
                
                if ($transaction->invoice_code && $transaction->type === 'bundling') {
                    Transaction::where('invoice_code', 'LIKE', $transaction->invoice_code . '-%')
                        ->update(['status' => 'paid', 'payment_method' => 'wallet']);
                }
                
                // ============================================================
                // LOGIKA AKTIVASI MEMBERSHIP
                // ============================================================
                $metadata = is_string($transaction->metadata) ? json_decode($transaction->metadata, true) : $transaction->metadata;
                
                if (isset($metadata['type']) && $metadata['type'] === 'membership') {
                    $durationDays = $metadata['days'] ?? 0;
                    
                    // Cek apakah user sudah punya masa aktif sebelumnya
                    $hasActiveMembership = $user->membership_expires_at && Carbon::parse($user->membership_expires_at)->isFuture();
                    $currentExpiry = $hasActiveMembership ? Carbon::parse($user->membership_expires_at) : now();
                    
                    // Tambahkan masa aktif (bypass Mass-Assignment dengan save)
                    $user->membership_expires_at = $currentExpiry->addDays($durationDays);
                    $user->save();
                    
                    Log::info("DEBUG MEMBERSHIP: Berhasil aktivasi paket {$metadata['plan_name']} untuk user {$user->id} via Dompet.");
                }
                // ============================================================

                Log::info("DEBUG KOMISI: Memproses transaksi {$transaction->id} untuk user {$user->id}");

                $totalProductPurchases = Transaction::where('user_id', $user->id)
                    ->whereIn('status', ['paid', 'success'])
                    ->whereNotNull('tryout_id')
                    ->count();

                Log::info("DEBUG KOMISI: Total Pembelian Produk = {$totalProductPurchases}");

                if ($totalProductPurchases === 1 && !empty($user->referred_by)) {
                    $upline = User::where('id', $user->referred_by)
                                  ->orWhere('affiliate_code', $user->referred_by)
                                  ->first();

                    if ($upline) {
                        $upline->increment('affiliate_balance', 2500);
                        WalletTransaction::create([
                            'user_id' => $upline->id,
                            'amount' => 2500,
                            'type' => 'credit',
                            'description' => "Komisi pendaftaran dari: {$user->name}",
                            'status' => 'success'
                        ]);
                        Log::info("DEBUG KOMISI: SUKSES transfer Rp2500 ke Upline ID {$upline->id}");
                    } else {
                        Log::warning("DEBUG KOMISI: GAGAL, Upline dengan ID/Kode {$user->referred_by} tidak ditemukan!");
                    }
                } else {
                     Log::info("DEBUG KOMISI: Komisi dilewati (Pembelian ke-{$totalProductPurchases})");
                }
            });
            
            return redirect()->route('dashboard')->with('success', 'Pembayaran Berhasil! Akses Anda telah aktif.');
        }
        return back();
    }

    private function initMidtrans() 
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true; 
        Config::$is3ds = true;
    }
}