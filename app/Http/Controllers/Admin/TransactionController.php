<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User; // <-- PERBAIKAN: Import Model User
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB; // <-- PERBAIKAN: Import DB Facade
use Carbon\Carbon; // <-- PERBAIKAN: Import Carbon untuk tanggal

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        // 1. Mulai query dengan relasi yang dibutuhkan
        $query = Transaction::query()
            ->with(['user', 'tryout']); 

        // 2. Filter berdasarkan Pencarian (Search)
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_code', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // 3. Filter berdasarkan Status 
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 4. Eksekusi pagination
        $transactions = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Menerima / Menyetujui Transaksi Manual
     */
    public function approve(Transaction $transaction)
    {
        if (in_array($transaction->status, ['paid', 'success'])) {
            return back();
        }

        DB::transaction(function () use ($transaction) {
            $transaction->update(['status' => 'paid']);

            // ============================================================
            // PERBAIKAN: LOGIKA AKTIVASI MEMBERSHIP JIKA DI-APPROVE ADMIN
            // ============================================================
            $metadata = is_string($transaction->metadata) ? json_decode($transaction->metadata, true) : $transaction->metadata;
            
            if (isset($metadata['type']) && $metadata['type'] === 'membership') {
                $user = $transaction->user;
                $durationDays = $metadata['days'] ?? 0;
                
                $hasActiveMembership = $user->membership_expires_at && Carbon::parse($user->membership_expires_at)->isFuture();
                $currentExpiry = $hasActiveMembership ? Carbon::parse($user->membership_expires_at) : now();
                
                $user->membership_expires_at = $currentExpiry->addDays($durationDays);
                $user->save();
            }

            // Tambahkan komisi ke pemilik kode voucher
            // Pastikan kolom affiliate_commission ada di tabel transactions Anda
            if ($transaction->referrer_id && $transaction->affiliate_commission > 0) {
                $referrer = User::find($transaction->referrer_id);
                if ($referrer) {
                    $referrer->increment('affiliate_balance', $transaction->affiliate_commission);
                }
            }
        });

        return back()->with('success', 'Transaksi disetujui.');
    }

    /**
     * Membatalkan / Menolak Transaksi
     */
    public function reject(Transaction $transaction)
    {
        // PERBAIKAN: Ubah jadi 'failed' agar cocok dengan tab filter di Vue
        $transaction->update([
            'status' => 'failed' 
        ]);

        // Jika transaksi ini adalah bundling (punya banyak anak invoice)
        if ($transaction->invoice_code && $transaction->type === 'bundling') {
            Transaction::where('invoice_code', 'LIKE', $transaction->invoice_code . '-%')
                ->update(['status' => 'failed']);
        }

        return back()->with('success', 'Transaksi berhasil ditolak / dibatalkan.');
    }
}