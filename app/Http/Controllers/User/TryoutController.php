<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use App\Models\Question;
use App\Models\ExamAttempt; // PENTING: Import model hasil ujian
use Illuminate\Http\Request; // PENTING: Import class Request
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class TryoutController extends Controller
{
public function index()
{
    $tryouts = Tryout::where('is_active', true)
        ->where('published_at', '<=', now())
        ->withCount('questions')
        ->latest()
        ->get();

    return Inertia::render('User/Tryout/Index', [
        'tryouts' => $tryouts
    ]);
}

    public function show(Tryout $tryout)
    {
        $questions = $tryout->questions()->orderBy('order', 'asc')->get();

        return Inertia::render('User/Tryout/Show', [
            'tryout' => $tryout,
            'questions' => $questions
        ]);
    }

    public function finish(Request $request, Tryout $tryout)
    {
        $userAnswers = $request->answers; // Ambil jawaban dari Vue [question_id => 'a']
        $questions = $tryout->questions;
        
        $twk = 0; $tiu = 0; $tkp = 0;

        foreach ($questions as $q) {
            $answer = $userAnswers[$q->id] ?? null;

            if ($q->type === 'TKP') {
                // Ambil bobot dari JSON tkp_scores (misal: 'a' => 5)
                $tkp += (int) ($q->tkp_scores[$answer] ?? 0);
            } else {
                // TWK & TIU: Benar +5, Salah 0
                if ($answer === $q->correct_answer) {
                    if ($q->type === 'TWK') $twk += 5;
                    if ($q->type === 'TIU') $tiu += 5;
                }
            }
        }

        // Simpan hasil ke database
        $attempt = ExamAttempt::create([
            'user_id' => auth()->id(),
            'tryout_id' => $tryout->id,
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
}