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
     * Gabungan Katalog & Tryout Saya
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isPremiumMember = $user->membership_expires_at && now()->lt($user->membership_expires_at);

        // --- 1. DATA KATALOG ---
        $catalogTryouts = Tryout::query()
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNotIn('type', ['akbar', 'adidaya'])->orWhereNull('type');
            })
            ->when(!$isPremiumMember, function($query) use ($user) {
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
            ->get();

        // --- 2. DATA TRYOUT SAYA ---
        $myTryouts = Tryout::query()
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNotIn('type', ['akbar', 'adidaya'])->orWhereNull('type');
            })
            ->where(function($query) use ($user, $isPremiumMember) {
                $query->whereHas('transactions', function($q) use ($user) {
                    $q->whereIn('status', ['paid', 'success'])
                      ->where(function($sq) use ($user) {
                          $sq->where('user_id', $user->id)
                            ->orWhereJsonContains('participants_data', $user->email);
                      });
                });

                if ($isPremiumMember) {
                    $query->orWhere(function($q) {
                        $q->whereNotIn('type', ['akbar', 'adidaya'])->orWhereNull('type');
                    });
                }
            })
            ->withCount(['examAttempts' => function($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->latest()
            ->get();

        return Inertia::render('User/Tryout/Index', [
            'catalogTryouts' => $catalogTryouts,
            'myTryouts' => $myTryouts,
            'filters' => $request->only(['search']),
            'isPremiumMember' => $isPremiumMember
        ]);
    }

    public function show(Tryout $tryout)
    {
        $isClosed = $tryout->end_date && now()->greaterThan($tryout->end_date);

        return Inertia::render('User/Tryout/Show', [
            'tryout' => $tryout,
            'is_registration_closed' => $isClosed,
        ]);
    }

    /**
     * Proses Pendaftaran Tryout
     */
    public function processRegistration(Request $request, Tryout $tryout)
    {
        if ($tryout->end_date && now()->greaterThan($tryout->end_date)) {
            return back()->withErrors(['message' => 'Pendaftaran untuk tryout ini sudah ditutup karena melewati batas waktu.']);
        }

        $request->validate([
            'payment_method' => 'required|in:wallet,midtrans',
            'emails' => 'array|max:5',
            'emails.*' => 'required|email|exists:users,email|distinct',
            'voucher_code' => 'nullable|string|exists:users,affiliate_code',
        ]);

        $participants = collect([auth()->user()->email])->merge($request->emails ?? [])->unique()->values();
        $qty = $participants->count();
        $discount = match($qty) { 2 => 0.05, 3 => 0.10, 4 => 0.15, default => $qty >= 5 ? 0.20 : 0 };

        $referrerId = null;
        $discountVoucher = 0;
        $commission = 0;

        if ($request->voucher_code) {
            $referrer = User::where('affiliate_code', $request->voucher_code)->first();
            if ($referrer && $referrer->id !== auth()->id()) {
                $referrerId = $referrer->id;
                $discountVoucher = 3000;
                $commission = 3000;
            }
        }

        $finalAmount = ($tryout->price * $qty) * (1 - $discount) - $discountVoucher;
        $finalAmount = max(0, $finalAmount); 
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
                    'referrer_id' => $referrerId,
                    'invoice_code' => $invoice, 
                    'unit_price' => $tryout->price, 
                    'qty' => $qty, 
                    'amount' => $finalAmount, 
                    'discount_amount' => $discountVoucher,
                    'affiliate_commission' => $commission,
                    'participants_data' => $participants->all(), 
                    'status' => 'paid'
                ]);

                if ($referrerId) {
                    User::find($referrerId)->increment('affiliate_balance', $commission);
                }
            });

            return redirect()->route('tryout.index')->with('success', 'Pembelian berhasil!');
        }

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

