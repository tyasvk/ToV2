<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Tryout;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. Statistik Utama Cards
        $stats = [
            'total_users' => User::role('user')->count(),
            'total_revenue' => Transaction::whereIn('status', ['paid', 'success'])->sum('amount'),
            'active_tryouts' => Tryout::where('is_published', true)->count(),
            'pending_transactions' => Transaction::where('status', 'pending')->count(),
        ];

        // 2. Transaksi Terbaru (5 Data)
        $recentTransactions = Transaction::with(['user', 'tryout'])
            ->latest()
            ->take(5)
            ->get();

        // 3. User Terbaru (5 Data)
        $newUsers = User::role('user')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'newUsers' => $newUsers,
        ]);
    }
}