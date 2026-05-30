<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminAffiliateController extends Controller
{
    public function index()
    {
        // 1. Ambil data semua user yang bergabung dalam program afiliasi
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

        // 2. Ambil semua riwayat pengajuan penarikan dana
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

        // 3. Ringkasan total statistik
        $summary = [
            'total_partners' => $affiliateUsers->count(),
            'total_allocated_balance' => User::sum('affiliate_balance'),
            'total_payouts' => WithdrawalRequest::where('status', 'approved')->sum('amount'),
            'pending_requests' => WithdrawalRequest::where('status', 'pending')->count(),
        ];

        return Inertia::render('Admin/Affiliate/Index', [
            'users' => $affiliateUsers,
            'withdrawals' => $withdrawalRequests,
            'summary' => $summary,
        ]);
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

    // FUNGSI UTAMA UNTUK MEMBERIKAN HADIAH TARGET (PASTIKAN BERADA DI SINI)
 public function giveReward(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'type' => 'required|string|in:REWARD-WEEKLY,REWARD-MONTHLY'
        ]);

        DB::transaction(function () use ($user, $request) {
            // 1. Tambahkan nominal hadiah ke saldo afiliasi pengguna
            $user->update([
                'affiliate_balance' => ($user->affiliate_balance ?? 0) + $request->amount
            ]);

            // 2. Catat ke WalletTransaction dengan menyertakan field 'type'
            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'type' => 'credit', // PERBAIKAN: Menambahkan field type yang wajib diisi database Anda
                'status' => 'success',
                'proof_payment' => $request->type, 
                'description' => 'Bonus Pencapaian Target Afiliasi'
            ]);
        });

        return back()->with('success', "Bonus sebesar Rp " . number_format($request->amount, 0, ',', '.') . " berhasil diberikan kepada {$user->name}!");
    }
}