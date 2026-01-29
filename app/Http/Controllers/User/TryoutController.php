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
     * Katalog Tryout - Menampilkan daftar paket yang tersedia.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $tryouts = Tryout::query()
            ->where('is_published', true)
            ->when($search, function($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->with(['transactions' => function($query) {
                $query->where('user_id', auth()->id())
                      ->whereIn('status', ['paid', 'success']);
            }])
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return Inertia::render('User/Tryout/Index', [
            'tryouts' => $tryouts,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Halaman Detail Paket.
     */
    public function show(Tryout $tryout)
    {
        return Inertia::render('User/Tryout/Show', [
            'tryout' => $tryout->loadCount('questions')
        ]);
    }

    /**
     * Halaman Registrasi / Form Pendaftaran.
     */
    public function registerForm(Tryout $tryout)
    {
        // Jika tryout gratis, arahkan langsung ke pendaftaran otomatis atau show
        if ($tryout->price <= 0) {
            return redirect()->route('tryout.show', $tryout->id);
        }

        return Inertia::render('User/Tryout/Register', [
            'tryout' => $tryout
        ]);
    }

    /**
     * Proses Pembayaran (Wallet atau Midtrans).
     */
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

        // Kalkulasi Harga & Diskon
        $price = $tryout->price;
        $discount = 0;
        if ($qty === 2) $discount = 0.05;
        elseif ($qty === 3) $discount = 0.10;
        elseif ($qty === 4) $discount = 0.15;
        elseif ($qty >= 5) $discount = 0.20;

        $finalAmount = ($price * $qty) * (1 - $discount);

        // SKENARIO A: WALLET
        if ($request->payment_method === 'wallet') {
            $user = auth()->user();

            if ($user->balance < $finalAmount) {
                return back()->withErrors(['payment' => 'Saldo dompet tidak mencukupi.']);
            }

            return DB::transaction(function () use ($user, $tryout, $finalAmount, $participants, $qty) {
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
                    'invoice_code' => 'INV-' . strtoupper(Str::random(10)),
                    'unit_price' => $tryout->price,
                    'qty' => $qty,
                    'amount' => $finalAmount,
                    'participants_data' => $participants->all(),
                    'status' => 'paid',
                ]);

                return redirect()->route('tryout.index')->with('message', 'Pendaftaran berhasil!');
            });
        }

        // SKENARIO B: MIDTRANS
        Transaction::create([
            'user_id' => auth()->id(),
            'tryout_id' => $tryout->id,
            'invoice_code' => 'INV-' . strtoupper(Str::random(10)),
            'unit_price' => $price,
            'qty' => $qty,
            'amount' => $finalAmount,
            'participants_data' => $participants->all(),
            'status' => 'pending',
        ]);

        return redirect()->route('checkout.show', $tryout->id);
    }

    /**
     * Halaman Instruksi (Wait) sebelum mulai.
     */
    public function wait(Tryout $tryout)
    {
        $hasAccess = Transaction::where('user_id', auth()->id())
            ->where('tryout_id', $tryout->id)
            ->whereIn('status', ['paid', 'success'])
            ->exists();

        if (!$hasAccess) {
            return redirect()->route('tryout.index')->with('error', 'Anda belum terdaftar.');
        }

        return Inertia::render('User/Tryout/Wait', [
            'tryout' => $tryout->loadCount('questions')
        ]);
    }

    /**
     * Halaman Ujian (BKN Mode).
     */
    public function examBkn(Tryout $tryout)
    {
        return $this->secureExamAccess($tryout, 'User/Tryout/ExamSheetBKN');
    }

    /**
     * Halaman Ujian (Regular Mode).
     */
    public function exam(Tryout $tryout)
    {
        return $this->secureExamAccess($tryout, 'User/Tryout/ExamSheet');
    }

    /**
     * Helper untuk memvalidasi akses ujian (Pembayaran & Waktu).
     */
    private function secureExamAccess(Tryout $tryout, $view)
    {
        $user = auth()->user();
        $now = Carbon::now();

        // 1. Cek Pembayaran
        $hasAccess = Transaction::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->whereIn('status', ['paid', 'success'])
            ->exists();

        if (!$hasAccess) {
            return redirect()->route('tryout.show', $tryout->id)->with('error', 'Silakan daftar terlebih dahulu.');
        }

        // 2. Cek Waktu Mulai
        if ($tryout->start_time && $now->lt($tryout->start_time)) {
            return redirect()->route('tryout.index')->with('error', 'Ujian belum dimulai. Akses dibuka: ' . $tryout->start_time->format('d M Y, H:i'));
        }

        $questions = $tryout->questions()->orderBy('order', 'asc')->get();

        return Inertia::render($view, [
            'tryout' => $tryout,
            'questions' => $questions,
            'user' => $user
        ]);
    }

    /**
     * Menyimpan jawaban dan hitung skor.
     */
    public function finish(Request $request, Tryout $tryout)
    {
        $userAnswers = $request->answers ?? []; 
        $questions = $tryout->questions;
        
        $twk = 0; $tiu = 0; $tkp = 0;

        foreach ($questions as $q) {
            $answer = $userAnswers[$q->id] ?? null;

            if ($q->type === 'TKP') {
                $tkp += (int) ($q->tkp_scores[$answer] ?? 0);
            } else {
                if ($answer === $q->correct_answer) {
                    if ($q->type === 'TWK') $twk += 5;
                    if ($q->type === 'TIU') $tiu += 5;
                }
            }
        }

        $attempt = ExamAttempt::create([
            'user_id' => auth()->id(),
            'tryout_id' => $tryout->id,
            'answers' => $userAnswers,
            'twk_score' => $twk,
            'tiu_score' => $tiu,
            'tkp_score' => $tkp,
            'total_score' => $twk + $tiu + $tkp,
            'status' => 'finished',
            'finished_at' => now(),
        ]);

        return redirect()->route('tryout.result', $attempt->id);
    }

    // ... (Result, History, Review, Certificate Tetap Sama) ...
    
    public function result(ExamAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) abort(403);
        return Inertia::render('User/Tryout/Result', ['attempt' => $attempt->load('tryout')]);
    }

    public function history()
    {
        $attempts = ExamAttempt::where('user_id', auth()->id())->with('tryout')->latest()->get();
        return Inertia::render('User/Tryout/History', ['attempts' => $attempts]);
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

        return Inertia::render('User/Tryout/Review', ['attempt' => $attempt, 'questions' => $questions]);
    }

    public function leaderboard(Tryout $tryout)
    {
        $rankings = ExamAttempt::where('tryout_id', $tryout->id)
            ->where('status', 'finished')
            ->with('user:id,name')
            ->orderByDesc('total_score')
            ->orderByRaw('TIMESTAMPDIFF(SECOND, created_at, finished_at) ASC')
            ->get()
            ->map(function ($attempt, $index) {
                return [
                    'rank' => $index + 1,
                    'name' => $attempt->user->name,
                    'score' => $attempt->total_score,
                    'is_me' => $attempt->user_id === auth()->id(),
                ];
            });

        return Inertia::render('User/Tryout/Leaderboard', [
            'tryout' => $tryout,
            'rankings' => $rankings,
            'my_rank' => $rankings->where('is_me', true)->first()
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
}