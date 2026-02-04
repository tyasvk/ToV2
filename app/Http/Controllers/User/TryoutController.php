<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use App\Models\Question;
use App\Models\ExamAttempt;
use App\Models\Transaction;
use App\Models\WalletTransaction;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class TryoutController extends Controller
{
    /**
     * Katalog Tryout Umum
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isPremiumMember = $user->membership_expires_at && now()->lt($user->membership_expires_at);

        $tryouts = Tryout::query()
            ->where('is_published', true)
            // Filter agar tipe 'akbar' dan 'adidaya' tidak muncul di list umum
            ->where(function ($query) {
                $query->whereNotIn('type', ['akbar', 'adidaya'])
                      ->orWhereNull('type');
            })
            // Jika Member Premium, pindahkan semua ke 'Tryout Saya' (katalog umum menjadi kosong)
            ->when($isPremiumMember, function ($query) {
                $query->whereRaw('1 = 0');
            })
            // Jika bukan member, sembunyikan yang sudah dibeli manual
            ->when(!$isPremiumMember, function ($query) use ($user) {
                $query->whereDoesntHave('transactions', function($q) use ($user) {
                    $q->whereIn('status', ['paid', 'success'])
                      ->where(function($subQuery) use ($user) {
                          $subQuery->where('user_id', $user->id)
                                   ->orWhereJsonContains('participants_data', $user->email);
                      });
                });
            })
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return Inertia::render('User/Tryout/Index', [
            'tryouts' => $tryouts,
            'filters' => $request->only(['search']),
        ]);
    }

    // Cari method processRegistration dan ganti isinya dengan ini
public function processRegistration(Request $request, Tryout $tryout)
{
    $request->validate([
        'payment_method' => 'required|in:wallet,midtrans',
        'emails' => 'array|max:5',
        'emails.*' => 'required|email|exists:users,email|distinct',
        'voucher_code' => 'nullable|string|exists:users,affiliate_code', // Tambahkan validasi
    ]);

    $participants = collect([auth()->user()->email])->merge($request->emails ?? [])->unique()->values();
    $qty = $participants->count();
    $discount = match($qty) { 2 => 0.05, 3 => 0.10, 4 => 0.15, default => $qty >= 5 ? 0.20 : 0 };

    // --- LOGIKA AFILIASI ---
    $referrerId = null;
    $discountVoucher = 0;
    $commission = 0;

    if ($request->voucher_code) {
        $referrer = User::where('affiliate_code', $request->voucher_code)->first();
        // Cek agar tidak pakai kode sendiri
        if ($referrer && $referrer->id !== auth()->id()) {
            $referrerId = $referrer->id;
            $discountVoucher = 3000;
            $commission = 3000;
        }
    }

    $finalAmount = ($tryout->price * $qty) * (1 - $discount) - $discountVoucher;
    $finalAmount = max(0, $finalAmount); // Pastikan tidak negatif
    $invoice = 'INV-' . strtoupper(Str::random(10));

    if ($request->payment_method === 'wallet') {
        $user = auth()->user();
        if ($user->balance < $finalAmount) return back()->withErrors(['payment' => 'Saldo dompet tidak mencukupi.']);

        DB::transaction(function () use ($user, $tryout, $finalAmount, $participants, $qty, $invoice, $referrerId, $commission, $discountVoucher) {
            $user->decrement('balance', $finalAmount);
            
            WalletTransaction::create(['user_id' => $user->id, 'type' => 'debit', 'amount' => $finalAmount, 'description' => 'Bayar Tryout: ' . $tryout->title, 'status' => 'success', 'proof_payment' => 'WALLET-SYSTEM']);
            
            $transaction = Transaction::create([
                'user_id' => $user->id, 
                'tryout_id' => $tryout->id, 
                'referrer_id' => $referrerId, // Simpan Referrer
                'invoice_code' => $invoice, 
                'unit_price' => $tryout->price, 
                'qty' => $qty, 
                'amount' => $finalAmount, 
                'discount_amount' => $discountVoucher, // Simpan Diskon
                'affiliate_commission' => $commission, // Simpan Komisi
                'participants_data' => $participants->all(), 
                'status' => 'paid'
            ]);

            // INPUT KOMISI KE REFERRER (Jika bayar pakai wallet)
            if ($referrerId) {
                User::find($referrerId)->increment('affiliate_balance', $commission);
            }
        });

        return redirect()->route('tryout.index')->with('success', 'Pembelian berhasil!');
    }

    // Untuk Midtrans: Simpan data referal, komisi akan cair saat status jadi 'paid' di callback
    $transaction = Transaction::create([
        'user_id' => auth()->id(), 
        'tryout_id' => $tryout->id, 
        'referrer_id' => $referrerId,
        'invoice_code' => $invoice, 
        'unit_price' => $tryout->price, 
        'qty' => $qty, 
        'amount' => $finalAmount, 
        'discount_amount' => $discountVoucher,
        'affiliate_commission' => $commission,
        'participants_data' => $participants->all(), 
        'status' => 'pending'
    ]);
    
    return redirect()->route('checkout.show', $transaction->id);
}

    /**
     * Tampilkan halaman Nusantara Adidaya (Premium Membership)
     */
    public function adidaya()
    {
        $exclusiveTryouts = Tryout::where('type', 'adidaya')
            ->where('is_published', true)
            ->withCount('questions')
            ->latest()
            ->get();

        return Inertia::render('User/Tryout/AdidayaIndex', [
            'exclusiveTryouts' => $exclusiveTryouts,
        ]);
    }

    /**
     * Halaman Tryout Saya
     */
    public function myTryouts(Request $request)
    {
        $user = auth()->user();
        $userEmail = $user->email;
        $userId = $user->id;
        $isPremiumMember = $user->membership_expires_at && now()->lt($user->membership_expires_at);

        $tryouts = Tryout::query()
            ->where('is_published', true)
            // Hanya tampilkan paket umum (Bukan akbar/adidaya)
            ->where(function ($query) {
                $query->whereNotIn('type', ['akbar', 'adidaya'])
                      ->orWhereNull('type');
            })
            ->where(function($query) use ($userId, $userEmail, $isPremiumMember) {
                // Akses via Transaksi Manual
                $query->whereHas('transactions', function($q) use ($userId, $userEmail) {
                    $q->whereIn('status', ['paid', 'success'])
                      ->where(function($sq) use ($userId, $userEmail) {
                          $sq->where('user_id', $userId)
                            ->orWhereJsonContains('participants_data', $userEmail);
                      });
                });

                // Member Premium otomatis memiliki semua paket umum tanpa beli lagi
                if ($isPremiumMember) {
                    $query->orWhere(function($q) {
                        $q->whereNotIn('type', ['akbar', 'adidaya'])
                          ->orWhereNull('type');
                    });
                }
            })
            ->withCount(['examAttempts' => function($q) use ($userId) {
                $q->where('user_id', $userId);
            }])
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return Inertia::render('User/Tryout/MyTryouts', [
            'tryouts' => $tryouts,
            'filters' => $request->only(['search']),
        ]);
    }

    public function result(ExamAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) abort(403);
        
        $attempt->load('tryout');
        $tryout = $attempt->tryout;

        $rank = ExamAttempt::where('tryout_id', $tryout->id)
            ->where('total_score', '>', $attempt->total_score)
            ->count() + 1;

        $totalParticipants = ExamAttempt::where('tryout_id', $tryout->id)
            ->distinct('user_id')
            ->count();

        $pgTwk = ExamAttempt::PASSING_GRADE_TWK;
        $pgTiu = ExamAttempt::PASSING_GRADE_TIU;
        $pgTkp = ExamAttempt::PASSING_GRADE_TKP;

        $scoreDetails = [
            ['category' => 'Tes Wawasan Kebangsaan (TWK)', 'score' => $attempt->twk_score, 'passing_grade' => $pgTwk, 'is_passed' => $attempt->twk_score >= $pgTwk],
            ['category' => 'Tes Intelegensia Umum (TIU)', 'score' => $attempt->tiu_score, 'passing_grade' => $pgTiu, 'is_passed' => $attempt->tiu_score >= $pgTiu],
            ['category' => 'Tes Karakteristik Pribadi (TKP)', 'score' => $attempt->tkp_score, 'passing_grade' => $pgTkp, 'is_passed' => $attempt->tkp_score >= $pgTkp]
        ];

        $attempt->status = $attempt->is_passed ? 'lulus' : 'tidak_lulus';

        return Inertia::render('User/Tryout/Result', [
            'attempt' => $attempt,
            'tryout' => $tryout,
            'totalScore' => $attempt->total_score,
            'scoreDetails' => $scoreDetails,
            'ranking' => ['rank' => $rank, 'total_participants' => $totalParticipants]
        ]);
    }

    public function historyDetail(Tryout $tryout)
    {
        $user = auth()->user();
        $isPremiumMember = $user->membership_expires_at && now()->lt($user->membership_expires_at);

        // Akses diberikan jika Member (Adidaya/Umum) ATAU punya transaksi manual
        $hasAccess = ($isPremiumMember && $tryout->type !== 'akbar') || Transaction::where('tryout_id', $tryout->id)
            ->whereIn('status', ['paid', 'success'])
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereJsonContains('participants_data', $user->email);
            })
            ->exists();

        if (!$hasAccess) abort(403, 'Akses ditolak.');

        if ($tryout->type === 'akbar') {
            $attempt = ExamAttempt::where('user_id', $user->id)->where('tryout_id', $tryout->id)->latest()->first();
            if ($attempt) return redirect()->route('tryout.result', $attempt->id);
        }

        $attempts = ExamAttempt::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('User/Tryout/HistoryDetail', [
            'tryout' => $tryout,
            'attempts' => $attempts
        ]);
    }

    private function validateAccess(Tryout $tryout)
    {
        $user = auth()->user();
        $now = now();
        $isPremiumMember = $user->membership_expires_at && now()->lt($user->membership_expires_at);
        
        // Akses via Membership (Adidaya & Umum) atau Transaksi Manual
        $hasAccess = ($isPremiumMember && $tryout->type !== 'akbar') || Transaction::where('tryout_id', $tryout->id)
            ->whereIn('status', ['paid', 'success'])
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereJsonContains('participants_data', $user->email);
            })
            ->exists();

        if (!$hasAccess) {
            return [
                'allowed' => false,
                'route' => 'tryout.register',
                'params' => $tryout->id,
                'message' => 'Anda belum terdaftar atau membership Anda sudah tidak aktif.'
            ];
        }

        if ($tryout->type === 'akbar') {
            if ($now->lt($tryout->started_at)) {
                return ['allowed' => false, 'route' => 'tryout-akbar.index', 'message' => 'Event belum dimulai!'];
            }
        }

        // Batas Pengerjaan: Akbar 1x, Adidaya & Umum 3x
        $maxAttempts = ($tryout->type === 'akbar') ? 1 : 3;
        $attemptsCount = ExamAttempt::where('user_id', $user->id)->where('tryout_id', $tryout->id)->count();

        if ($attemptsCount >= $maxAttempts) {
            return [
                'allowed' => false,
                'route' => 'tryout.history.detail',
                'params' => $tryout->id,
                'message' => "Anda sudah mencapai batas maksimal ($maxAttempts x) pengerjaan."
            ];
        }

        return ['allowed' => true];
    }

    public function registerForm(Tryout $tryout)
    {
        return Inertia::render('User/Tryout/Register', ['tryout' => $tryout]);
    }

    public function wait(Tryout $tryout)
    {
        $check = $this->validateAccess($tryout);
        if (!$check['allowed']) {
            return redirect()->route($check['route'], $check['params'] ?? $tryout->id)->with('error', $check['message']);
        }
        return Inertia::render('User/Tryout/Wait', ['tryout' => $tryout->loadCount('questions')]);
    }

    public function examBkn(Tryout $tryout)
    {
        $check = $this->validateAccess($tryout);
        if (!$check['allowed']) return redirect()->route($check['route'], $tryout->id)->with('error', $check['message']);
        return Inertia::render('User/Tryout/ExamSheetBKN', ['tryout' => $tryout, 'questions' => $tryout->questions()->orderBy('order', 'asc')->get(), 'user' => auth()->user()]);
    }

    public function exam(Tryout $tryout)
    {
        $check = $this->validateAccess($tryout);
        if (!$check['allowed']) return redirect()->route($check['route'], $tryout->id)->with('error', $check['message']);
        return Inertia::render('User/Tryout/ExamSheet', ['tryout' => $tryout, 'questions' => $tryout->questions()->orderBy('order', 'asc')->get(), 'user' => auth()->user()]);
    }

    public function finish(Request $request, Tryout $tryout)
    {
        $answers = $request->answers ?? [];
        $questions = $tryout->questions;
        $twk = 0; $tiu = 0; $tkp = 0;

        foreach ($questions as $q) {
            $ans = $answers[$q->id] ?? null;
            if ($q->type === 'TKP') {
                $tkpScoreMap = is_string($q->tkp_scores) ? json_decode($q->tkp_scores, true) : $q->tkp_scores;
                $tkp += (int) ($tkpScoreMap[$ans] ?? 0);
            } else {
                if ($ans === $q->correct_answer) {
                    if ($q->type === 'TWK') $twk += 5;
                    if ($q->type === 'TIU') $tiu += 5;
                }
            }
        }

        $attempt = ExamAttempt::create(['user_id' => auth()->id(), 'tryout_id' => $tryout->id, 'answers' => $answers, 'twk_score' => $twk, 'tiu_score' => $tiu, 'tkp_score' => $tkp, 'total_score' => $twk + $tiu + $tkp, 'completed_at' => now()]);
        return redirect()->route('tryout.result', $attempt->id);
    }

    public function show(Tryout $tryout)
    {
        return Inertia::render('User/Tryout/Show', ['tryout' => $tryout->loadCount('questions')]);
    }

    public function history()
    {
        return Inertia::render('User/Tryout/History', [
            'attempts' => ExamAttempt::where('user_id', auth()->id())->with('tryout')->latest()->get()
        ]);
    }

    public function review(ExamAttempt $attempt) 
    {
        if ($attempt->user_id !== auth()->id()) abort(403);
        $attempt->load('tryout');
        $userAnswers = $attempt->answers ?? [];
        $questions = $attempt->tryout->questions()->orderBy('order', 'asc')->get()->map(function ($q) use ($userAnswers) {
            $selected = $userAnswers[$q->id] ?? null;
            $q->user_answer = $selected; 
            $q->is_correct_user = (string)$selected === (string)$q->correct_answer;
            return $q;
        });
        return Inertia::render('User/Tryout/Review', ['attempt' => $attempt, 'questions' => $questions, 'tryout' => $attempt->tryout]);
    }

    public function leaderboard(Request $request, Tryout $tryout)
    {
        $user = auth()->user();
        $pgTwk = ExamAttempt::PASSING_GRADE_TWK; $pgTiu = ExamAttempt::PASSING_GRADE_TIU; $pgTkp = ExamAttempt::PASSING_GRADE_TKP;

        $rankings = ExamAttempt::query()
            ->where('tryout_id', $tryout->id)
            ->whereIn('id', function($q) use ($tryout) {
                 $q->selectRaw('MAX(id)')->from('exam_attempts')->where('tryout_id', $tryout->id)->groupBy('user_id');
            })
            ->with('user')
            ->get()
            ->sortByDesc(fn($a) => sprintf('%d-%03d-%03d-%03d-%03d', ($a->twk_score >= $pgTwk && $a->tiu_score >= $pgTiu && $a->tkp_score >= $pgTkp), $a->total_score, $a->tkp_score, $a->tiu_score, $a->twk_score))
            ->values()
            ->map(fn($a, $i) => [
                'rank' => $i + 1, 'name' => $a->user->name, 'score' => $a->total_score, 'twk' => $a->twk_score, 'tiu' => $a->tiu_score, 'tkp' => $a->tkp_score, 'is_passed' => ($a->twk_score >= $pgTwk && $a->tiu_score >= $pgTiu && $a->tkp_score >= $pgTkp), 'duration' => $a->created_at->diff($a->completed_at)->format('%H:%I:%S'), 'is_me' => $a->user_id === auth()->id()
            ]);

        return Inertia::render('User/Tryout/Leaderboard', [
            'tryout' => $tryout, 
            'rankings' => $rankings, 
            'my_rank' => $rankings->firstWhere('is_me', true)
        ]);
    }

    public function certificate(ExamAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) abort(403);
        $pdf = Pdf::loadView('pdf.certificate', ['attempt' => $attempt->load(['user', 'tryout']), 'isPassed' => $attempt->is_passed])->setPaper('a4', 'landscape');
        return $pdf->stream('Sertifikat_' . $attempt->user->name . '.pdf');
    }

    public function collectiveRegister(Tryout $tryout)
    {
        return Inertia::render('User/Tryout/CollectiveRegister', ['tryout' => $tryout]);
    }

    public function checkEmail(Request $request)
    {
        return response()->json(['exists' => User::where('email', $request->email)->exists()]);
    }
}