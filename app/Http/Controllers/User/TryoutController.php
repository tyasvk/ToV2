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
    /**
     * HALAMAN KATALOG
     */
    public function index(Request $request)
    {
        $tryouts = Tryout::query()
            ->where('is_published', true)
            ->whereDoesntHave('transactions', function($query) {
                $query->where('user_id', auth()->id())
                      ->whereIn('status', ['paid', 'success']);
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

    /**
     * HALAMAN TRYOUT SAYA
     */
    public function myTryouts(Request $request)
    {
        $tryouts = Tryout::query()
            ->where('is_published', true)
            ->whereHas('transactions', function($query) {
                $query->where('user_id', auth()->id())
                      ->whereIn('status', ['paid', 'success']);
            })
            ->withCount(['examAttempts' => function($q) {
                $q->where('user_id', auth()->id());
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

    private function validateAccess(Tryout $tryout)
    {
        $user = auth()->user();
        $now = Carbon::now();

        $hasPaid = Transaction::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->whereIn('status', ['paid', 'success'])
            ->exists();

        if (!$hasPaid) {
            return [
                'allowed' => false,
                'route' => 'tryout.register',
                'message' => 'Anda belum terdaftar. Silakan lakukan pendaftaran.'
            ];
        }

        if ($tryout->started_at && $now->lt($tryout->started_at)) {
            return [
                'allowed' => false,
                'route' => 'tryout.my',
                'message' => 'Ujian belum dimulai. Jadwal: ' . $tryout->started_at->format('d M Y, H:i')
            ];
        }

        $attemptsCount = ExamAttempt::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->count();

        if ($attemptsCount >= 3) {
            return [
                'allowed' => false,
                'route' => 'tryout.history.detail',
                'message' => 'Anda telah mencapai batas maksimal 3x pengerjaan.'
            ];
        }

        return ['allowed' => true];
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

    /**
     * PERBAIKAN: finish method
     * Menggunakan completed_at dan menghapus status.
     */
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
            'completed_at' => now(), // PERBAIKAN: Gunakan completed_at, bukan finished_at
            // Hapus 'status' => 'finished' karena kolom tidak ada
        ]);

        return redirect()->route('tryout.result', $attempt->id);
    }

    public function result(ExamAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) abort(403);
        return Inertia::render('User/Tryout/Result', ['attempt' => $attempt->load('tryout')]);
    }

    public function historyDetail(Tryout $tryout)
    {
        $hasPaid = Transaction::where('user_id', auth()->id())
            ->where('tryout_id', $tryout->id)
            ->whereIn('status', ['paid', 'success'])
            ->exists();

        if (!$hasPaid) abort(403, 'Akses ditolak.');

        $attempts = ExamAttempt::where('user_id', auth()->id())
            ->where('tryout_id', $tryout->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('User/Tryout/HistoryDetail', [
            'tryout' => $tryout,
            'attempts' => $attempts
        ]);
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
            $q->user_selected_answer = $selected;
            $q->is_correct = (string)$selected === (string)$q->correct_answer;
            return $q;
        });

        return Inertia::render('User/Tryout/Review', [
            'attempt' => $attempt,
            'questions' => $questions
        ]);
    }

    /**
     * PERBAIKAN: leaderboard method
     * Menggunakan completed_at sebagai penanda selesai dan waktu urut.
     */
public function leaderboard(Request $request, Tryout $tryout)
    {
        $user = auth()->user();
        $search = $request->input('search');
        $scope = $request->input('scope', 'nasional'); 

        // 1. Ambil ID Pengerjaan Pertama (First Attempt) setiap user
        $firstAttemptIds = ExamAttempt::selectRaw('MIN(id) as id')
            ->where('tryout_id', $tryout->id)
            ->whereNotNull('completed_at')
            ->groupBy('user_id');

        // 2. Query Utama
        $query = ExamAttempt::query()
            ->whereIn('exam_attempts.id', $firstAttemptIds) 
            ->join('users', 'exam_attempts.user_id', '=', 'users.id') 
            ->select('exam_attempts.*'); 

        // 3. Filter Scope (Nasional / Provinsi)
        if ($scope === 'provinsi') {
            $query->where('users.province', $user->province);
        }

        // 4. Filter Pencarian Nama
        if ($search) {
            $query->where('users.name', 'like', "%{$search}%");
        }

        // 5. Logika Perangkingan (PERBAIKAN: Tambahkan prefix 'exam_attempts.')
        $query->orderByRaw("
            (CASE WHEN exam_attempts.twk_score >= 65 AND exam_attempts.tiu_score >= 80 AND exam_attempts.tkp_score >= 166 THEN 1 ELSE 0 END) DESC,
            exam_attempts.total_score DESC,
            exam_attempts.tkp_score DESC,
            exam_attempts.tiu_score DESC,
            exam_attempts.twk_score DESC,
            TIMESTAMPDIFF(SECOND, exam_attempts.created_at, exam_attempts.completed_at) ASC
        ");

        // Ambil Data
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

/**
     * API Check Email
     */
    public function checkEmail(Request $request)
    {
        // Validasi input
        if (!$request->has('email')) {
            return response()->json(['exists' => false]);
        }

        // Cek database
        $exists = \App\Models\User::where('email', $request->email)->exists();
        
        return response()->json(['exists' => $exists]);
    }
}