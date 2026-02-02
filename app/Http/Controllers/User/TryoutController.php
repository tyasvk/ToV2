<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use App\Models\Question;
use App\Models\ExamAttempt;
use App\Models\Transaction;
use App\Models\WalletTransaction;
use App\Models\User; // Tambahkan ini
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class TryoutController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $tryouts = Tryout::query()
            ->where('is_published', true)
            // PERBAIKAN 1: Filter agar tipe 'akbar' tidak muncul di list umum
            ->where(function ($query) {
                $query->where('type', '!=', 'akbar')
                      ->orWhereNull('type');
            })
            // -----------------------------------------------------------
            ->whereDoesntHave('transactions', function($query) use ($user) {
                $query->whereIn('status', ['paid', 'success'])
                      ->where(function($subQuery) use ($user) {
                          $subQuery->where('user_id', $user->id)
                                   ->orWhereJsonContains('participants_data', $user->email);
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

// GANTI method quantum() menjadi seperti ini:

/**
 * Tampilkan halaman Nusantara Adidaya (Premium Membership)
 */
public function adidaya()
{
    // Arahkan ke file 'resources/js/Pages/User/Tryout/AdidayaIndex.vue'
    return Inertia::render('User/Tryout/AdidayaIndex', [
        'exclusiveTryouts' => [], // Nanti isi dari DB
        'generalTryouts' => []
    ]);
}

    public function myTryouts(Request $request)
    {
        $userEmail = auth()->user()->email;
        $userId = auth()->id();

        $tryouts = Tryout::query()
            ->where('is_published', true)
            ->where(function ($query) {
                $query->where('type', '!=', 'akbar')
                      ->orWhereNull('type');
            })
            ->whereHas('transactions', function($query) use ($userId, $userEmail) {
                $query->whereIn('status', ['paid', 'success'])
                      ->where(function($q) use ($userId, $userEmail) {
                          $q->where('user_id', $userId)
                            ->orWhereJsonContains('participants_data', $userEmail);
                      });
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

        // PERBAIKAN 2: Gunakan Constants dari Model
        $pgTwk = ExamAttempt::PASSING_GRADE_TWK;
        $pgTiu = ExamAttempt::PASSING_GRADE_TIU;
        $pgTkp = ExamAttempt::PASSING_GRADE_TKP;

        $scoreDetails = [
            [
                'category' => 'Tes Wawasan Kebangsaan (TWK)',
                'score' => $attempt->twk_score,
                'passing_grade' => $pgTwk,
                'is_passed' => $attempt->twk_score >= $pgTwk
            ],
            [
                'category' => 'Tes Intelegensia Umum (TIU)',
                'score' => $attempt->tiu_score,
                'passing_grade' => $pgTiu,
                'is_passed' => $attempt->tiu_score >= $pgTiu
            ],
            [
                'category' => 'Tes Karakteristik Pribadi (TKP)',
                'score' => $attempt->tkp_score,
                'passing_grade' => $pgTkp,
                'is_passed' => $attempt->tkp_score >= $pgTkp
            ]
        ];

        // Status attempt diperbarui (hanya untuk tampilan, tidak save ke DB kecuali perlu)
        $attempt->status = $attempt->is_passed ? 'lulus' : 'tidak_lulus';

        return Inertia::render('User/Tryout/Result', [
            'attempt' => $attempt,
            'tryout' => $tryout,
            'totalScore' => $attempt->total_score,
            'scoreDetails' => $scoreDetails,
            'ranking' => [
                'rank' => $rank,
                'total_participants' => $totalParticipants
            ]
        ]);
    }

    public function historyDetail(Tryout $tryout)
    {
        $user = auth()->user();

        $hasPaid = Transaction::where('tryout_id', $tryout->id)
            ->whereIn('status', ['paid', 'success'])
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereJsonContains('participants_data', $user->email);
            })
            ->exists();

        if (!$hasPaid) abort(403, 'Akses ditolak.');

        if ($tryout->type === 'akbar') {
            $attempt = ExamAttempt::where('user_id', $user->id)
                ->where('tryout_id', $tryout->id)
                ->latest()
                ->first();

            if ($attempt) {
                return redirect()->route('tryout.result', $attempt->id);
            }
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

        $hasPaid = Transaction::where('tryout_id', $tryout->id)
            ->whereIn('status', ['paid', 'success'])
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereJsonContains('participants_data', $user->email);
            })
            ->exists();

        if (!$hasPaid) {
            return [
                'allowed' => false,
                'route' => 'tryout.register',
                'params' => $tryout->id,
                'message' => 'Anda belum terdaftar di event ini.'
            ];
        }

        if ($tryout->type === 'akbar') {
            if ($now->lt($tryout->started_at)) {
                return [
                    'allowed' => false,
                    'route' => 'tryout-akbar.index',
                    'message' => 'Event belum dimulai! Jadwal: ' . Carbon::parse($tryout->started_at)->format('d M Y, H:i') . ' WIB'
                ];
            }

            if ($now->gt($tryout->ended_at)) {
                return [
                    'allowed' => false,
                    'route' => 'tryout-akbar.index',
                    'message' => 'Event sudah berakhir pada: ' . Carbon::parse($tryout->ended_at)->format('d M Y, H:i') . ' WIB'
                ];
            }
        }

        $maxAttempts = ($tryout->type === 'akbar') ? 1 : 3;
        
        $attemptsCount = ExamAttempt::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->count();

        if ($attemptsCount >= $maxAttempts) {
            return [
                'allowed' => false,
                'route' => 'tryout.history',
                'message' => 'Anda sudah menyelesaikan ujian ini.'
            ];
        }

        return ['allowed' => true];
    }

    public function registerForm(Tryout $tryout)
    {
        return Inertia::render('User/Tryout/Register', [
            'tryout' => $tryout
        ]);
    }

    public function processRegistration(Request $request, Tryout $tryout)
    {
        $request->validate([
            'payment_method' => 'required|in:wallet,midtrans',
            'emails' => 'array|max:5',
            'emails.*' => 'required|email|exists:users,email|distinct',
        ]);

        $participants = collect([auth()->user()->email]);
        if ($request->emails) {
            $participants = $participants->merge($request->emails);
        }
        $participants = $participants->unique()->values();
        $qty = $participants->count();

        $discount = match($qty) {
            2 => 0.05,
            3 => 0.10,
            4 => 0.15,
            default => $qty >= 5 ? 0.20 : 0
        };

        $finalAmount = ($tryout->price * $qty) * (1 - $discount);
        $invoice = 'INV-' . strtoupper(Str::random(10));

        // Pembayaran Via Wallet
        if ($request->payment_method === 'wallet') {
            $user = auth()->user();

            if ($user->balance < $finalAmount) {
                return back()->withErrors(['payment' => 'Saldo dompet tidak mencukupi.']);
            }

            DB::transaction(function () use ($user, $tryout, $finalAmount, $participants, $qty, $invoice) {
                $user->decrement('balance', $finalAmount);

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $finalAmount,
                    'description' => 'Bayar Tryout: ' . $tryout->title,
                    'status' => 'success',
                    'proof_payment' => 'WALLET-SYSTEM',
                ]);

                Transaction::create([
                    'user_id' => $user->id,
                    'tryout_id' => $tryout->id,
                    'invoice_code' => $invoice,
                    'unit_price' => $tryout->price,
                    'qty' => $qty,
                    'amount' => $finalAmount,
                    'participants_data' => $participants->all(),
                    'status' => 'paid',
                ]);
            });

            // Gunakan 'success' agar tertangkap oleh SweetAlert di Index.vue
return redirect()->route('tryout.index')
    ->with('success', 'Pembelian berhasil! Silahkan cek menu Tryout Saya.');
        }

        // PERBAIKAN 3: Pembayaran Midtrans (Fix Redirect ke Checkout)
        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'tryout_id' => $tryout->id,
            'invoice_code' => $invoice,
            'unit_price' => $tryout->price,
            'qty' => $qty,
            'amount' => $finalAmount, // Harga sudah termasuk diskon
            'participants_data' => $participants->all(),
            'status' => 'pending',
        ]);

        // Redirect ke route checkout dengan parameter TRANSACTION, bukan TRYOUT
        return redirect()->route('checkout.show', $transaction->id);
    }

    public function wait(Tryout $tryout)
    {
        $check = $this->validateAccess($tryout);
        if (!$check['allowed']) {
            return redirect()->route($check['route'], $tryout->id)->with('error', $check['message']);
        }

        return Inertia::render('User/Tryout/Wait', [
            'tryout' => $tryout->loadCount('questions')
        ]);
    }

    public function examBkn(Tryout $tryout)
    {
        $check = $this->validateAccess($tryout);
        if (!$check['allowed']) {
            return redirect()->route($check['route'], $tryout->id)->with('error', $check['message']);
        }

        return Inertia::render('User/Tryout/ExamSheetBKN', [
            'tryout' => $tryout,
            'questions' => $tryout->questions()->orderBy('order', 'asc')->get(),
            'user' => auth()->user()
        ]);
    }

    public function exam(Tryout $tryout)
    {
        $check = $this->validateAccess($tryout);
        if (!$check['allowed']) {
            return redirect()->route($check['route'], $tryout->id)->with('error', $check['message']);
        }

        return Inertia::render('User/Tryout/ExamSheet', [
            'tryout' => $tryout,
            'questions' => $tryout->questions()->orderBy('order', 'asc')->get(),
            'user' => auth()->user()
        ]);
    }

    public function finish(Request $request, Tryout $tryout)
    {
        $answers = $request->answers ?? [];
        $questions = $tryout->questions;
        
        $twk = 0; $tiu = 0; $tkp = 0;

        foreach ($questions as $q) {
            $ans = $answers[$q->id] ?? null;

            if ($q->type === 'TKP') {
                // Pastikan tkp_scores di model Question di-cast ke array
                $tkpScoreMap = is_string($q->tkp_scores) ? json_decode($q->tkp_scores, true) : $q->tkp_scores;
                $tkp += (int) ($tkpScoreMap[$ans] ?? 0);
            } else {
                if ($ans === $q->correct_answer) {
                    if ($q->type === 'TWK') $twk += 5;
                    if ($q->type === 'TIU') $tiu += 5;
                }
            }
        }

        $attempt = ExamAttempt::create([
            'user_id' => auth()->id(),
            'tryout_id' => $tryout->id,
            'answers' => $answers,
            'twk_score' => $twk,
            'tiu_score' => $tiu,
            'tkp_score' => $tkp,
            'total_score' => $twk + $tiu + $tkp,
            'completed_at' => now(), 
        ]);

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
        $userAnswers = $attempt->answers ?? []; // Pastikan ini array
        
        $questions = $attempt->tryout->questions()->orderBy('order', 'asc')->get()->map(function ($q) use ($userAnswers) {
            // Ambil jawaban user dari array JSON
            $selected = $userAnswers[$q->id] ?? null;
            
            // Format untuk Frontend (Vue/React)
            $q->user_answer = $selected; 
            $q->is_correct_user = (string)$selected === (string)$q->correct_answer;
            
            return $q;
        });

        return Inertia::render('User/Tryout/Review', [
            'attempt' => $attempt,
            'questions' => $questions,
            'tryout' => $attempt->tryout
        ]);
    }

    // PERBAIKAN 4: Leaderboard Logic yang Dioptimasi
    public function leaderboard(Request $request, Tryout $tryout)
    {
        $user = auth()->user();
        $search = $request->input('search');
        $scope = $request->input('scope', 'nasional'); 

        // Konstanta untuk Query SQL
        $pgTwk = ExamAttempt::PASSING_GRADE_TWK;
        $pgTiu = ExamAttempt::PASSING_GRADE_TIU;
        $pgTkp = ExamAttempt::PASSING_GRADE_TKP;

        // Ambil ID attempt TERBAIK per user (Highest Total Score)
        // Jika skor sama, ambil yang lulus passing grade. Jika sama, ambil yang tkp tertinggi.
        $subQuery = ExamAttempt::select('id')
            ->where('tryout_id', $tryout->id)
            ->whereNotNull('completed_at')
            ->orderByRaw("total_score DESC, tkp_score DESC")
            ->limit(10000); // Limit untuk performa jika data besar

        // Query Utama
        $query = ExamAttempt::query()
             // Trik ambil unik user_id dengan nilai tertinggi (Grouping di PHP/Collection kadang lebih mudah jika data kecil)
             // Untuk SQL Strict Mode, lebih aman join manual atau window function.
             // Di sini kita pakai pendekatan Grouping User ID yang sudah lulus filter tryout.
            ->where('tryout_id', $tryout->id)
            ->whereIn('id', function($q) use ($tryout) {
                 $q->selectRaw('MAX(id)') // Ambil ID terakhir atau logic max score custom
                   ->from('exam_attempts')
                   ->where('tryout_id', $tryout->id)
                   ->groupBy('user_id');
            })
            ->with('user');

        if ($scope === 'provinsi') {
            $query->whereHas('user', function($q) use ($user) {
                $q->where('province', $user->province);
            });
        }

        if ($search) {
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Logic Ranking CPNS: Lulus PG > Total Score > TKP > TIU > TWK > Waktu
        $rankings = $query->get()
            ->sortByDesc(function ($attempt) use ($pgTwk, $pgTiu, $pgTkp) {
                $isPassed = ($attempt->twk_score >= $pgTwk && $attempt->tiu_score >= $pgTiu && $attempt->tkp_score >= $pgTkp) ? 1 : 0;
                // Buat skor komposit untuk sorting
                return sprintf('%d-%03d-%03d-%03d-%03d', 
                    $isPassed, 
                    $attempt->total_score, 
                    $attempt->tkp_score, 
                    $attempt->tiu_score, 
                    $attempt->twk_score
                );
            })
            ->values()
            ->map(function ($attempt, $index) use ($pgTwk, $pgTiu, $pgTkp) {
                $duration = $attempt->created_at->diff($attempt->completed_at);
                $isPassed = ($attempt->twk_score >= $pgTwk && $attempt->tiu_score >= $pgTiu && $attempt->tkp_score >= $pgTkp);
                
                return [
                    'rank' => $index + 1,
                    'name' => $attempt->user->name,
                    'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($attempt->user->name) . '&background=random&color=fff',
                    'score' => $attempt->total_score,
                    'twk' => $attempt->twk_score,
                    'tiu' => $attempt->tiu_score,
                    'tkp' => $attempt->tkp_score,
                    'is_passed' => $isPassed,
                    'duration' => $duration->format('%H:%I:%S'),
                    'is_me' => $attempt->user_id === auth()->id(),
                    'province' => $attempt->user->province ?? '-',
                ];
            });

        $myRank = $rankings->firstWhere('is_me', true);

        return Inertia::render('User/Tryout/Leaderboard', [
            'tryout' => $tryout,
            'rankings' => $rankings, // Sudah collection/array
            'my_rank' => $myRank,
            'filters' => [
                'search' => $search,
                'scope' => $scope,
                'user_province' => $user->province,
            ]
        ]);
    }

    public function certificate(ExamAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) abort(403);
        $attempt->load(['user', 'tryout']);
        
        // Gunakan accessor is_passed
        $isPassed = $attempt->is_passed;
        
        $pdf = Pdf::loadView('pdf.certificate', compact('attempt', 'isPassed'))->setPaper('a4', 'landscape');
        return $pdf->stream('Sertifikat_' . $attempt->user->name . '.pdf');
    }

    public function collectiveRegister(Tryout $tryout)
    {
        return Inertia::render('User/Tryout/CollectiveRegister', ['tryout' => $tryout]);
    }

    public function checkEmail(Request $request)
    {
        if (!$request->has('email')) {
            return response()->json(['exists' => false]);
        }

        $exists = User::where('email', $request->email)->exists();
        
        return response()->json(['exists' => $exists]);
    }
}