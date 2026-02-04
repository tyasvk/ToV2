<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\WalletTransaction; // TAMBAHKAN INI

class AffiliateController extends Controller
{
public function index()
{
    $user = auth()->user();
    
    // Ambil data pengumuman pemenang terbaru (Top 10)
    $announcements = WalletTransaction::whereIn('proof_payment', ['REWARD-WEEKLY', 'REWARD-MONTHLY'])
        ->with('user:id,name')
        ->latest()
        ->take(10)
        ->get();

    $monthlyReferralCount = Transaction::where('referrer_id', $user->id)
        ->whereIn('status', ['paid', 'success'])
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();

    $referrals = Transaction::where('referrer_id', $user->id)
        ->whereIn('status', ['paid', 'success'])
        ->with('user')
        ->latest()
        ->paginate(5)
        ->withQueryString();

    $withdrawals = WithdrawalRequest::where('user_id', $user->id)
        ->latest()
        ->get();

    return Inertia::render('User/Affiliate/Index', [
        'user' => $user,
        'referrals' => $referrals,
        'withdrawals' => $withdrawals,
        'announcements' => $announcements, // Data pengumuman
        'monthly_count' => $monthlyReferralCount,
        'target_limit' => 100,
        'special_bonus' => 100000,
        'min_withdrawal' => 30000
    ]);
}

    public function join()
    {
        $user = auth()->user();
        
        if (!$user->affiliate_code) {
            $user->update([
                'affiliate_code' => strtoupper(Str::random(6)) . $user->id
            ]);
        }

        return back()->with('success', 'Selamat! Program afiliasi Anda telah aktif.');
    }

public function updateBankInfo(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
        ]);

        auth()->user()->update([
            'bank_info' => $request->only(['bank_name', 'account_number', 'account_name'])
        ]);

        return back()->with('success', 'Data bank berhasil disimpan.');
    }

public function withdraw()
{
    $user = auth()->user();
    
    if (!$user->bank_info) {
        return back()->with('error', 'Lengkapi data bank.');
    }

    if ($user->affiliate_balance < 3000) {
        return back()->with('error', 'Minimal Rp 30.000.');
    }

    \Illuminate\Support\Facades\DB::transaction(function () use ($user) {
        $amount = $user->affiliate_balance;
        
        // Buat record baru
        \App\Models\WithdrawalRequest::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'bank_details' => $user->bank_info,
            'status' => 'pending'
        ]);

        // Reset saldo user
        $user->update(['affiliate_balance' => 0]);
    });

    return back()->with('success', 'Berhasil diajukan.');
}
}