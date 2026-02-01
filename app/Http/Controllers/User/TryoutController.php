<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use App\Models\Question;
use App\Models\ExamAttempt;
use App\Models\Transaction;
use App\Models\WalletTransaction;
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

        $passingGrades = [
            'TWK' => 65,
            'TIU' => 80,
            'TKP' => 166
        ];

        $scoreDetails = [
            [
                'category' => 'Tes Wawasan Kebangsaan (TWK)',
                'score' => $attempt->twk_score,
                'passing_grade' => $passingGrades['TWK'],
                'is_passed' => $attempt->twk_score >= $passingGrades['TWK']
            ],
            [
                'category' => 'Tes Intelegensia Umum (TIU)',
                'score' => $attempt->tiu_score,
                'passing_grade' => $passingGrades['TIU'],
                'is_passed' => $attempt->tiu_score >= $passingGrades['TIU']
            ],
            [
                'category' => 'Tes Karakteristik Pribadi (TKP)',
                'score' => $attempt->tkp_score,
                'passing_grade' => $passingGrades['TKP'],
                'is_passed' => $attempt->tkp_score >= $passingGrades['TKP']
            ]
        ];

        $isAllPassed = collect($scoreDetails)->every(fn($item) => $item['is_passed']);
        $attempt->status = $isAllPassed ? 'lulus' : 'tidak_lulus';

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

        $hasPaid = \App\Models\Transaction::where('tryout_id', $tryout->id)
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
                    'message' => 'Event belum dimulai! Jadwal: ' . \Carbon\Carbon::parse($tryout->started_at)->format('d M Y, H:i') . ' WIB'
                ];
            }

            if ($now->gt($tryout->ended_at)) {
                return [
                    'allowed' => false,
                    'route' => 'tryout-akbar.index',
                    'message' => 'Event sudah berakhir pada: ' . \Carbon\Carbon::parse($tryout->ended_at)->format('d M Y, H:i') . ' WIB'
                ];
            }
        }

        $maxAttempts = ($tryout->type === 'akbar') ? 1 : 3;
        
        $attemptsCount = \App\Models\ExamAttempt::where('user_id', $user->id)
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

            return redirect()->route('tryout.index')->with('message', 'Pendaftaran berhasil!');
        }

        Transaction::create([
            'user_id' => auth()->id(),
            'tryout_id' => $tryout->id,
            'invoice_code' => $invoice,
            'unit_price' => $tryout->price,
            'qty' => $qty,
            'amount' => $finalAmount,
            'participants_data' => $participants->all(),
            'status' => 'pending',
        ]);

        return redirect()->route('checkout.show', $tryout->id);
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
                $tkp += (int) ($q->tkp_scores[$ans] ?? 0);
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

    // -------------------------------------------------------------
    // PERBAIKAN UTAMA DI SINI (METHOD REVIEW)
    // -------------------------------------------------------------
    public function review(ExamAttempt $attempt) 
    {
        if ($attempt->user_id !== auth()->id()) abort(403);
        
        $attempt->load('tryout');
        $userAnswers = $attempt->answers ?? [];
        
        $questions = $attempt->tryout->questions()->orderBy('order', 'asc')->get()->map(function ($q) use ($userAnswers) {
            $selected = $userAnswers[$q->id] ?? null;
            
            // FIX: Gunakan nama 'user_answer' agar sesuai dengan Vue
            $q->user_answer = $selected; 
            
            // Optional: Tetap simpan is_correct untuk kemudahan
            $q->is_correct = (string)$selected === (string)$q->correct_answer;
            
            return $q;
        });

        return Inertia::render('User/Tryout/Review', [
            'attempt' => $attempt,
            'questions' => $questions,
            'tryout' => $attempt->tryout // Pastikan tryout dikirim untuk deteksi mode
        ]);
    }

    public function leaderboard(Request $request, Tryout $tryout)
    {
        $user = auth()->user();
        $search = $request->input('search');
        $scope = $request->input('scope', 'nasional'); 

        $firstAttemptIds = ExamAttempt::selectRaw('MIN(id) as id')
            ->where('tryout_id', $tryout->id)
            ->whereNotNull('completed_at')
            ->groupBy('user_id');

        $query = ExamAttempt::query()
            ->whereIn('exam_attempts.id', $firstAttemptIds) 
            ->join('users', 'exam_attempts.user_id', '=', 'users.id') 
            ->select('exam_attempts.*'); 

        if ($scope === 'provinsi') {
            $query->where('users.province', $user->province);
        }

        if ($search) {
            $query->where('users.name', 'like', "%{$search}%");
        }

        $query->orderByRaw("
            (CASE WHEN exam_attempts.twk_score >= 65 AND exam_attempts.tiu_score >= 80 AND exam_attempts.tkp_score >= 166 THEN 1 ELSE 0 END) DESC,
            exam_attempts.total_score DESC,
            exam_attempts.tkp_score DESC,
            exam_attempts.tiu_score DESC,
            exam_attempts.twk_score DESC,
            TIMESTAMPDIFF(SECOND, exam_attempts.created_at, exam_attempts.completed_at) ASC
        ");

        $rankings = $query->get()->map(function ($attempt, $index) {
            $duration = $attempt->created_at->diff($attempt->completed_at);
            
            return [
                'rank' => $index + 1,
                'name' => $attempt->user->name,
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($attempt->user->name) . '&background=random&color=fff',
                'score' => $attempt->total_score,
                'twk' => $attempt->twk_score,
                'tiu' => $attempt->tiu_score,
                'tkp' => $attempt->tkp_score,
                'is_passed' => ($attempt->twk_score >= 65 && $attempt->tiu_score >= 80 && $attempt->tkp_score >= 166),
                'duration' => $duration->format('%H:%I:%S'),
                'is_me' => $attempt->user_id === auth()->id(),
                'province' => $attempt->user->province ?? '-',
            ];
        });

        $myRank = $rankings->firstWhere('is_me', true);

        return Inertia::render('User/Tryout/Leaderboard', [
            'tryout' => $tryout,
            'rankings' => $rankings->values(),
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
        
        $isPassed = $attempt->twk_score >= 65 && $attempt->tiu_score >= 80 && $attempt->tkp_score >= 166;
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

        $exists = \App\Models\User::where('email', $request->email)->exists();
        
        return response()->json(['exists' => $exists]);
    }
}