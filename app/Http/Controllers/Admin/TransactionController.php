<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index(Request $request)
{
    // 1. Mulai query dengan relasi yang dibutuhkan
    $query = Transaction::query()
        ->with(['user', 'tryout']); // Pastikan relasi 'user' dan 'tryout' ada di model

    // 2. Filter berdasarkan Pencarian (Search)
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('invoice_code', 'like', '%' . $request->search . '%')
              ->orWhereHas('user', function($userQuery) use ($request) {
                  $userQuery->where('name', 'like', '%' . $request->search . '%');
              });
        });
    }

    // 3. Filter berdasarkan Status (Inilah yang membuat tombol Anda berfungsi)
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
public function approve(Transaction $transaction)
{
    if ($transaction->status === 'paid') return back();

    DB::transaction(function () use ($transaction) {
        $transaction->update(['status' => 'paid']);

        // Tambahkan komisi ke pemilik kode voucher
        if ($transaction->referrer_id && $transaction->affiliate_commission > 0) {
            User::find($transaction->referrer_id)->increment('affiliate_balance', $transaction->affiliate_commission);
        }
    });

    return back()->with('success', 'Transaksi disetujui dan komisi afiliasi telah dikirim.');
}
}