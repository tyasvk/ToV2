<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AffiliateController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Konfigurasi Nominal Aturan Sistem Afiliasi Terbaru
        $systemInfo = [
            'min_withdrawal' => 30000,
            'commission_per_referral' => 2500,   // Komisi pendaftar masuk ke saldo afiliasi
            'wallet_bonus_for_referral' => 2500, // Bonus pendaftar masuk ke dompet pendaftar
            'token_discount' => 2000,            // Diskon checkout pakai token
            'token_commission' => 2000,          // Komisi pemilik token
            'target_limit' => 100,
            'special_bonus' => 100000,
        ];

        // 1. JIKA USER BELUM DAFTAR PROGRAM AFILIASI
        if (!$user->affiliate_code) {
            return Inertia::render('User/Affiliate/Index', array_merge([
                'user' => $user, 
                'affiliate_code' => null,
                'affiliate_url' => '',
                'stats' => [
                    'clicks' => 0, 
                    'token_usages' => 0, 
                    'conversions' => 0, 
                    'total_earnings' => 0, 
                    'current_balance' => 0
                ],
                'earning_history' => [],
                'withdrawals' => [],
                'announcements' => [],
                'monthly_count' => 0,
                'weekly_leaderboard' => [],
                'monthly_leaderboard' => [],
            ], $systemInfo));
        }

        // 2. JIKA USER SUDAH DAFTAR PROGRAM AFILIASI
        $affiliateUrl = url('/register?ref=' . $user->affiliate_code);

        $announcements = WalletTransaction::whereIn('proof_payment', ['REWARD-WEEKLY', 'REWARD-MONTHLY'])
            ->with('user:id,name')
            ->latest()
            ->take(10)
            ->get();

        // Hitung total penggunaan token di bulan ini
        $monthlyReferralCount = Transaction::where('referrer_id', $user->id)
            ->whereIn('status', ['paid', 'success'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // ===============================================
        // MENGAMBIL RIWAYAT PENDAPATAN (GABUNGAN)
        // ===============================================
        // A. Dari Pendaftaran Baru
        $registeredUsers = User::where('referred_by', $user->id)->get()->map(function($u) use ($systemInfo) {
            return [
                'id' => 'reg_'.$u->id,
                'name' => $u->name,
                'type' => 'Pendaftaran Akun Baru',
                'amount' => $systemInfo['commission_per_referral'],
                'created_at' => $u->created_at,
            ];
        });

        // B. Dari Penggunaan Token Tryout
        $tokenUsages = Transaction::where('referrer_id', $user->id)
            ->whereIn('status', ['paid', 'success'])
            ->with('user')
            ->get()
            ->map(function($t) use ($systemInfo) {
                return [
                    'id' => 'trx_'.$t->id,
                    'name' => $t->user->name ?? 'Pengguna',
                    'type' => 'Pembelian Tryout (Token)',
                    'amount' => $systemInfo['token_commission'],
                    'created_at' => $t->created_at,
                ];
            });

        // Gabungkan dan urutkan dari yang terbaru
        $earningHistory = $registeredUsers->merge($tokenUsages)->sortByDesc('created_at')->values()->all();
        // ===============================================

        $stats = [
            'clicks' => 0,
            'token_usages' => $tokenUsages->count(), 
            'conversions' => $tokenUsages->count(),
            'total_earnings' => WithdrawalRequest::where('user_id', $user->id)->where('status', 'approved')->sum('amount') + ($user->affiliate_balance ?? 0),
            'current_balance' => $user->affiliate_balance ?? 0,
        ];

        $withdrawals = WithdrawalRequest::where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($wd) {
                $bankName = is_array($wd->bank_details) ? ($wd->bank_details['bank_name'] ?? 'Transfer Bank') : 'Transfer Bank';
                return [
                    'id' => $wd->id,
                    'created_at' => $wd->created_at,
                    'status' => $wd->status,
                    'bank_name' => $bankName,
                    'amount' => $wd->amount,
                ];
            });

        // KLASEMEN KOMPETISI MINGGUAN & BULANAN
        $weeklyLeaderboard = DB::table('transactions')
            ->join('users', 'transactions.referrer_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(transactions.id) as total'))
            ->whereIn('transactions.status', ['paid', 'success'])
            ->whereBetween('transactions.created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $monthlyLeaderboard = DB::table('transactions')
            ->join('users', 'transactions.referrer_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(transactions.id) as total'))
            ->whereIn('transactions.status', ['paid', 'success'])
            ->whereMonth('transactions.created_at', now()->month)
            ->whereYear('transactions.created_at', now()->year)
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return Inertia::render('User/Affiliate/Index', array_merge([
            'user' => $user,
            'affiliate_code' => $user->affiliate_code,
            'affiliate_url' => $affiliateUrl,
            'stats' => $stats,
            'earning_history' => $earningHistory, // Data gabungan baru dikirim ke Vue
            'withdrawals' => $withdrawals,
            'announcements' => $announcements,
            'monthly_count' => $monthlyReferralCount,
            'weekly_leaderboard' => $weeklyLeaderboard,
            'monthly_leaderboard' => $monthlyLeaderboard,
        ], $systemInfo));
    }

    public function register()
    {
        $user = auth()->user();
        if (!$user->affiliate_code) {
            $user->update([
                'affiliate_code' => strtoupper(Str::random(6)) . $user->id
            ]);
        }
        return back()->with('success', 'Selamat! Program kemitraan afiliasi Anda kini telah aktif.');
    }

    public function updateBankInfo(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
        ]);

        $user = auth()->user();
        
        $user->update([
            'bank_info' => [
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
            ]
        ]);

        return back()->with('success', 'Informasi rekening bank berhasil disimpan.');
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:30000',
        ], [
            'amount.min' => 'Minimal batas penarikan saldo adalah Rp 30.000',
            'amount.required' => 'Jumlah penarikan wajib diisi.',
        ]);

        $user = auth()->user();

        if (!$user->bank_info) {
            return back()->withErrors(['amount' => 'Harap simpan data rekening bank di atas terlebih dahulu.']);
        }

        if (($user->affiliate_balance ?? 0) < $request->amount) {
            return back()->withErrors(['amount' => 'Saldo komisi Anda saat ini tidak mencukupi.']);
        }

        DB::transaction(function () use ($user, $request) {
            $amount = $request->amount;

            WithdrawalRequest::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'bank_details' => $user->bank_info, 
                'status' => 'pending'
            ]);

            $user->update([
                'affiliate_balance' => $user->affiliate_balance - $amount
            ]);
        });

        return back()->with('success', 'Pengajuan penarikan saldo berhasil! Silakan cek riwayat Anda.');
    }
}