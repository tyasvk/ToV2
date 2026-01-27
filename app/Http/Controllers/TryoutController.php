<?php

namespace App\Http\Controllers;

use App\Models\Tryout;
use App\Models\Question;
use App\Models\ExamSession;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TryoutController extends Controller
{
    /**
     * Menampilkan daftar paket tryout yang tersedia.
     */
    public function index()
    {
        $tryouts = Tryout::where('is_active', true)->get();

        return Inertia::render('Tryout/Index', [
            'tryouts' => $tryouts
        ]);
    }

    /**
     * Inisialisasi sesi ujian dan mengambil soal.
     */
    public function start(Tryout $tryout)
    {
        // Mengambil soal secara acak berdasarkan tipe (TWK, TIU, TKP)
        // Sesuai standar: 30 TWK, 35 TIU, 45 TKP (Total 110)
        $questions = Question::inRandomOrder()->get();

        $session = ExamSession::create([
            'user_id' => auth()->id(),
            'tryout_id' => $tryout->id,
            'started_at' => now(),
            'status' => 'ongoing'
        ]);

        return Inertia::render('Tryout/ExamSheet', [
            'tryout' => $tryout,
            'questions' => $questions,
            'sessionId' => $session->id,
            'duration' => $tryout->duration_minutes
        ]);
    }

    /**
     * Menyimpan jawaban dan menghitung skor akhir.
     */
public function submit(Request $request, ExamSession $session)
{
    $answers = $request->answers; // JSON dari Vue: { question_id: 'a' }
    $scoreTwk = 0; $scoreTiu = 0; $scoreTkp = 0;

    foreach ($answers as $qId => $ans) {
        $question = Question::find($qId);
        if (!$question) continue;

        if ($question->type === 'TKP') {
            // TKP: Ambil poin dari JSON tkp_scores misal {"a":5, "b":4...}
            $scores = json_decode($question->tkp_scores, true);
            $scoreTkp += $scores[$ans] ?? 0;
        } else {
            // TWK & TIU: Benar 5
            if ($question->correct_answer === $ans) {
                ($question->type === 'TWK') ? $scoreTwk += 5 : $scoreTiu += 5;
            }
        }
    }

    $session->update([
        'score_twk' => $scoreTwk,
        'score_tiu' => $scoreTiu,
        'score_tkp' => $scoreTkp,
        'score_total' => $scoreTwk + $scoreTiu + $scoreTkp,
        'status' => 'completed',
        'ended_at' => now()
    ]);

    return redirect()->route('tryout.results', $session->id);
}

    /**
     * Menampilkan hasil skor ujian.
     */
    public function results(ExamSession $session)
    {
        // Pastikan hanya pemilik sesi yang bisa melihat hasil
        if ($session->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Tryout/Results', [
            'session' => $session->load('tryout')
        ]);
    }
}