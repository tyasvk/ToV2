<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AffiliateManagerController extends Controller
{
public function leaderboard(Request $request)
{
    // 1. Tentukan Rentang Tanggal (Default: Senin - Minggu pekan ini)
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $month = $request->input('month');
    $year = (int)$request->input('year', now()->year);

    $query = Transaction::query()
        ->whereNotNull('referrer_id')
        ->whereIn('status', ['paid', 'success']);

    // Logika Penentuan Periode
    if ($startDate && $endDate) {
        // Jika Admin pakai Kalender (Manual Range)
        $start = \Carbon\Carbon::parse($startDate)->startOfDay();
        $end = \Carbon\Carbon::parse($endDate)->endOfDay();
    } else if (!$month) {
        // DEFAULT: Jika baru buka halaman, langsung tampilkan MINGGU INI (Senin - Minggu)
        $start = now()->startOfWeek(\Carbon\Carbon::MONDAY);
        $end = now()->endOfWeek(\Carbon\Carbon::SUNDAY);
        // Set info untuk filter di frontend
        $startDate = $start->format('Y-m-d');
        $endDate = $end->format('Y-m-d');
    } else {
        // Jika pakai filter Bulan/Tahun
        $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
    }

    $query->whereBetween('created_at', [$start, $end]);

    // 2. Ambil Data Ranking & Cek Status Hadiah di PERIODE TERSEBUT
    $rankings = $query->select(
            'referrer_id',
            DB::raw('SUM(affiliate_commission) as total_commission'),
            DB::raw('COUNT(*) as total_referrals')
        )
        ->with(['referrer' => function($q) use ($start, $end, $month, $year) {
            $q->select('id', 'name', 'email', 'affiliate_code');
            
            // CEK HADIAH MINGGUAN: Hanya hitung jika ada transaksi di rentang $start sampai $end
            $q->withCount(['walletTransactions as weekly_sent' => function($sq) use ($start, $end) {
                $sq->where('proof_payment', 'REWARD-WEEKLY')
                   ->whereBetween('created_at', [$start, $end]);
            }]);

            // CEK HADIAH BULANAN: Hanya hitung di bulan tersebut
            $q->withCount(['walletTransactions as monthly_sent' => function($sq) use ($month, $year) {
                $sq->where('proof_payment', 'REWARD-MONTHLY');
                if ($month) {
                    $sq->whereMonth('created_at', $month)->whereYear('created_at', $year);
                } else {
                    $sq->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                }
            }]);

            // Cek Bonus 100K
            $q->withCount(['walletTransactions as bonus_sent_count' => function($sq) use ($start, $end) {
                $sq->where('proof_payment', 'SYSTEM-BONUS')
                   ->whereBetween('created_at', [$start, $end]);
            }]);
        }])
        ->groupBy('referrer_id')
        ->orderByRaw('SUM(affiliate_commission) DESC')
        ->paginate(10)
        ->withQueryString();

    return Inertia::render('Admin/Affiliate/Leaderboard', [
        'rankings' => $rankings,
        'filters' => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'month' => $month,
            'year' => $year,
        ],
        'stats' => [
            'total_commission' => (float) $query->sum('affiliate_commission'),
            'total_referrals' => (int) $query->count()
        ]
    ]);
}

public function sendIndividualReward(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'amount' => 'required|numeric',
        'rank' => 'required',
        'reward_type' => 'required|in:weekly,monthly',
        // Tambahkan validasi tanggal filter
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date',
    ]);

    DB::transaction(function () use ($request) {
        $user = User::find($request->user_id);
        $user->increment('affiliate_balance', $request->amount);

        // Logika Penentuan Label Tanggal
        if ($request->reward_type === 'weekly' && $request->start_date && $request->end_date) {
            $tglAwal = \Carbon\Carbon::parse($request->start_date)->format('d M');
            $tglAkhir = \Carbon\Carbon::parse($request->end_date)->format('d M Y');
            $periode = "Periode ($tglAwal - $tglAkhir)";
        } else {
            $periode = "Bulan " . now()->translatedFormat('F Y');
        }

        $label = $request->reward_type === 'weekly' ? 'Mingguan' : 'Bulanan';

        WalletTransaction::create([
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => $request->amount,
            // Deskripsi Otomatis dengan Rentang Tanggal
            'description' => "Hadiah $label Peringkat {$request->rank} Affiliate $periode",
            'status' => 'success',
            'proof_payment' => $request->reward_type === 'weekly' ? 'REWARD-WEEKLY' : 'REWARD-MONTHLY'
        ]);
    });

    return back()->with('success', 'Hadiah berhasil terkirim dengan deskripsi periode yang akurat.');
}
public function transactions(Request $request)
{
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $month = $request->input('month');
    $year = (int)$request->input('year', now()->year);
    $search = $request->input('search');

    $query = Transaction::query()
        ->whereNotNull('referrer_id') // Hanya transaksi affiliate
        ->with(['user:id,name', 'referrer:id,name,affiliate_code']);

    // Logika Filter Periode
    if ($startDate && $endDate) {
        $query->whereBetween('created_at', [
            \Carbon\Carbon::parse($startDate)->startOfDay(),
            \Carbon\Carbon::parse($endDate)->endOfDay()
        ]);
    } elseif ($month) {
        $query->whereMonth('created_at', $month)->whereYear('created_at', $year);
    }

    // Filter Pencarian
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->whereHas('user', function($sq) use ($search) {
                $sq->where('name', 'like', "%{$search}%");
            })->orWhereHas('referrer', function($sq) use ($search) {
                $sq->where('name', 'like', "%{$search}%")
                  ->orWhere('affiliate_code', 'like', "%{$search}%");
            });
        });
    }

    $transactions = $query->latest()->paginate(15)->withQueryString();

    return Inertia::render('Admin/Affiliate/Transactions', [
        'transactions' => $transactions,
        'filters' => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'month' => $month ? (int)$month : null,
            'year' => $year,
            'search' => $search
        ]
    ]);
}

