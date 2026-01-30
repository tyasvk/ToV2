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
            ->with(['user', 'tryout']); // Load data User dan Tryout agar tidak error di frontend

        // Fitur Pencarian (Opsional, tapi bagus untuk Admin)
        if ($request->search) {
            $query->where('invoice_code', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
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
        $transaction->update(['status' => 'paid']); // Atau 'success' tergantung enum Anda
        return back()->with('success', 'Transaksi berhasil disetujui.');
    }
}