<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use App\Models\Question;
use App\Models\ExamAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Transaction; 
use Illuminate\Support\Str;
use App\Models\WalletTransaction; // Tambahkan ini di atas
use Illuminate\Support\Facades\DB;  // Tambahkan ini di atas

class TryoutController extends Controller
{
    /**
     * Menampilkan daftar tryout yang tersedia (Katalog).
     */
public function index()
{
    $tryouts = Tryout::where('is_published', true)
        ->with(['transactions' => function($query) {
            // Hanya ambil transaksi milik user ini yang sudah lunas
            $query->where('user_id', auth()->id())
                  ->whereIn('status', ['paid', 'success']);
        }])
        ->latest()
        ->get();

    return inertia('User/Tryout/Index', [
        'tryouts' => $tryouts
    ]);
}

public function wait(Tryout $tryout)
{
    // Cek apakah user sudah bayar/terdaftar
    $isRegistered = \App\Models\Transaction::where('user_id', auth()->id())
        ->where('tryout_id', $tryout->id)
        ->whereIn('status', ['paid', 'success'])
        ->exists();

    if (!$isRegistered) {
        return redirect()->route('tryout.index')->with('error', 'Anda belum terdaftar di tryout ini.');
    }

    return inertia('User/Tryout/Wait', [
        'tryout' => $tryout->loadCount('questions')
    ]);
}

public function leaderboard(Tryout $tryout)
{
    // Ambil semua hasil ujian (attempts) untuk tryout ini
    // Diurutkan berdasarkan skor tertinggi, lalu waktu pengerjaan tercepat
    $rankings = \App\Models\ExamAttempt::where('tryout_id', $tryout->id)
        ->where('status', 'finished')
        ->with('user:id,name')
        ->select('user_id', 'total_score', 'started_at', 'finished_at')
        // Logic peringkat: Skor tertinggi, jika sama maka yang selesai lebih cepat menang
        ->orderByDesc('total_score')
        ->orderByRaw('TIMESTAMPDIFF(SECOND, started_at, finished_at) ASC')
        ->get()
        ->map(function ($attempt, $index) {
            return [
                'rank' => $index + 1,
                'name' => $attempt->user->name,
                'score' => $attempt->total_score,
                'is_me' => $attempt->user_id === auth()->id(),
            ];
        });

    return inertia('User/Tryout/Leaderboard', [
        'tryout' => $tryout,
        'rankings' => $rankings,
        'my_rank' => $rankings->where('is_me', true)->first()
    ]);
}

