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
        // Query Dasar dengan Relasi
        $query = Transaction::query()
            ->with(['user', 'tryout']) // Load data User dan Tryout
            ->where('amount', '>', 0); // <--- FILTER PENTING: Hanya ambil yang berbayar (Harga > 0)

        // Fitur Pencarian
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('invoice_code', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($userQ) use ($request) {
                      $userQ->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Ambil data terbaru, 10 per halaman
        $transactions = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['search']),
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