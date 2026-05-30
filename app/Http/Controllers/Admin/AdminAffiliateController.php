<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use App\Models\WalletTransaction;
use App\Models\Setting; // TAMBAHKAN IMPORT MODEL SETTING
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class AdminAffiliateController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data semua user yang bergabung dalam program afiliasi
        $affiliateUsers = User::whereNotNull('affiliate_code')
            ->get()
            ->map(function ($user) {
                $totalReferrals = Transaction::where('referrer_id', $user->id)
                    ->whereIn('status', ['paid', 'success'])
                    ->count();

                $totalWithdrawn = WithdrawalRequest::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->sum('amount');

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'affiliate_code' => $user->affiliate_code,
                    'current_balance' => $user->affiliate_balance ?? 0,
                    'total_referrals' => $totalReferrals,
                    'total_withdrawn' => $totalWithdrawn,
                    'bank_info' => $user->bank_info,
                ];
            });

        // Ambil semua riwayat pengajuan penarikan dana
        $withdrawalRequests = WithdrawalRequest::with('user:id,name,email')
            ->latest()
            ->get()
            ->map(function ($wd) {
                $bankDetails = is_array($wd->bank_details) ? $wd->bank_details : [];
                return [
                    'id' => $wd->id,
                    'user_name' => $wd->user->name ?? 'Pengguna',
                    'user_email' => $wd->user->email ?? '-',
                    'amount' => $wd->amount,
                    'status' => $wd->status,
                    'created_at' => $wd->created_at,
                    'bank_name' => $bankDetails['bank_name'] ?? '-',
                    'account_number' => $bankDetails['account_number'] ?? '-',
                    'account_name' => $bankDetails['account_name'] ?? '-',
                ];
            });

        // Ringkasan total statistik
        $summary = [
            'total_partners' => $affiliateUsers->count(),
            'total_allocated_balance' => User::sum('affiliate_balance'),
            'total_payouts' => WithdrawalRequest::where('status', 'approved')->sum('amount'),
            'pending_requests' => WithdrawalRequest::where('status', 'pending')->count(),
        ];

        // LOGIKA PERINGKAT (LEADERBOARD) MINGGUAN & BULANAN
        $now = Carbon::now();
        $indoMonths = [1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'];
        
        $firstTx = Transaction::whereNotNull('referrer_id')->orderBy('created_at', 'asc')->first();
        $startDate = $firstTx ? Carbon::parse($firstTx->created_at) : $now->copy();
        
        // Dropdown Arsip Bulanan
        $archiveMonths = [];
        $currentMonthLoop = $startDate->copy()->startOfMonth();
        $nowMonth = $now->copy()->startOfMonth();
        while ($currentMonthLoop <= $nowMonth) {
            $archiveMonths[] = [
                'value' => $currentMonthLoop->format('Y-m'),
                'label' => $indoMonths[(int)$currentMonthLoop->format('m')] . ' ' . $currentMonthLoop->format('Y')
            ];
            $currentMonthLoop->addMonth();
        }
        $archiveMonths = array_reverse($archiveMonths);

        // Dropdown Arsip Mingguan
        $archiveWeeks = [];
        $currentWeekLoop = $startDate->copy()->startOfWeek();
        $nowWeek = $now->copy()->startOfWeek();
        while ($currentWeekLoop <= $nowWeek) {
            $weekNum = $currentWeekLoop->weekOfMonth;
            $monthLabel = $indoMonths[(int)$currentWeekLoop->format('m')] . ' ' . $currentWeekLoop->format('Y');
            
            $archiveWeeks[] = [
                'value' => $currentWeekLoop->format('Y-m-d'),
                'label' => "Minggu ke-$weekNum $monthLabel (" . $currentWeekLoop->format('d M') . " - " . $currentWeekLoop->copy()->endOfWeek()->format('d M') . ")"
            ];
            $currentWeekLoop->addWeek();
        }
        $archiveWeeks = array_reverse($archiveWeeks);

        $selectedMonth = $request->input('month', count($archiveMonths) > 0 ? $archiveMonths[0]['value'] : $now->format('Y-m'));
        $selectedWeek = $request->input('week', count($archiveWeeks) > 0 ? $archiveWeeks[0]['value'] : $now->startOfWeek()->format('Y-m-d'));

        // Query Peringkat Bulanan
        $monthParts = explode('-', $selectedMonth);
        $monthlyTx = Transaction::whereIn('status', ['paid', 'success'])
            ->whereNotNull('referrer_id')
            ->whereYear('created_at', $monthParts[0])
            ->whereMonth('created_at', $monthParts[1])
            ->select('referrer_id', DB::raw('count(*) as total'))
            ->groupBy('referrer_id')
            ->orderByDesc('total')
            ->take(50)
            ->get();
        
        $monthlyReferrers = User::whereIn('id', $monthlyTx->pluck('referrer_id'))->get()->keyBy('id');
        $monthlyLeaderboard = $monthlyTx->map(function($tx) use ($monthlyReferrers) {
            $user = $monthlyReferrers[$tx->referrer_id] ?? null;
            return [
                'id' => $tx->referrer_id,
                'name' => $user ? $user->name : 'Mitra Tidak Diketahui',
                'email' => $user ? $user->email : '-',
                'total_referrals' => $tx->total
            ];
        });

        // Query Peringkat Mingguan
        $weekStartObj = Carbon::parse($selectedWeek);
        $weekEndObj = $weekStartObj->copy()->endOfWeek();
        
        $weeklyTx = Transaction::whereIn('status', ['paid', 'success'])
            ->whereNotNull('referrer_id')
            ->whereBetween('created_at', [$weekStartObj->format('Y-m-d 00:00:00'), $weekEndObj->format('Y-m-d 23:59:59')])
            ->select('referrer_id', DB::raw('count(*) as total'))
            ->groupBy('referrer_id')
            ->orderByDesc('total')
            ->take(50)
            ->get();

        $weeklyReferrers = User::whereIn('id', $weeklyTx->pluck('referrer_id'))->get()->keyBy('id');
        $weeklyLeaderboard = $weeklyTx->map(function($tx) use ($weeklyReferrers) {
            $user = $weeklyReferrers[$tx->referrer_id] ?? null;
            return [
                'id' => $tx->referrer_id,
                'name' => $user ? $user->name : 'Mitra Tidak Diketahui',
                'email' => $user ? $user->email : '-',
                'total_referrals' => $tx->total
            ];
        });

        // AMBIL TEKS PENGATURAN KOMPETISI DARI DATABASE (JIKA BELUM ADA, KASIH VALUE DEFAULT)
        $competitionTitleSetting = Setting::where('key', 'competition_title')->first();
        $competitionDescSetting = Setting::where('key', 'competition_description')->first();

        $competitionSettings = [
            'title' => $competitionTitleSetting ? $competitionTitleSetting->value : 'Kompetisi Penggunaan Token',
            'description' => $competitionDescSetting ? $competitionDescSetting->value : 'Raih target penggunaan token terbanyak setiap minggu dan bulan untuk mendapatkan hadiah saldo khusus secara langsung! Bagikan tautan kompetisi atau token unik Anda di bawah ini ke seluruh media sosial.'
        ];

        return Inertia::render('Admin/Affiliate/Index', [
            'users' => $affiliateUsers,
            'withdrawals' => $withdrawalRequests,
            'summary' => $summary,
            'archiveMonths' => $archiveMonths,
            'archiveWeeks' => $archiveWeeks,
            'monthlyLeaderboard' => $monthlyLeaderboard,
            'weeklyLeaderboard' => $weeklyLeaderboard,
            'competitionSettings' => $competitionSettings, // lempar data ke Vue Admin
            'selectedFilters' => [
                'month' => $selectedMonth,
                'week' => $selectedWeek
            ]
        ]);
    }

    // METHOD BARU UNTUK UPDATE TEKS KOMPETISI OLEH ADMIN
    public function updateCompetitionSetting(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        Setting::updateOrCreate(
            ['key' => 'competition_title'],
            ['value' => $request->title, 'type' => 'string']
        );

        Setting::updateOrCreate(
            ['key' => 'competition_description'],
            ['value' => $request->description, 'type' => 'text']
        );

        return back()->with('success', 'Konten teks kompetisi reward berhasil diperbarui!');
    }

    public function updateWithdrawStatus(Request $request, WithdrawalRequest $withdrawal)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        DB::transaction(function () use ($request, $withdrawal) {
            if ($request->status === 'rejected' && $withdrawal->status === 'pending') {
                $user = $withdrawal->user;
                if ($user) {
                    $user->update([
                        'affiliate_balance' => $user->affiliate_balance + $withdrawal->amount
                    ]);
                }
            }

            $withdrawal->update([
                'status' => $request->status
            ]);
        });

        return back()->with('success', 'Status permintaan penarikan dana berhasil diperbarui.');
    }

    public function giveReward(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'type' => 'required|string|in:REWARD-WEEKLY,REWARD-MONTHLY'
        ]);

        DB::transaction(function () use ($user, $request) {
            $user->update([
                'affiliate_balance' => ($user->affiliate_balance ?? 0) + $request->amount
            ]);

            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'type' => 'credit', 
                'status' => 'success',
                'proof_payment' => $request->type, 
                'description' => 'Bonus Pencapaian Target Afiliasi'
            ]);
        });

        return back()->with('success', "Bonus sebesar Rp " . number_format($request->amount, 0, ',', '.') . " berhasil diberikan kepada {$user->name}!");
    }
}