public function withdrawals(Request $request)
{
    $status = $request->input('status');
    
    $query = \App\Models\WithdrawalRequest::query()
        ->with('user:id,name,email,bank_info')
        ->latest();

    // Filter berdasarkan status (pending/approved)
    if ($status) {
        $query->where('status', $status);
    }

    $withdrawals = $query->paginate(15)->withQueryString();

    return Inertia::render('Admin/Affiliate/Withdrawals', [
        'withdrawals' => $withdrawals,
        'filters' => [
            'status' => $status
        ]
    ]);
}

public function approveWithdrawal(Request $request, $id)
{
    $withdrawal = \App\Models\WithdrawalRequest::findOrFail($id);
    
    if ($withdrawal->status !== 'pending') {
        return back()->with('error', 'PENGAJUAN SUDAH DIPROSES SEBELUMNYA.');
    }

    DB::transaction(function () use ($withdrawal) {
        // Update Status
        $withdrawal->update([
            'status' => 'approved',
            'processed_at' => now()
        ]);

        // Opsional: Kirim Notifikasi ke User atau catat log pengeluaran
    });

    return back()->with('success', 'STATUS BERHASIL DIUBAH MENJADI APPROVED.');
}

/**
 * Helper untuk tag pelacakan agar tombol di View bisa berubah jadi centang ✅
 */
private function getProofTag($type)
{
    return match($type) {
        'weekly' => 'REWARD-WEEKLY',
        'monthly' => 'REWARD-MONTHLY',
        'special' => 'SYSTEM-BONUS',
        default => 'OTHER'
    };
}

/**
 * Bonus Spesial Target 100 Referal
 */
public function sendSpecialBonus(Request $request)
{
    DB::transaction(function () use ($request) {
        $user = User::find($request->user_id);
        
        // Masuk ke saldo pencairan (affiliate_balance)
        $user->increment('affiliate_balance', 100000);

        WalletTransaction::create([
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => 100000,
            'description' => "Bonus Spesial Target 100 Referal " . now()->format('F Y'),
            'status' => 'success',
            'proof_payment' => 'SYSTEM-BONUS'
        ]);
    });

    return back()->with('success', 'Bonus 100K telah ditambahkan ke saldo pencairan user.');
}

public function exportPdf(Request $request)
{
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    
    // Pastikan month dan year dikonversi ke Integer (int)
    $month = $request->filled('month') ? (int) $request->input('month') : null;
    $year = (int) $request->input('year', now()->year);

    $query = Transaction::query()
        ->whereNotNull('referrer_id')
        ->whereIn('status', ['paid', 'success']);

    if ($startDate && $endDate) {
        $start = \Carbon\Carbon::parse($startDate)->startOfDay();
        $end = \Carbon\Carbon::parse($endDate)->endOfDay();
        $query->whereBetween('created_at', [$start, $end]);
        $periodLabel = $start->format('d M Y') . ' - ' . $end->format('d M Y');
    } else {
        // Jika month kosong, gunakan bulan saat ini
        $month = $month ?? (int) now()->month;
        
        $query->whereMonth('created_at', $month)->whereYear('created_at', $year);
        
        // Perbaikan di sini: Pastikan $month adalah integer
        $periodLabel = \Carbon\Carbon::create()->month($month)->format('F') . ' ' . $year;
    }

    $data = (clone $query)->select(
            'referrer_id', 
            DB::raw('SUM(affiliate_commission) as total_comm'), 
            DB::raw('COUNT(*) as total_ref')
        )
        ->groupBy('referrer_id')
        ->with('referrer')
        ->orderByDesc('total_comm')
        ->get();

    $pdf = Pdf::loadView('pdf.affiliate-leaderboard', [
        'data' => $data,
        'period' => $periodLabel,
        'total_commission' => (float) $query->sum('affiliate_commission'),
        'total_referrals' => (int) $query->count(),
        'date' => now()->translatedFormat('d F Y H:i')
    ]);

    return $pdf->download("Laporan-Affiliate-" . \Illuminate\Support\Str::slug($periodLabel) . ".pdf");
}
    public function distributeRewards(Request $request)
    {
        // Ambil Top 3 Bulan Ini
        $topThree = Transaction::whereNotNull('referrer_id')
            ->whereIn('status', ['paid', 'success'])
            ->whereMonth('created_at', now()->month)
            ->select('referrer_id', DB::raw('SUM(affiliate_commission) as total'))
            ->groupBy('referrer_id')
            ->orderByDesc('total')
            ->take(3)
            ->get();

        $rewards = [50000, 30000, 20000]; // Hadiah Juara 1, 2, 3

        DB::transaction(function () use ($topThree, $rewards) {
            foreach ($topThree as $index => $rank) {
                $amount = $rewards[$index];
                $user = User::find($rank->referrer_id);
                
                // Tambah Saldo Dompet
                $user->increment('balance', $amount);

                // Catat Transaksi
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'credit',
                    'amount' => $amount,
                    'description' => "Hadiah Juara " . ($index + 1) . " Affiliate Bulan " . now()->format('F'),
                    'status' => 'success',
                    'proof_payment' => 'SYSTEM-REWARD'
                ]);
            }
        });

        return back()->with('success', 'Reward berhasil dikirim ke saldo dompet Top 3!');
    }
}