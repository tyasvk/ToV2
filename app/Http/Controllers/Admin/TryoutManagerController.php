<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon; 
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

        // Tangkap parameter pencarian
        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Gunakan get() untuk mengambil SEMUA data tanpa terpotong pagination
        $tryouts = $query->latest()->get();

        return Inertia::render('Admin/Tryout/Index', [
            'tryouts' => $tryouts,
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                 => 'required|string|max:255',
            'duration'              => 'required|integer|min:1',
            'description'           => 'nullable|string',
            'is_paid'               => 'required|boolean',
            'price'                 => 'required_if:is_paid,true|numeric|min:0',
            'category'              => 'required|in:skd,skb', // Tambahan SKB
            'passing_grade'         => 'nullable|required_if:category,skb|integer|min:0', // Tambahan SKB
            'is_published'          => 'boolean',
            'registration_start_at' => 'nullable|date',
            'registration_end_at'   => 'nullable|date|after_or_equal:registration_start_at',
            'started_at'            => 'nullable|date', 
            'end_date'              => 'nullable|date|after_or_equal:started_at',    
        ]);

        $validated['type'] = 'general';
        $validated['is_active'] = $request->is_published; 
        
        if ($request->is_published) {
            $validated['published_at'] = Carbon::now();
        }

        Tryout::create($validated);

        return back()->with('message', 'Paket Tryout berhasil ditambahkan!');
    }

    public function update(Request $request, Tryout $tryout)
    {
        $validated = $request->validate([
            'title'                 => 'required|string|max:255',
            'duration'              => 'required|integer|min:1',
            'description'           => 'nullable|string',
            'is_paid'               => 'required|boolean',
            'price'                 => 'required_if:is_paid,true|numeric|min:0',
            'category'              => 'required|in:skd,skb', // Tambahan SKB
            'passing_grade'         => 'nullable|required_if:category,skb|integer|min:0', // Tambahan SKB
            'is_published'          => 'boolean',
            'registration_start_at' => 'nullable|date',
            'registration_end_at'   => 'nullable|date|after_or_equal:registration_start_at',
            'started_at'            => 'nullable|date', 
            'end_date'              => 'nullable|date|after_or_equal:started_at',    
        ]);
        
        // Reset passing grade jika admin mengubah dari SKB kembali ke SKD
        if ($validated['category'] === 'skd') {
            $validated['passing_grade'] = null;
        }

        $validated['is_active'] = $request->is_published;
        
        if ($request->is_published && !$tryout->published_at) {
            $validated['published_at'] = Carbon::now();
        } else if (!$request->is_published) {
            $validated['published_at'] = null;
        }

        $tryout->update($validated);

        return back()->with('message', 'Paket Tryout berhasil diperbarui!');
    }

    public function destroy(Tryout $tryout)
    {
        $tryout->delete();
        return back()->with('message', 'Paket Tryout berhasil dihapus.');
    }

    public function getDummies($id)
    {
        $dummies = \App\Models\ExamAttempt::where('tryout_id', $id)
            ->whereHas('user', function($q) {
                $q->where('email', 'like', 'bot_%'); 
            })
            ->join('users', 'exam_attempts.user_id', '=', 'users.id')
            ->select('exam_attempts.*', 'users.name', 'users.email', 'users.agency_name')
            ->orderBy('exam_attempts.total_score', 'desc')
            ->get();

        return response()->json($dummies);
    }

    public function clearDummies($id)
    {
        $userIds = \App\Models\ExamAttempt::where('tryout_id', $id)
            ->whereHas('user', function($q) {
                $q->where('email', 'like', 'bot_%');
            })->pluck('user_id');

        \App\Models\ExamAttempt::where('tryout_id', $id)->whereIn('user_id', $userIds)->delete();
        \App\Models\User::whereIn('id', $userIds)->delete();

        return back()->with('success', 'Semua peserta bot berhasil dihapus dari Tryout ini!');
    }

    public function generateDummies(\Illuminate\Http\Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|integer|min:1|max:500' 
        ]);

        $tryout = \App\Models\Tryout::findOrFail($id);
        $amount = $request->amount;
        $faker = \Faker\Factory::create('id_ID');

        $instansiPusat = [
            'Kementerian Hukum dan Hak Asasi Manusia', 'Kementerian Keuangan', 
            'Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi', 'Kejaksaan Agung RI', 
            'Badan Intelijen Negara', 'Kementerian Kesehatan', 'Kementerian Agama',
            'Kementerian Pertahanan', 'Kementerian Perhubungan', 'Badan Pusat Statistik',
            'Badan Kepegawaian Negara', 'Kementerian Luar Negeri', 'Kementerian ATR/BPN'
        ];

        $instansiDaerah = [
            'Pemerintah Provinsi Jawa Barat', 'Pemerintah Provinsi Jawa Tengah', 
            'Pemerintah Provinsi Jawa Timur', 'Pemerintah Provinsi DKI Jakarta',
            'Pemerintah Kota Surabaya', 'Pemerintah Kabupaten Bogor', 'Pemerintah Kota Medan',
            'Pemerintah Provinsi Banten', 'Pemerintah Provinsi Bali'
        ];

        $provinsiList = ['11', '12', '13', '31', '32', '33', '34', '35', '51', '73'];
        $dummyPassword = bcrypt('dummy_bot_password');

        \Illuminate\Support\Facades\DB::transaction(function () use ($amount, $faker, $tryout, $instansiPusat, $instansiDaerah, $provinsiList, $dummyPassword) {
            for ($i = 0; $i < $amount; $i++) {
                
                $isPusat = rand(1, 100) <= 75; 
                $assignedInstansi = $isPusat 
                    ? $faker->randomElement($instansiPusat) 
                    : $faker->randomElement($instansiDaerah);

                $user = \App\Models\User::create([
                    'name' => $faker->name(),
                    'email' => 'bot_' . \Illuminate\Support\Str::random(8) . '@cpnsnusantara.dummy',
                    'password' => $dummyPassword, 
                    'agency_name' => $assignedInstansi,
                    'province_code' => $faker->randomElement($provinsiList),
                    'email_verified_at' => now(),
                ]);

                // Hitung skor berdasarkan kategori (SKB vs SKD)
                if ($tryout->category === 'skb') {
                    $twk = 0; $tiu = 0; $tkp = 0;
                    $total = $faker->numberBetween(150, 450); // Estimasi skor SKB
                } else {
                    $twk = $faker->numberBetween(50, 135); 
                    $tiu = $faker->numberBetween(60, 155); 
                    $tkp = $faker->numberBetween(130, 210); 
                    $total = $twk + $tiu + $tkp;
                }

                \App\Models\ExamAttempt::create([
                    'user_id' => $user->id,
                    'tryout_id' => $tryout->id,
                    'twk_score' => $twk,
                    'tiu_score' => $tiu,
                    'tkp_score' => $tkp,
                    'total_score' => $total,
                    'answers' => [], 
                    'completed_at' => now()->subMinutes(rand(1, 10000)), 
                ]);
            }
        });

        return back()->with('success', "Berhasil menambahkan {$amount} peserta fiktif ke Tryout ini!");
    }

    public function results(Request $request, Tryout $tryout)
    {
        $search = $request->input('search');

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
            ->map(function ($userAttempts) use ($tryout) {
                return $userAttempts->sortByDesc(function ($a) use ($tryout) {
                    // Sorting berdasarkan Kategori
                    if ($tryout->category === 'skb') {
                        $isPassed = $a->total_score >= ($tryout->passing_grade ?? 0) ? 1 : 0;
                        return sprintf('%d-%05d', $isPassed, $a->total_score);
                    } else {
                        $pgTwk = ExamAttempt::PASSING_GRADE_TWK ?? 65;
                        $pgTiu = ExamAttempt::PASSING_GRADE_TIU ?? 80;
                        $pgTkp = ExamAttempt::PASSING_GRADE_TKP ?? 166;
                        $isPassed = ($a->twk_score >= $pgTwk && $a->tiu_score >= $pgTiu && $a->tkp_score >= $pgTkp) ? 1 : 0;
                        return sprintf('%d-%03d-%03d-%03d-%03d', $isPassed, $a->total_score, $a->tkp_score, $a->tiu_score, $a->twk_score);
                    }
                })->first()->id;
            })
            ->values();

        $attemptsPaginated = ExamAttempt::with('user')
            ->whereIn('id', $bestAttemptIds)
            ->get()
            ->sortByDesc(function ($a) use ($tryout) {
                if ($tryout->category === 'skb') {
                    $isPassed = $a->total_score >= ($tryout->passing_grade ?? 0) ? 1 : 0;
                    return sprintf('%d-%05d', $isPassed, $a->total_score);
                } else {
                    $pgTwk = ExamAttempt::PASSING_GRADE_TWK ?? 65;
                    $pgTiu = ExamAttempt::PASSING_GRADE_TIU ?? 80;
                    $pgTkp = ExamAttempt::PASSING_GRADE_TKP ?? 166;
                    $isPassed = ($a->twk_score >= $pgTwk && $a->tiu_score >= $pgTiu && $a->tkp_score >= $pgTkp) ? 1 : 0;
                    return sprintf('%d-%03d-%03d-%03d-%03d', $isPassed, $a->total_score, $a->tkp_score, $a->tiu_score, $a->twk_score);
                }
            })
            ->values();

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

        $results->getCollection()->transform(function ($a, $key) use ($currentPage, $perPage, $tryout) {
            $isPassed = false;
            if ($tryout->category === 'skb') {
                $isPassed = $a->total_score >= ($tryout->passing_grade ?? 0);
            } else {
                $pgTwk = ExamAttempt::PASSING_GRADE_TWK ?? 65;
                $pgTiu = ExamAttempt::PASSING_GRADE_TIU ?? 80;
                $pgTkp = ExamAttempt::PASSING_GRADE_TKP ?? 166;
                $isPassed = ($a->twk_score >= $pgTwk && $a->tiu_score >= $pgTiu && $a->tkp_score >= $pgTkp);
            }

            return [
                'id' => $a->id,
                'rank' => (($currentPage - 1) * $perPage) + $key + 1,
                'user_name' => $a->user->name ?? 'User Telah Dihapus',
                'user_email' => $a->user->email ?? '-',
                'twk_score' => $a->twk_score,
                'tiu_score' => $a->tiu_score,
                'tkp_score' => $a->tkp_score,
                'total_score' => $a->total_score,
                'is_passed' => $isPassed,
                'completed_at' => $a->completed_at ? \Carbon\Carbon::parse($a->completed_at)->format('d M Y H:i') : '-',
            ];
        });

        return inertia('Admin/Tryout/Results', [
            'tryout' => $tryout,
            'results' => $results,
            'filters' => $request->only(['search'])
        ]);
    }

    public function destroyAttempt(ExamAttempt $attempt)
    {
        $attempt->delete();
        return back()->with('success', 'Data pengerjaan peserta berhasil dihapus. Peserta sekarang dapat mengikuti ujian kembali.');
    }

    public function recalculate(Tryout $tryout)
    {
        $questions = \App\Models\Question::where('tryout_id', $tryout->id)->get()->keyBy('id');
        $attempts = \App\Models\ExamAttempt::where('tryout_id', $tryout->id)
                        ->whereNotNull('completed_at')
                        ->get();

        $updatedCount = 0;

        foreach ($attempts as $attempt) {
            $twkScore = 0;
            $tiuScore = 0;
            $tkpScore = 0;
            $skbScore = 0;

            $userAnswers = $attempt->answers;
            if (is_string($userAnswers)) {
                $userAnswers = json_decode($userAnswers, true);
            }

            if (is_array($userAnswers) || is_object($userAnswers)) {
                foreach ($userAnswers as $key => $answerData) {
                    
                    $questionId = (is_array($answerData) && isset($answerData['question_id'])) 
                                  ? $answerData['question_id'] : $key;

                    $userChoiceRaw = (is_array($answerData) && isset($answerData['answer'])) 
                                  ? $answerData['answer'] : (is_string($answerData) ? $answerData : null);
                    
                    $userChoice = $userChoiceRaw !== null ? strtoupper(trim($userChoiceRaw)) : null;

                    if (isset($questions[$questionId]) && $userChoice !== null) {
                        $question = $questions[$questionId];
                        $category = strtoupper(trim($question->category ?? $question->type ?? $question->kategori ?? '')); 
                        
                        // -- LOGIKA SKB --
                        if ($tryout->category === 'skb') {
                            $correctAnswer = strtoupper(trim($question->correct_answer));
                            if ($userChoice === $correctAnswer) {
                                $skbScore += 5;
                            }
                        } 
                        // -- LOGIKA SKOR TWK & TIU --
                        else if ($category === 'TWK' || $category === 'TIU') {
                            $correctAnswer = strtoupper(trim($question->correct_answer));
                            if ($userChoice === $correctAnswer) {
                                if ($category === 'TWK') $twkScore += 5;
                                if ($category === 'TIU') $tiuScore += 5;
                            }
                        } 
                        // -- LOGIKA SKOR TKP --
                        elseif ($category === 'TKP') {
                            $foundScore = false;

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

            // Simpan hasil sesuai kategori TO
            if ($tryout->category === 'skb') {
                $attempt->twk_score   = 0;
                $attempt->tiu_score   = 0;
                $attempt->tkp_score   = 0;
                $attempt->total_score = $skbScore;
                $attempt->is_passed   = $skbScore >= ($tryout->passing_grade ?? 0);
            } else {
                $attempt->twk_score   = $twkScore;
                $attempt->tiu_score   = $tiuScore;
                $attempt->tkp_score   = $tkpScore;
                $attempt->total_score = $twkScore + $tiuScore + $tkpScore;
                $attempt->is_passed   = ($twkScore >= 65 && $tiuScore >= 80 && $tkpScore >= 166);
            }
            
            $attempt->save();
            $updatedCount++;
        }

        return back()->with('success', "Kalkulasi berhasil! $updatedCount data peserta telah diperbarui.");
    }
}