public function adidaya()
    {
        // Mengambil semua paket tryout bertipe 'adidaya' yang sudah di-publish
        $tryouts = Tryout::where('type', 'adidaya')
            ->where('is_published', true) // Opsional: hapus baris ini jika ingin yang belum dipublish tetap tampil
            ->latest()
            ->get();

        return Inertia::render('User/Tryout/AdidayaIndex', [
            'tryouts' => $tryouts
        ]);
    }
    public function myTryouts(Request $request)
    {
        $user = auth()->user();
        $userEmail = $user->email;
        $userId = $user->id;
        $isPremiumMember = $user->membership_expires_at && now()->lt($user->membership_expires_at);

        $tryouts = Tryout::query()
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNotIn('type', ['akbar', 'adidaya'])
                      ->orWhereNull('type');
            })
            ->where(function($query) use ($userId, $userEmail, $isPremiumMember) {
                $query->whereHas('transactions', function($q) use ($userId, $userEmail) {
                    $q->whereIn('status', ['paid', 'success'])
                      ->where(function($sq) use ($userId, $userEmail) {
                          $sq->where('user_id', $userId)
                            ->orWhereJsonContains('participants_data', $userEmail);
                      });
                });

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

        // --- UBAH LOGIKA BACK URL DI SINI ---
        // Jika tryout akbar, arahkan ke waiting room. 
        // Jika tryout biasa, arahkan ke history detail (sesuai tombol bawaan sebelumnya)
        $backUrl = ($tryout->type === 'akbar') 
            ? route('tryout-akbar.wait', $tryout->id) 
            : route('tryout.history.detail', $tryout->id); 

        $rank = ExamAttempt::where('tryout_id', $tryout->id)
            ->where('total_score', '>', $attempt->total_score)
            ->count() + 1;if ($attempt->user_id !== auth()->id()) abort(403);
        
        $attempt->load('tryout');
        $tryout = $attempt->tryout;

        // --- UBAH LOGIKA BACK URL DI SINI ---
        // Jika tryout akbar, arahkan ke waiting room. 
        // Jika tryout biasa, arahkan ke history detail (sesuai tombol bawaan sebelumnya)
        $backUrl = ($tryout->type === 'akbar') 
            ? route('tryout-akbar.wait', $tryout->id) 
            : route('tryout.history.detail', $tryout->id); 

        $rank = ExamAttempt::where('tryout_id', $tryout->id)
            ->where('total_score', '>', $attempt->total_score)
            ->count() + 1;

        $totalParticipants = ExamAttempt::where('tryout_id', $tryout->id)
            ->distinct('user_id')
            ->count();

        $pgTwk = ExamAttempt::PASSING_GRADE_TWK ?? 65;
        $pgTiu = ExamAttempt::PASSING_GRADE_TIU ?? 80;
        $pgTkp = ExamAttempt::PASSING_GRADE_TKP ?? 166;

        $scoreDetails = [
            ['category' => 'Tes Wawasan Kebangsaan (TWK)', 'score' => $attempt->twk_score, 'passing_grade' => $pgTwk, 'is_passed' => $attempt->twk_score >= $pgTwk],
            ['category' => 'Tes Intelegensia Umum (TIU)', 'score' => $attempt->tiu_score, 'passing_grade' => $pgTiu, 'is_passed' => $attempt->tiu_score >= $pgTiu],
            ['category' => 'Tes Karakteristik Pribadi (TKP)', 'score' => $attempt->tkp_score, 'passing_grade' => $pgTkp, 'is_passed' => $attempt->tkp_score >= $pgTkp]
        ];

        $attempt->status = $attempt->is_passed ? 'lulus' : 'tidak_lulus';

        // --- TAMBAHAN KALKULASI STATISTIK WAKTU ---
        $durationSeconds = 0;
        if ($attempt->created_at && $attempt->completed_at) {
            $durationSeconds = \Carbon\Carbon::parse($attempt->created_at)->diffInSeconds($attempt->completed_at);
        }
        
        $totalQuestions = $tryout->questions()->count() ?? 110;
        
        $timeStats = [
            'total_seconds' => max(0, $durationSeconds),
            'average_seconds' => $totalQuestions > 0 ? max(0, floor($durationSeconds / $totalQuestions)) : 0,
            'total_questions' => $totalQuestions,
        ];

        return Inertia::render('User/Tryout/Result', [
            'attempt' => $attempt,
            'tryout' => $tryout,
            'totalScore' => $attempt->total_score,
            'scoreDetails' => $scoreDetails,
            'ranking' => ['rank' => $rank, 'total_participants' => $totalParticipants],
            'timeStats' => $timeStats,
            // --- 2. KIRIMKAN VARIABEL KE VUE ---
            'backUrl' => $backUrl 
        ]);
    }

    public function historyDetail(Tryout $tryout)
    {
        $user = auth()->user();
        $isPremiumMember = $user->membership_expires_at && now()->lt($user->membership_expires_at);

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
        if ($tryout->end_date && now()->greaterThan($tryout->end_date)) {
            return redirect()->route('tryout.show', $tryout->id)
                ->with('error', 'Pendaftaran untuk tryout ini telah ditutup.');
        }

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
        $timeLeft = $request->time_left ?? 0; // Ambil sisa waktu dari frontend
        
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

        // PASTIKAN COMPLETED_AT DISIMPAN
        $attempt = ExamAttempt::create([
            'user_id' => auth()->id(), 
            'tryout_id' => $tryout->id, 
            'answers' => $answers, 
            'twk_score' => $twk, 
            'tiu_score' => $tiu, 
            'tkp_score' => $tkp, 
            'total_score' => $twk + $tiu + $tkp, 
            'completed_at' => now() 
        ]);
        
        // --- PERBAIKAN WAKTU ---
        // Hitung waktu riil yang dihabiskan berdasarkan durasi tryout dan sisa waktu
        $tryoutDurationSeconds = ($tryout->duration ?? 110) * 60;
        $timeTaken = $tryoutDurationSeconds - $timeLeft;
        $timeTaken = max(0, $timeTaken); // Memastikan tidak negatif
        
        // Memundurkan created_at agar rentang durasi terhitung benar (juga berpengaruh pada Leaderboard)
        $attempt->timestamps = false; // Matikan auto-update waktu Laravel
        $attempt->created_at = now()->subSeconds($timeTaken);
        $attempt->save();
        
        return redirect()->route('tryout.result', $attempt->id);
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
        $pgTwk = ExamAttempt::PASSING_GRADE_TWK ?? 65; 
        $pgTiu = ExamAttempt::PASSING_GRADE_TIU ?? 80; 
        $pgTkp = ExamAttempt::PASSING_GRADE_TKP ?? 166;

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
                'id' => $a->id,
                'rank' => $i + 1, 
                
                // DATA USER & INSTANSI
                'name' => $a->user ? $a->user->name : 'User', 
                'avatar' => $a->user ? $a->user->avatar : null,
                'agency_name' => $a->user ? $a->user->agency_name : null, 
                'province_code' => $a->user ? $a->user->province_code : null,
                'gender' => $a->user ? $a->user->gender : null, 
                
                // SKOR & STATUS
                'score' => $a->total_score, 
                'twk' => $a->twk_score, 
                'tiu' => $a->tiu_score, 
                'tkp' => $a->tkp_score, 
                'is_passed' => ($a->twk_score >= $pgTwk && $a->tiu_score >= $pgTiu && $a->tkp_score >= $pgTkp), 
                
                // DURASI WAKTU PENGERJAAN (DIPERBAIKI: Tanpa Error Syntax)
                // Jika selisihnya 0 (karena waktu mulai & selesai sama), paksa jadi minimal 1 detik dengan fungsi max()
                'duration' => $a->created_at 
                    ? max(1, \Carbon\Carbon::parse($a->created_at)->diffInSeconds($a->completed_at ?? $a->updated_at ?? $a->created_at))
                    : 0,
                
                'is_me' => $a->user_id === auth()->id()
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