    public function processRegistration(Request $request, Tryout $tryout)
    {
        // 1. Validasi Input
        $request->validate([
            'payment_method' => 'required|in:wallet,midtrans',
            'emails' => 'array|max:5',
            'emails.*' => 'required|email|exists:users,email|distinct',
        ]);

        // 2. Siapkan Data Peserta (Ketua + Anggota)
        $participants = collect([auth()->user()->email]);
        if ($request->emails) {
            $participants = $participants->merge($request->emails);
        }
        $participants = $participants->unique()->values();
        $qty = $participants->count();

        // 3. Kalkulasi Harga & Diskon Kolektif
        $price = $tryout->price;
        $discount = 0;
        if ($qty === 2) $discount = 0.05;
        elseif ($qty === 3) $discount = 0.10;
        elseif ($qty === 4) $discount = 0.15;
        elseif ($qty >= 5) $discount = 0.20;

        $finalAmount = ($price * $qty) * (1 - $discount);

        // --- SKENARIO A: PEMBAYARAN VIA SALDO WALLET ---
        if ($request->payment_method === 'wallet') {
            $user = auth()->user();

            // Cek apakah saldo cukup
            if ($user->balance < $finalAmount) {
                return back()->withErrors(['payment' => 'Saldo dompet tidak mencukupi untuk pendaftaran ini.']);
            }

            return DB::transaction(function () use ($user, $tryout, $finalAmount, $participants, $qty) {
                // A1. Potong Saldo User
                $user->decrement('balance', $finalAmount);

                // A2. Catat Riwayat Wallet (Debit)
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $finalAmount,
                    'description' => 'Bayar Tryout: ' . $tryout->title,
                    'status' => 'success',
                    'proof_payment' => 'WALLET-SYSTEM',
                ]);

                // A3. Buat Transaksi Tryout Langsung "Success"
                Transaction::create([
                    'user_id' => $user->id,
                    'tryout_id' => $tryout->id,
                    'invoice_code' => 'INV-' . strtoupper(Str::random(10)),
                    'unit_price' => $tryout->price,
                    'qty' => $qty,
                    'amount' => $finalAmount,
                    'participants_data' => $participants->all(),
                    'status' => 'paid', // Langsung aktif
                ]);

                return redirect()->route('tryout.index')
                    ->with('message', 'Pendaftaran berhasil menggunakan saldo dompet!');
            });
        }

        // --- SKENARIO B: PEMBAYARAN VIA MIDTRANS ---
        // Buat transaksi dengan status "pending" lalu lempar ke CheckoutController
        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'tryout_id' => $tryout->id,
            'invoice_code' => 'INV-' . strtoupper(Str::random(10)),
            'unit_price' => $price,
            'qty' => $qty,
            'amount' => $finalAmount,
            'participants_data' => $participants->all(),
            'status' => 'pending',
        ]);

        // Redirect ke halaman checkout Midtrans Anda
        return redirect()->route('checkout.show', $tryout->id);
    }

    /**
     * Menampilkan Form Pendaftaran
     */
    public function registerForm(Tryout $tryout)
    {
        if (!$tryout->is_paid) {
            return redirect()->route('tryout.show', $tryout->id);
        }

        return Inertia::render('User/Tryout/Register', [
            'tryout' => $tryout
        ]);
    }

    /**
     * Menampilkan detail tryout sebelum mengerjakan.
     */
    public function show(Tryout $tryout)
    {
        return Inertia::render('User/Tryout/Show', [
            'tryout' => $tryout->loadCount('questions')
        ]);
    }

    /**
     * Halaman pengerjaan soal (Ujian berlangsung).
     */
    public function exam(Tryout $tryout)
    {
        if ($tryout->started_at && now() < $tryout->started_at) {
            return redirect()->route('tryout.index')->with('error', 'Ujian belum dimulai.');
        }

        $questions = $tryout->questions()->orderBy('order', 'asc')->get();

        return Inertia::render('Tryout/ExamSheet', [
            'tryout' => $tryout,
            'questions' => $questions
        ]);
    }

    /**
     * Simulasi tampilan BKN.
     */
    public function examBkn(Tryout $tryout)
    {
        $questions = $tryout->questions()->orderBy('order', 'asc')->get();

        return Inertia::render('Tryout/ExamSheetBKN', [
            'tryout' => $tryout,
            'questions' => $questions,
            'user' => auth()->user()
        ]);
    }

    /**
     * Menyimpan jawaban user dan menghitung skor.
     */
    public function finish(Request $request, Tryout $tryout)
    {
        $userAnswers = $request->answers ?? []; 
        $questions = $tryout->questions;
        
        $twk = 0; 
        $tiu = 0; 
        $tkp = 0;

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
            'completed_at' => now(),
        ]);

        return redirect()->route('tryout.result', $attempt->id);
    }

    /**
     * Menampilkan halaman hasil ujian.
     */
    public function result(ExamAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('User/Tryout/Result', [
            'attempt' => $attempt->load('tryout')
        ]);
    }

    /**
     * Menampilkan riwayat ujian user.
     */
    public function history()
    {
        $attempts = ExamAttempt::where('user_id', auth()->id())
            ->with('tryout')
            ->latest()
            ->get();

        return Inertia::render('User/Tryout/History', [
            'attempts' => $attempts
        ]);
    }

    /**
     * Pembahasan soal.
     */
    public function review(ExamAttempt $attempt) 
    {
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        $attempt->load('tryout');
        $userAnswers = $attempt->answers ?? [];

        $questions = $attempt->tryout->questions()
            ->orderBy('order', 'asc')
            ->get()
            ->map(function ($question) use ($userAnswers) {
                $selected = $userAnswers[(string)$question->id] ?? $userAnswers[$question->id] ?? null;
                $question->user_selected_answer = $selected;
                $question->is_correct = strtolower((string)$selected) === strtolower((string)$question->correct_answer);
                $question->is_answered = !is_null($selected);
                return $question;
            });

        return Inertia::render('User/Tryout/Review', [
            'attempt' => $attempt,
            'questions' => $questions
        ]);
    }

    /**
     * Cetak Sertifikat PDF.
     */
    public function certificate(ExamAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        $attempt->load(['user', 'tryout']);

        $isPassed = $attempt->twk_score >= 65 && 
                    $attempt->tiu_score >= 80 && 
                    $attempt->tkp_score >= 166;

        $pdf = Pdf::loadView('pdf.certificate', [
            'attempt' => $attempt,
            'isPassed' => $isPassed
        ])->setPaper('a4', 'landscape');

        $filename = 'Sertifikat_' . str_replace(' ', '_', $attempt->user->name) . '.pdf';

        return $pdf->stream($filename);
    }

    public function collectiveRegister(Tryout $tryout)
    {
        if (!$tryout->is_paid) {
            return redirect()->route('tryout.show', $tryout->id);
        }

        return Inertia::render('User/Tryout/CollectiveRegister', [
            'tryout' => $tryout
        ]);
    }
}