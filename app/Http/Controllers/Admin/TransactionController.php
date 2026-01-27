<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index()
    {
        // Mengambil data dari tabel purchases yang kita buat sebelumnya
        $transactions = DB::table('purchases')
            ->join('users', 'purchases.user_id', '=', 'users.id')
            ->join('tryouts', 'purchases.tryout_id', '=', 'tryouts.id')
            ->select('purchases.*', 'users.name as user_name', 'tryouts.title as tryout_title')
            ->orderBy('purchases.created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => $transactions
        ]);
    }
}