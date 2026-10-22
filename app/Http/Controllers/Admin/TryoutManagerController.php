<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;

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

        // Filter berdasarkan kategori (opsional: skd / skb)
        if ($request->filled('category') && in_array($request->category, ['skd', 'skb'])) {
            if ($request->category === 'skd') {
                $query->where(function ($q) {
                    $q->where('category', 'skd')->orWhereNull('category');
                });
            } else {
                $query->where('category', 'skb');
            }
        }

        // Tangkap parameter pencarian
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Gunakan get() untuk mengambil SEMUA data tanpa terpotong pagination
        $tryouts = $query->latest()->get()->map(function ($tryout) {
            // Pastikan data lama yang category-nya masih null terbaca sebagai 'skd' di Frontend Vue
            if (empty($tryout->category)) {
                $tryout->category = 'skd';
            }
            return $tryout;
        });

        return Inertia::render('Admin/Tryout/Index', [
            'tryouts' => $tryouts,
            'filters' => [
                'search'   => $request->search,
                'category' => $request->category,
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
            'price'                 => 'nullable|required_if:is_paid,true|numeric|min:0',
            'category'              => 'required|in:skd,skb',
            'passing_grade'         => 'nullable|required_if:category,skb|integer|min:0',
            'is_published'          => 'boolean',
            'registration_start_at' => 'nullable|date',
            'registration_end_at'   => 'nullable|date|after_or_equal:registration_start_at',
            'started_at'            => 'nullable|date',
            'end_date'              => 'nullable|date|after_or_equal:started_at',
        ]);

        $validated['type']             = 'general';
        $validated['duration_minutes'] = $validated['duration']; // Sinkronisasi kolom durasi
        $validated['price']            = $request->boolean('is_paid') ? ($validated['price'] ?? 0) : 0;
        $validated['is_published']     = $request->boolean('is_published');
        $validated['is_active']        = $validated['is_published'];

        if ($validated['category'] === 'skd') {
            $validated['passing_grade'] = null;
        } else {
            $validated['passing_grade'] = $validated['passing_grade'] ?? 0;
        }

        if ($validated['is_published']) {
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
            'price'                 => 'nullable|required_if:is_paid,true|numeric|min:0',
            'category'              => 'required|in:skd,skb',
            'passing_grade'         => 'nullable|required_if:category,skb|integer|min:0',
            'is_published'          => 'boolean',
            'registration_start_at' => 'nullable|date',
            'registration_end_at'   => 'nullable|date|after_or_equal:registration_start_at',
            'started_at'            => 'nullable|date',
            'end_date'              => 'nullable|date|after_or_equal:started_at',
        ]);

        $validated['duration_minutes'] = $validated['duration']; // Sinkronisasi kolom durasi
        $validated['price']            = $request->boolean('is_paid') ? ($validated['price'] ?? 0) : 0;
        $validated['is_published']     = $request->boolean('is_published');
        $validated['is_active']        = $validated['is_published'];

        // Reset passing grade jika admin mengubah dari SKB kembali ke SKD
        if ($validated['category'] === 'skd') {
            $validated['passing_grade'] = null;
        } else {
            $validated['passing_grade'] = $validated['passing_grade'] ?? 0;
        }

        if ($validated['is_published'] && !$tryout->published_at) {
            $validated['published_at'] = Carbon::now();
        } elseif (!$validated['is_published']) {
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
        $dummies = ExamAttempt::where('tryout_id', $id)
            ->whereHas('user', function ($q) {
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
        $userIds = ExamAttempt::where('tryout_id', $id)
            ->whereHas('user', function ($q) {
                $q->where('email', 'like', 'bot_%');
            })->pluck('user_id');

        ExamAttempt::where('tryout_id', $id)->whereIn('user_id', $userIds)->delete();
        User::whereIn('id', $userIds)->delete();

        return back()->with('success', 'Semua peserta bot berhasil dihapus dari Tryout ini!');
    }

    public function generateDummies(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|integer|min:1|max:500'
        ]);

        $tryout = Tryout::withCount('questions')->findOrFail($id);
        $amount = $request->amount;
        $faker  = \Faker\Factory::create('id_ID');

        $instansiPusat = [
            'Kementerian Hukum dan Hak Asasi Manusia', 'Kementerian Keuangan',
            'Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi', 'Kejaksaan Agung RI',
            'Badan Intelijen Negara', 'Kementerian Kesehatan', 'Kementerian Agama',
            'Kementerian Pertahanan', 'Kementerian Perhubungan', 'Badan Pusat Statistik',
            'Badan Kepegawaian Negara', 'Kementerian Luar Negeri', 'Kementerian ATR/BPN',
            'Kementerian Imigrasi dan Pemasyarakatan'
        ];

        $instansiDaerah = [
            'Pemerintah Provinsi Jawa Barat', 'Pemerintah Provinsi Jawa Tengah',
            'Pemerintah Provinsi Jawa Timur', 'Pemerintah Provinsi DKI Jakarta',
            'Pemerintah Kota Surabaya', 'Pemerintah Kabupaten Bogor', 'Pemerintah Kota Medan',
            'Pemerintah Provinsi Banten', 'Pemerintah Provinsi Bali', 'Pemerintah Provinsi Sumatera Selatan'
        ];

        $provinsiList  = ['11', '12', '13', '16', '31', '32', '33', '34', '35', '51', '73'];
        $dummyPassword = bcrypt('dummy_bot_password');

        $pgTwk = ExamAttempt::PASSING_GRADE_TWK ?? 65;
        $pgTiu = ExamAttempt::PASSING_GRADE_TIU ?? 80;
        $pgTkp = ExamAttempt::PASSING_GRADE_TKP ?? 166;

        DB::transaction(function () use ($amount, $faker, $tryout, $instansiPusat, $instansiDaerah, $provinsiList, $dummyPassword, $pgTwk, $pgTiu, $pgTkp) {
            $maxQuestions = $tryout->questions_count > 0 ? $tryout->questions_count : 100;

            for ($i = 0; $i < $amount; $i++) {
                $isPusat = rand(1, 100) <= 75;
                $assignedInstansi = $isPusat
                    ? $faker->randomElement($instansiPusat)
                    : $faker->randomElement($instansiDaerah);

                $user = User::create([
                    'name'              => $faker->name(),
                    'email'             => 'bot_' . Str::random(8) . '@cpnsnusantara.dummy',
                    'password'          => $dummyPassword,
                    'agency_name'       => $assignedInstansi,
                    'province_code'     => $faker->randomElement($provinsiList),
                    'email_verified_at' => now(),
                ]);

                // Hitung skor berdasarkan kategori (SKB vs SKD)
                if ($tryout->category === 'skb') {
                    $twk = 0;
                    $tiu = 0;
                    $tkp = 0;

                    // Karena tiap soal SKB berbobot 5 poin, skor harus kelipatan 5 murni
                    $minCorrect = max(10, (int) floor($maxQuestions * 0.30));
                    $maxCorrect = max($minCorrect + 5, (int) floor($maxQuestions * 0.90));
                    $correctCount = $faker->numberBetween($minCorrect, $maxCorrect);

                    $total    = $correctCount * 5;
                    $isPassed = $total >= ($tryout->passing_grade ?? 0);
                } else {
                    // Skor TWK & TIU kelipatan 5
                    $twk      = $faker->numberBetween(10, 27) * 5; // 50 - 135
                    $tiu      = $faker->numberBetween(12, 31) * 5; // 60 - 155
                    $tkp      = $faker->numberBetween(130, 210);
                    $total    = $twk + $tiu + $tkp;
                    $isPassed = ($twk >= $pgTwk && $tiu >= $pgTiu && $tkp >= $pgTkp);
                }

                ExamAttempt::create([
                    'user_id'      => $user->id,
                    'tryout_id'    => $tryout->id,
                    'twk_score'    => $twk,
                    'tiu_score'    => $tiu,
                    'tkp_score'    => $tkp,
                    'total_score'  => $total,
                    'is_passed'    => $isPassed,
                    'answers'      => [],
                    'completed_at' => now()->subMinutes(rand(1, 10000)),
                ]);
            }
        });

        return back()->with('success', "Berhasil menambahkan {$amount} peserta fiktif ke Tryout ini!");
    }

    public function results(Request $request, Tryout $tryout)
    {
        $search = $request->input('search');

        $pgTwk = ExamAttempt::PASSING_GRADE_TWK ?? 65;
        $pgTiu = ExamAttempt::PASSING_GRADE_TIU ?? 80;
        $pgTkp = ExamAttempt::PASSING_GRADE_TKP ?? 166;

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
            ->map(function ($userAttempts) use ($tryout, $pgTwk, $pgTiu, $pgTkp) {
                return $userAttempts->sortByDesc(function ($a) use ($tryout, $pgTwk, $pgTiu, $pgTkp) {
                    if ($tryout->category === 'skb') {
                        $isPassed = $a->total_score >= ($tryout->passing_grade ?? 0) ? 1 : 0;
                        return sprintf('%d-%05d', $isPassed, $a->total_score);
                    } else {
                        $isPassed = ($a->twk_score >= $pgTwk && $a->tiu_score >= $pgTiu && $a->tkp_score >= $pgTkp) ? 1 : 0;
                        return sprintf('%d-%03d-%03d-%03d-%03d', $isPassed, $a->total_score, $a->tkp_score, $a->tiu_score, $a->twk_score);
                    }
                })->first()->id;
            })
            ->values();

        $attemptsPaginated = ExamAttempt::with('user')
            ->whereIn('id', $bestAttemptIds)
            ->get()
            ->sortByDesc(function ($a) use ($tryout, $pgTwk, $pgTiu, $pgTkp) {
                if ($tryout->category === 'skb') {
                    $isPassed = $a->total_score >= ($tryout->passing_grade ?? 0) ? 1 : 0;
                    return sprintf('%d-%05d', $isPassed, $a->total_score);
                } else {
                    $isPassed = ($a->twk_score >= $pgTwk && $a->tiu_score >= $pgTiu && $a->tkp_score >= $pgTkp) ? 1 : 0;
                    return sprintf('%d-%03d-%03d-%03d-%03d', $isPassed, $a->total_score, $a->tkp_score, $a->tiu_score, $a->twk_score);
                }
            })
            ->values();

        $currentPage  = LengthAwarePaginator::resolveCurrentPage();
        $perPage      = 10;
        $currentItems = $attemptsPaginated->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $results = new LengthAwarePaginator(
            $currentItems,
            $attemptsPaginated->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $results->getCollection()->transform(function ($a, $key) use ($currentPage, $perPage, $tryout, $pgTwk, $pgTiu, $pgTkp) {
            if ($tryout->category === 'skb') {
                $isPassed = $a->total_score >= ($tryout->passing_grade ?? 0);
            } else {
                $isPassed = ($a->twk_score >= $pgTwk && $a->tiu_score >= $pgTiu && $a->tkp_score >= $pgTkp);
            }

            return [
                'id'           => $a->id,
                'rank'         => (($currentPage - 1) * $perPage) + $key + 1,
                'user_name'    => $a->user->name ?? 'User Telah Dihapus',
                'user_email'   => $a->user->email ?? '-',
                'twk_score'    => $a->twk_score,
                'tiu_score'    => $a->tiu_score,
                'tkp_score'    => $a->tkp_score,
                'total_score'  => $a->total_score,
                'is_passed'    => $isPassed,
                'completed_at' => $a->completed_at ? Carbon::parse($a->completed_at)->format('d M Y H:i') : '-',
            ];
        });

        return Inertia::render('Admin/Tryout/Results', [
            'tryout'  => $tryout,
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
        $questions = Question::where('tryout_id', $tryout->id)->get()->keyBy('id');
        $attempts  = ExamAttempt::where('tryout_id', $tryout->id)
                        ->whereNotNull('completed_at')
                        ->get();

        $updatedCount = 0;

        $pgTwk = ExamAttempt::PASSING_GRADE_TWK ?? 65;
        $pgTiu = ExamAttempt::PASSING_GRADE_TIU ?? 80;
        $pgTkp = ExamAttempt::PASSING_GRADE_TKP ?? 166;

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
                        ? $answerData['question_id']
                        : $key;

                    $userChoiceRaw = (is_array($answerData) && isset($answerData['answer']))
                        ? $answerData['answer']
                        : (is_scalar($answerData) ? (string) $answerData : null);

                    $userChoice = $userChoiceRaw !== null ? strtoupper(trim($userChoiceRaw)) : null;

                    if (isset($questions[$questionId]) && $userChoice !== null && $userChoice !== '') {
                        $question = $questions[$questionId];
                        $category = strtoupper(trim($question->category ?? $question->type ?? $question->kategori ?? ''));

                        // -- 1. LOGIKA SKB (Setiap Soal Benar = 5 Poin) --
                        if ($tryout->category === 'skb') {
                            $correctAnswer = strtoupper(trim((string) $question->correct_answer));
                            if ($userChoice === $correctAnswer) {
                                $skbScore += 5;
                            }
                        }
                        // -- 2. LOGIKA SKD: TWK & TIU --
                        elseif ($category === 'TWK' || $category === 'TIU') {
                            $correctAnswer = strtoupper(trim((string) $question->correct_answer));
                            if ($userChoice === $correctAnswer) {
                                if ($category === 'TWK') $twkScore += 5;
                                if ($category === 'TIU') $tiuScore += 5;
                            }
                        }
                        // -- 3. LOGIKA SKD: TKP --
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
                $attempt->is_passed   = ($twkScore >= $pgTwk && $tiuScore >= $pgTiu && $tkpScore >= $pgTkp);
            }

            $attempt->save();
            $updatedCount++;
        }

        return back()->with('success', "Kalkulasi berhasil! {$updatedCount} data peserta telah diperbarui.");
    }
}