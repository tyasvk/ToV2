<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use App\Models\Question;
use App\Models\ExamAttempt; // PENTING: Import model hasil ujian
use Illuminate\Http\Request; // PENTING: Import class Request
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class TryoutController extends Controller
{
public function index()
{
    $tryouts = Tryout::withCount('questions')->get();
    
    // Ambil semua attempt user yang sudah selesai
    $attempts = auth()->user()->examAttempts()->whereNotNull('completed_at')->get();

    return Inertia::render('User/Tryout/Index', [
        'tryouts' => $tryouts->map(function($t) use ($attempts) {
            // Cari skor terakhir untuk tryout ini
            $lastAttempt = $attempts->where('tryout_id', $t->id)->first();
            $t->last_score = $lastAttempt ? $lastAttempt->total_score : null;
            return $t;
        }),
        'stats' => [
            'total_completed' => $attempts->count(),
            'average_score' => round($attempts->avg('total_score') ?? 0),
        ]
    ]);
}

public function show(Tryout $tryout)
{
    // Jika sampai di sini, berarti data ID ditemukan
    return Inertia::render('User/Tryout/Show', [
        'tryout' => $tryout->loadCount('questions')
    ]);
}

public function exam(Tryout $tryout)
{
    // Mengambil soal hanya saat ujian benar-benar dimulai
    $questions = $tryout->questions()->orderBy('order', 'asc')->get();

    return Inertia::render('Tryout/ExamSheet', [
        'tryout' => $tryout,
        'questions' => $questions
    ]);
}

   public function finish(Request $request, Tryout $tryout)
{
    $userAnswers = $request->answers; 
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

    // SIMPAN HASIL DENGAN JAWABANNYA
    $attempt = ExamAttempt::create([
        'user_id' => auth()->id(),
        'tryout_id' => $tryout->id,
        'answers' => $userAnswers, // <--- SANGAT PENTING: Simpan jawaban di sini
        'twk_score' => $twk,
        'tiu_score' => $tiu,
        'tkp_score' => $tkp,
        'total_score' => $twk + $tiu + $tkp,
        'completed_at' => now(),
    ]);

    return redirect()->route('tryout.result', $attempt->id);
}

    public function result(ExamAttempt $attempt)
    {
        // Pastikan hanya pemilik hasil yang bisa melihat
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('User/Tryout/Result', [
            'attempt' => $attempt->load('tryout')
        ]);
    }

public function examBkn(Tryout $tryout)
{
    $questions = $tryout->questions()->orderBy('order', 'asc')->get();

    // PERBAIKAN: Render ke 'Tryout/ExamSheetBKN' (bukan User/Tryout/...)
    return Inertia::render('Tryout/ExamSheetBKN', [
        'tryout' => $tryout,
        'questions' => $questions,
        'user' => auth()->user()
    ]);
}

public function certificate(ExamAttempt $attempt) // Gunakan ExamAttempt, variabel tetap $attempt sesuai route
{
    // Pastikan user hanya bisa akses milik sendiri
    if ($attempt->user_id !== auth()->id()) {
        abort(403);
    }

    // Load relasi agar data tryout dan user muncul di PDF
    $attempt->load(['user', 'tryout']);

    // Ambang Batas SKD
    $isPassed = $attempt->twk_score >= 65 && 
                $attempt->tiu_score >= 80 && 
                $attempt->tkp_score >= 166;

    // Load view sertifikat
    $pdf = Pdf::loadView('pdf.certificate', [
        'attempt' => $attempt,
        'isPassed' => $isPassed
    ])->setPaper('a4', 'landscape');

    $filename = 'Sertifikat_' . str_replace(' ', '_', $attempt->user->name) . '.pdf';

    return $pdf->stream($filename);
}
public function leaderboard(Tryout $tryout)
{
    // Gunakan ExamAttempt, bukan Attempt
    $rankings = ExamAttempt::where('tryout_id', $tryout->id)
        ->with('user')
        ->orderBy('total_score', 'desc')
        ->get();

    return Inertia::render('User/Tryout/Leaderboard', [
        'tryout' => $tryout,
        'rankings' => $rankings
    ]);
}

// Tambahkan method ini di dalam class TryoutController
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

public function review(ExamAttempt $attempt) 
{
    $attempt->load('tryout');
    
    // Ambil jawaban user dari kolom JSON yang sudah jadi array
    $userAnswers = $attempt->answers ?? [];

    $questions = $attempt->tryout->questions()
        ->orderBy('order', 'asc')
        ->get()
        ->map(function ($question) use ($userAnswers) {
            // Kita cari jawaban user berdasarkan ID soal
            // Gunakan (string) pada ID untuk jaga-jaga jika JSON key-nya berupa string
            $selected = $userAnswers[(string)$question->id] ?? $userAnswers[$question->id] ?? null;
            
            $question->user_selected_answer = $selected;
            
            // Logika pengecekan (Abaikan besar/kecil huruf)
            $question->is_correct = strtolower((string)$selected) === strtolower((string)$question->correct_answer);
            $question->is_answered = !is_null($selected);
            
            return $question;
        });

    return Inertia::render('User/Tryout/Review', [
        'attempt' => $attempt,
        'questions' => $questions
    ]);
}

}