<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon; // Pastikan import Carbon untuk waktu
use App\Models\ExamAttempt;

class TryoutManagerController extends Controller
{
    public function index(Request $request)
    {
        $query = Tryout::query()->withCount('questions');

        // Filter tipe (bukan adidaya/akbar)
        $query->where(function ($q) {
            $q->whereNotIn('type', ['adidaya', 'akbar'])
              ->orWhereNull('type');
        });

        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $tryouts = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Tryout/Index', [
            'tryouts' => $tryouts,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_paid' => 'required|boolean',
            'price' => 'required_if:is_paid,true|numeric|min:0',
            'is_published' => 'boolean',
            'started_at' => 'nullable|date', // <--- WAJIB DITAMBAHKAN
            'end_date' => 'nullable|date',     
        ]);

        $validated['type'] = 'general'; // Default tipe
        
        // WAJIB: Samakan is_active dengan is_published
        $validated['is_active'] = $request->is_published; 
        
        // WAJIB: Isi published_at jika dipublish agar lolos filter tanggal
        if ($request->is_published) {
            $validated['published_at'] = Carbon::now();
        }

        Tryout::create($validated);

        return back()->with('message', 'Paket Tryout berhasil ditambahkan!');
    }

    public function update(Request $request, Tryout $tryout)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_paid' => 'required|boolean',
            'price' => 'required_if:is_paid,true|numeric|min:0',
            'is_published' => 'boolean',
            'started_at' => 'nullable|date', // <--- WAJIB DITAMBAHKAN
            'end_date' => 'nullable|date',     
        ]);
        
        // WAJIB: Samakan is_active dengan is_published
        $validated['is_active'] = $request->is_published;
        
        // Cek jika statusnya berubah dari false ke true, set published_at
        if ($request->is_published && !$tryout->published_at) {
            $validated['published_at'] = Carbon::now();
        } else if (!$request->is_published) {
            $validated['published_at'] = null; // Tarik kembali (unpublish)
        }

        $tryout->update($validated);

        return back()->with('message', 'Paket Tryout berhasil diperbarui!');
    }

    public function destroy(Tryout $tryout)
    {
        $tryout->delete();
        return back()->with('message', 'Paket Tryout berhasil dihapus.');
    }

    /**
     * Menampilkan hasil pengerjaan (leaderboard) untuk suatu tryout dengan Pencarian & Paginasi
     */
    public function results(Request $request, Tryout $tryout)
    {
        $pgTwk = ExamAttempt::PASSING_GRADE_TWK ?? 65;
        $pgTiu = ExamAttempt::PASSING_GRADE_TIU ?? 80;
        $pgTkp = ExamAttempt::PASSING_GRADE_TKP ?? 166;

        $search = $request->input('search');

        // Subquery untuk mengambil ID attempt TERBAIK dari masing-masing user_id pada tryout ini
        // Menggunakan logika pengurutan SKOR TERBAIK (Lulus -> Total -> TKP -> TIU -> TWK)
        $bestAttemptIds = ExamAttempt::where('tryout_id', $tryout->id)
            ->whereNotNull('completed_at')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->get()
            ->groupBy('user_id')
            ->map(function ($userAttempts) use ($pgTwk, $pgTiu, $pgTkp) {
                return $userAttempts->sortByDesc(function ($a) use ($pgTwk, $pgTiu, $pgTkp) {
                    $isPassed = ($a->twk_score >= $pgTwk && $a->tiu_score >= $pgTiu && $a->tkp_score >= $pgTkp) ? 1 : 0;
                    return sprintf('%d-%03d-%03d-%03d-%03d', $isPassed, $a->total_score, $a->tkp_score, $a->tiu_score, $a->twk_score);
                })->first()->id;
            })
            ->values();

        // Ambil data attempt murni menggunakan ID terbaik yang sudah didapatkan + Paginasi 10 data per halaman
        $attemptsPaginated = ExamAttempt::with('user')
            ->whereIn('id', $bestAttemptIds)
            ->get()
            ->sortByDesc(function ($a) use ($pgTwk, $pgTiu, $pgTkp) {
                $isPassed = ($a->twk_score >= $pgTwk && $a->tiu_score >= $pgTiu && $a->tkp_score >= $pgTkp) ? 1 : 0;
                return sprintf('%d-%03d-%03d-%03d-%03d', $isPassed, $a->total_score, $a->tkp_score, $a->tiu_score, $a->twk_score);
            })
            ->values();

        // Buat Paginasi Manual dari Collection agar nomor ranking tetap berurutan dan presisi
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentItems = $attemptsPaginated->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $results = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $attemptsPaginated->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Transformasi struktur data ke format bersih untuk Vue
        $results->getCollection()->transform(function ($a, $key) use ($currentPage, $perPage, $pgTwk, $pgTiu, $pgTkp) {
            return [
                'id' => $a->id,
                'rank' => (($currentPage - 1) * $perPage) + $key + 1,
                'user_name' => $a->user->name ?? 'User Telah Dihapus',
                'user_email' => $a->user->email ?? '-',
                'twk_score' => $a->twk_score,
                'tiu_score' => $a->tiu_score,
                'tkp_score' => $a->tkp_score,
                'total_score' => $a->total_score,
                'is_passed' => ($a->twk_score >= $pgTwk && $a->tiu_score >= $pgTiu && $a->tkp_score >= $pgTkp),
                'completed_at' => $a->completed_at ? \Carbon\Carbon::parse($a->completed_at)->format('d M Y H:i') : '-',
            ];
        });

        return inertia('Admin/Tryout/Results', [
            'tryout' => $tryout,
            'results' => $results,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Fitur Baru: Menghapus data pengerjaan peserta agar bisa mengulang ujian kembali
     */
    public function destroyAttempt(ExamAttempt $attempt)
    {
        $tryoutId = $attempt->tryout_id;
        
        // Hapus data attempt dari database
        $attempt->delete();

        return back()->with('success', 'Data pengerjaan peserta berhasil dihapus. Peserta sekarang dapat mengikuti ujian kembali.');
    }

    public function recalculate(Tryout $tryout)
    {
        // 1. Ambil semua soal dari tryout ini
        $questions = \App\Models\Question::where('tryout_id', $tryout->id)->get()->keyBy('id');

        // 2. Ambil attempt (pengerjaan) yang sudah selesai
        $attempts = \App\Models\ExamAttempt::where('tryout_id', $tryout->id)
                        ->whereNotNull('completed_at')
                        ->get();

        $updatedCount = 0;

        foreach ($attempts as $attempt) {
            $twkScore = 0;
            $tiuScore = 0;
            $tkpScore = 0;

            // Pastikan format JSON terbaca
            $userAnswers = $attempt->answers;
            if (is_string($userAnswers)) {
                $userAnswers = json_decode($userAnswers, true);
            }

            if (is_array($userAnswers) || is_object($userAnswers)) {
                foreach ($userAnswers as $key => $answerData) {
                    
                    $questionId = (is_array($answerData) && isset($answerData['question_id'])) 
                                  ? $answerData['question_id'] 
                                  : $key;

                    $userChoiceRaw = (is_array($answerData) && isset($answerData['answer'])) 
                                  ? $answerData['answer'] 
                                  : (is_string($answerData) ? $answerData : null);
                    
                    $userChoice = $userChoiceRaw !== null ? strtoupper(trim($userChoiceRaw)) : null;

                    if (isset($questions[$questionId]) && $userChoice !== null) {
                        $question = $questions[$questionId];
                        
                        // Menangkap berbagai kemungkinan nama kolom kategori (category/type/kategori)
                        $category = strtoupper(trim($question->category ?? $question->type ?? $question->kategori ?? '')); 
                        
                        // -- LOGIKA SKOR TWK & TIU --
                        if ($category === 'TWK' || $category === 'TIU') {
                            $correctAnswer = strtoupper(trim($question->correct_answer));
                            if ($userChoice === $correctAnswer) {
                                if ($category === 'TWK') $twkScore += 5;
                                if ($category === 'TIU') $tiuScore += 5;
                            }
                        } 
                        // -- LOGIKA SKOR TKP (SUDAH DIPERBAIKI) --
                        elseif ($category === 'TKP') {
                            $foundScore = false;

                            // 1. Cek dari kolom `tkp_scores` (Sesuai dengan standard Controller Ujian)
                            if (!empty($question->tkp_scores)) {
                                $tkpScoresMap = is_string($question->tkp_scores) ? json_decode($question->tkp_scores, true) : $question->tkp_scores;
                                if (is_array($tkpScoresMap)) {
                                    if (isset($tkpScoresMap[$userChoice])) {
                                        $tkpScore += (int) $tkpScoresMap[$userChoice];
                                        $foundScore = true;
                                    } elseif (isset($tkpScoresMap[strtolower($userChoice)])) {
                                        $tkpScore += (int) $tkpScoresMap[strtolower($userChoice)];
                                        $foundScore = true;
                                    }
                                }
                            }

                            // 2. Fallback: Cek properti `points`
                            if (!$foundScore && !empty($question->points)) {
                                $pointsMap = is_string($question->points) ? json_decode($question->points, true) : $question->points;
                                if (is_array($pointsMap)) {
                                    if (isset($pointsMap[$userChoice])) {
                                        $tkpScore += (int) $pointsMap[$userChoice];
                                        $foundScore = true;
                                    } elseif (isset($pointsMap[strtolower($userChoice)])) {
                                        $tkpScore += (int) $pointsMap[strtolower($userChoice)];
                                        $foundScore = true;
                                    }
                                }
                            }
                            
                            // 3. Fallback: Mengecek penamaan kolom nilai terpisah di database (point_a, dll)
                            if (!$foundScore) {
                                $possibleColumns = [
                                    'point_' . strtolower($userChoice),
                                    'poin_' . strtolower($userChoice), 
                                    'score_' . strtolower($userChoice), 
                                    'skor_' . strtolower($userChoice),  
                                    strtolower($userChoice),            
                                ];

                                foreach ($possibleColumns as $col) {
                                    if (isset($question->$col)) {
                                        $tkpScore += (int) $question->$col;
                                        break; 
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // 3. BYPASS $FILLABLE DENGAN DIRECT ASSIGNMENT
            // Cara ini menjamin bahwa angka baru PASTI dimasukkan ke dalam database meskipun ada restriksi model
            $attempt->twk_score   = $twkScore;
            $attempt->tiu_score   = $tiuScore;
            $attempt->tkp_score   = $tkpScore;
            $attempt->total_score = $twkScore + $tiuScore + $tkpScore;
            $attempt->is_passed   = ($twkScore >= 65 && $tiuScore >= 80 && $tkpScore >= 166);
            
            // Simpan paksa ke database
            $attempt->save();

            $updatedCount++;
        }

        return back()->with('success', "Kalkulasi berhasil! $updatedCount data peserta telah diperbarui.");
    }
}