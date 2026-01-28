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
// Tambahkan use model di paling atas
use App\Models\Transaction; 
use Illuminate\Support\Str;

class TryoutController extends Controller
{
    /**
     * Menampilkan daftar tryout yang tersedia (Katalog).
     */
    public function index()
    {
        // PERBAIKAN UTAMA:
        // 1. Filter hanya yang 'is_published' = true
        // 2. Tampilkan jika tanggal publish sudah lewat ATAU belum diset (null)
        $tryouts = Tryout::where('is_published', true)
            ->where(function ($query) {
                $query->where('published_at', '<=', now())
                      ->orWhereNull('published_at');
            })
            ->withCount('questions')
            ->latest()
            ->get();

        return Inertia::render('User/Tryout/Index', [
            'tryouts' => $tryouts
        ]);
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
     * Memproses Pendaftaran & Simpan Transaksi
     */
    public function processRegistration(Request $request, Tryout $tryout)
    {
        // 1. Validasi Input
        $request->validate([
            'emails' => 'array|max:5', // Maksimal 5 anggota tambahan
            'emails.*' => 'required|email|exists:users,email|distinct', // Email harus terdaftar & tidak boleh kembar
        ]);

        // 2. Susun Data Peserta (Ketua + Anggota)
        // Email ketua (user login) otomatis dimasukkan paling awal
        $participants = collect([auth()->user()->email]);
        
        if ($request->emails && count($request->emails) > 0) {
            $participants = $participants->merge($request->emails);
        }

        // Cek duplikasi (jika ketua memasukkan emailnya sendiri di form anggota)
        $participants = $participants->unique()->values();
        $qty = $participants->count();

        // 3. Hitung Diskon (Logika Sama dengan Frontend)
        $price = $tryout->price;
        $discount = 0;
        
        if ($qty === 2) $discount = 0.05;
        elseif ($qty === 3) $discount = 0.10;
        elseif ($qty === 4) $discount = 0.15;
        elseif ($qty >= 5) $discount = 0.20;

        $totalNormal = $price * $qty;
        $totalDiscount = $totalNormal * $discount;
        $finalAmount = $totalNormal - $totalDiscount;

        // 4. Simpan ke Database (Tabel Transactions)
        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'tryout_id' => $tryout->id,
            'invoice_code' => 'INV-' . strtoupper(Str::random(10)),
            'unit_price' => $price,
            'qty' => $qty,
            'amount' => $finalAmount,
            'participants_data' => $participants->all(), // Simpan array email
            'status' => 'pending',
        ]);

        // 5. Redirect ke Halaman Pembayaran (Atau Index Dulu)
        // Nanti kita buat halaman detail transaksi. Untuk sekarang balik ke katalog dulu.
        return redirect()->route('tryout.index')->with('success', 'Tagihan berhasil dibuat! Silakan lakukan pembayaran.');
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
        // Cek apakah jadwal ujian sudah dimulai
        if ($tryout->started_at && now() < $tryout->started_at) {
            return redirect()->route('tryout.index')->with('error', 'Ujian belum dimulai.');
        }

        // Ambil soal urut berdasarkan 'order' atau acak jika perlu
        $questions = $tryout->questions()->orderBy('order', 'asc')->get();

        return Inertia::render('Tryout/ExamSheet', [
            'tryout' => $tryout,
            'questions' => $questions
        ]);
    }

    /**
     * Simulasi tampilan BKN (Opsional).
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
     * Menyimpan jawaban user dan menghitung skor (Selesai Ujian).
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

            // Logika Penilaian SKD CPNS
            if ($q->type === 'TKP') {
                // TKP: Nilai 1-5, tidak ada jawaban salah (0 jika tidak dijawab)
                $tkp += (int) ($q->tkp_scores[$answer] ?? 0);
            } else {
                // TIU & TWK: Benar = 5, Salah = 0
                if ($answer === $q->correct_answer) {
                    if ($q->type === 'TWK') $twk += 5;
                    if ($q->type === 'TIU') $tiu += 5;
                }
            }
        }

        // Simpan Hasil ke Database
        $attempt = ExamAttempt::create([
            'user_id' => auth()->id(),
            'tryout_id' => $tryout->id,
            'answers' => $userAnswers, // Simpan jawaban mentah (JSON)
            'twk_score' => $twk,
            'tiu_score' => $tiu,
            'tkp_score' => $tkp,
            'total_score' => $twk + $tiu + $tkp,
            'completed_at' => now(),
        ]);

        return redirect()->route('tryout.result', $attempt->id);
    }

    /**
     * Menampilkan halaman hasil ujian (Score Card).
     */
    public function result(ExamAttempt $attempt)
    {
        // Keamanan: Pastikan user hanya melihat hasil miliknya sendiri
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
     * Pembahasan soal (Review jawaban).
     */
    public function review(ExamAttempt $attempt) 
    {
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        $attempt->load('tryout');
        $userAnswers = $attempt->answers ?? [];

        // Gabungkan data soal dengan jawaban user
        $questions = $attempt->tryout->questions()
            ->orderBy('order', 'asc')
            ->get()
            ->map(function ($question) use ($userAnswers) {
                // Ambil jawaban user untuk soal ini
                $selected = $userAnswers[(string)$question->id] ?? $userAnswers[$question->id] ?? null;
                
                $question->user_selected_answer = $selected;
                
                // Cek kebenaran (Case insensitive)
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
     * Menampilkan peringkat peserta lain.
     */
    public function leaderboard(Tryout $tryout)
    {
        $rankings = ExamAttempt::where('tryout_id', $tryout->id)
            ->with('user')
            ->orderBy('total_score', 'desc')
            ->limit(50) // Batasi 50 besar agar ringan
            ->get();

        return Inertia::render('User/Tryout/Leaderboard', [
            'tryout' => $tryout,
            'rankings' => $rankings
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

        // Logika Lulus SKD (Passing Grade 2024/2025)
        // TWK: 65, TIU: 80, TKP: 166
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
    // Hanya paket berbayar yang bisa kolektif
    if (!$tryout->is_paid) {
        return redirect()->route('tryout.show', $tryout->id);
    }

    return Inertia::render('User/Tryout/CollectiveRegister', [
        'tryout' => $tryout
    ]);
}
}