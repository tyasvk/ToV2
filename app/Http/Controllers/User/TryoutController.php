<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use App\Models\Question;
use App\Models\ExamAttempt;
use App\Models\Transaction;
use App\Models\WalletTransaction;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class TryoutController extends Controller
{
    /**
     * Gabungan Katalog & Tryout Saya
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $userId = $user->id; 
        $isPremiumMember = $user->membership_expires_at && now()->lt($user->membership_expires_at);

        // --- 1. DATA KATALOG (Belum Dibeli) ---
        $catalogTryouts = Tryout::query()
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNotIn('type', ['akbar', 'adidaya'])->orWhereNull('type');
            })
            ->whereDoesntHave('transactions', function($q) use ($user) {
                $q->whereIn('status', ['paid', 'success'])
                  ->where(function($subQuery) use ($user) {
                      $subQuery->where('user_id', $user->id)
                               ->orWhereJsonContains('participants_data', $user->email);
                  });
            })
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->latest()
            ->get();

        // --- 2. DATA TRYOUT SAYA (Sudah Dibeli/Akses Premium) ---
        $myTryoutsQuery = Tryout::query()
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNotIn('type', ['akbar', 'adidaya'])->orWhereNull('type');
            });

        if ($isPremiumMember) {
            $myTryoutsQuery->withCount(['examAttempts as attempts_count' => function($q) use ($userId) {
                $q->where('user_id', $userId);
            }]);
        } else {
            $myTryoutsQuery->whereHas('transactions', function($q) use ($user) {
                $q->whereIn('status', ['paid', 'success'])
                  ->where(function($sq) use ($user) {
                      $sq->where('user_id', $user->id)
                        ->orWhereJsonContains('participants_data', $user->email);
                  });
            })->withCount(['examAttempts as attempts_count' => function($q) use ($userId) {
                $q->where('user_id', $userId);
            }]);
        }

        $myTryouts = $myTryoutsQuery
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->latest()
            ->get();

        return Inertia::render('User/Tryout/Index', [
            'catalogTryouts' => $catalogTryouts,
            'myTryouts' => $myTryouts,
            'filters' => $request->only(['search']),
            'isPremiumMember' => $isPremiumMember
        ]);
    }

    public function show(Tryout $tryout)
    {
        $isClosed = $tryout->end_date && now()->greaterThan($tryout->end_date);

        return Inertia::render('User/Tryout/Show', [
            'tryout' => $tryout,
            'is_registration_closed' => $isClosed,
        ]);
    }

    /**
     * Proses Pendaftaran Tryout (Validasi Diskon Grup & Voucher Afiliasi)
     */
    public function processRegistration(Request $request, Tryout $tryout)
    {
        if ($tryout->end_date && now()->greaterThan($tryout->end_date)) {
            return back()->withErrors(['message' => 'Pendaftaran untuk tryout ini sudah ditutup karena melewati batas waktu.']);
        }

        $request->validate([
            'payment_method' => 'required|in:wallet,midtrans',
            'emails' => 'array|max:5',
            'emails.*' => 'required|email|exists:users,email|distinct',
            'voucher_code' => 'nullable|string', 
        ]);

        $participants = collect([auth()->user()->email])->merge($request->emails ?? [])->unique()->values();
        $qty = $participants->count();

        $discount = 0;
        if ($qty == 2) $discount = 0.10;
        elseif ($qty == 3) $discount = 0.15;
        elseif ($qty == 4) $discount = 0.20;
        elseif ($qty >= 5) $discount = 0.25;

        $referrerId = null;
        $discountVoucher = 0;
        $commission = 0;

        if ($request->filled('voucher_code')) {
            $referrer = User::where('affiliate_code', trim($request->voucher_code))->first();
            
            if ($referrer) {
                if ($referrer->id !== auth()->id()) {
                    $referrerId = $referrer->id;
                    $discountVoucher = 2000; 
                    $commission = 2000;      
                } else {
                    return back()->withErrors(['voucher_code' => 'Anda tidak dapat menggunakan kode afiliasi Anda sendiri.']);
                }
            } else {
                return back()->withErrors(['voucher_code' => 'Kode voucher atau token afiliasi tidak valid.']);
            }
        }

        $totalHargaAwal = $tryout->price * $qty;
        $finalAmount = ($totalHargaAwal - ($totalHargaAwal * $discount)) - $discountVoucher;
        $finalAmount = max(0, $finalAmount); 
        $invoice = 'INV-' . strtoupper(Str::random(10));

        if ($request->payment_method === 'wallet') {
            $user = auth()->user();
            if ($user->balance < $finalAmount) {
                return back()->withErrors(['payment' => 'Saldo dompet tidak mencukupi.']);
            }

            DB::transaction(function () use ($user, $tryout, $finalAmount, $participants, $qty, $invoice, $referrerId, $commission, $discountVoucher) {
                $user->decrement('balance', $finalAmount);
                
                WalletTransaction::create([
                    'user_id' => $user->id, 
                    'type' => 'debit', 
                    'amount' => $finalAmount, 
                    'description' => 'Bayar Tryout: ' . $tryout->title, 
                    'status' => 'success', 
                    'proof_payment' => 'WALLET-SYSTEM'
                ]);
                
                Transaction::create([
                    'user_id' => $user->id, 
                    'tryout_id' => $tryout->id, 
                    'referrer_id' => $referrerId,
                    'invoice_code' => $invoice, 
                    'unit_price' => $tryout->price, 
                    'qty' => $qty, 
                    'amount' => $finalAmount, 
                    'discount_amount' => $discountVoucher,
                    'affiliate_commission' => $commission,
                    'participants_data' => $participants->all(), 
                    'status' => 'paid',
                    'metadata' => [
                        'base_price' => $tryout->price,
                        'jumlah_orang' => $qty,
                        'token_afiliasi' => $referrerId ? trim(request('voucher_code')) : null
                    ]
                ]);

                if ($referrerId) {
                    User::find($referrerId)->increment('affiliate_balance', $commission);
                }
            });

            return redirect()->route('tryout.index')->with('success', 'Pembelian berhasil!');
        }

        $transaction = Transaction::create([
            'user_id' => auth()->id(), 
            'tryout_id' => $tryout->id, 
            'referrer_id' => $referrerId,
            'invoice_code' => $invoice, 
            'unit_price' => $tryout->price, 
            'qty' => $qty, 
            'amount' => $finalAmount, 
            'discount_amount' => $discountVoucher,
            'affiliate_commission' => $commission,
            'participants_data' => $participants->all(), 
            'status' => 'pending',
            'metadata' => [
                'base_price' => $tryout->price,
                'jumlah_orang' => $qty,
                'token_afiliasi' => $referrerId ? trim($request->voucher_code) : null
            ]
        ]);
        
        return redirect()->route('checkout.show', $transaction->id);
    }

    public function adidaya()
    {
        $tryouts = Tryout::where('type', 'adidaya')
            ->where('is_published', true)
            ->latest()
            ->get();

        return Inertia::render('User/Tryout/AdidayaIndex', [
            'tryouts' => $tryouts
        ]);
    }

    public function myTryouts(Request $request)
    {
        $user = auth()->user();
        $userEmail = $user->email;
        $userId = $user->id;
        $isPremiumMember = $user->membership_expires_at && now()->lt($user->membership_expires_at);

        $tryouts = Tryout::query()
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNotIn('type', ['akbar', 'adidaya'])
                      ->orWhereNull('type');
            })
            ->where(function($query) use ($userId, $userEmail, $isPremiumMember) {
                $query->whereHas('transactions', function($q) use ($userId, $userEmail) {
                    $q->whereIn('status', ['paid', 'success'])
                      ->where(function($sq) use ($userId, $userEmail) {
                          $sq->where('user_id', $userId)
                            ->orWhereJsonContains('participants_data', $userEmail);
                      });
                });

                if ($isPremiumMember) {
                    $query->orWhere(function($q) {
                        $q->whereNotIn('type', ['akbar', 'adidaya'])
                          ->orWhereNull('type');
                    });
                }
            })
            ->withCount(['examAttempts as attempts_count' => function($q) use ($userId) {
                $q->where('user_id', $userId);
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

    // ===================================================================
    // PERBAIKAN MUTLAK: Mengunci Hitungan Detik dari Selisih Waktu Riil
    // ===================================================================
public function result(ExamAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) abort(403);
        
        $attempt->load('tryout');
        $tryout = $attempt->tryout;

        $backUrl = ($tryout->type === 'akbar') 
            ? route('tryout-akbar.wait', $tryout->id) 
            : route('tryout.history.detail', $tryout->id); 

        $pgTwk = ExamAttempt::PASSING_GRADE_TWK ?? 65;
        $pgTiu = ExamAttempt::PASSING_GRADE_TIU ?? 80;
        $pgTkp = ExamAttempt::PASSING_GRADE_TKP ?? 166;

        // =====================================================================
        // PERBAIKAN LOGIKA PERINGKAT: HANYA DIADU DENGAN PERCOBAAN PERTAMA
        // =====================================================================
        
        // 1. Ambil HANYA pengerjaan PERTAMA dari setiap user untuk tryout ini
        $firstAttempts = ExamAttempt::where('tryout_id', $tryout->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('user_id')
            ->map(function ($userAttempts) {
                return $userAttempts->first(); // Ambil riwayat paling awal
            });

        // Total peserta asli (jumlah orang, bukan jumlah percobaan)
        $totalParticipants = $firstAttempts->count();

        // 2. Format skor pengerjaan kita saat ini untuk perbandingan
        $currentPassed = ($attempt->twk_score >= $pgTwk && $attempt->tiu_score >= $pgTiu && $attempt->tkp_score >= $pgTkp) ? 1 : 0;
        $currentScoreString = sprintf('%d-%03d-%03d-%03d-%03d', $currentPassed, $attempt->total_score, $attempt->tkp_score, $attempt->tiu_score, $attempt->twk_score);

        // 3. Hitung Peringkat (Bandingkan dengan percobaan pertama orang lain)
        $rank = 1;
        foreach ($firstAttempts as $userId => $firstAttempt) {
            // Jangan membandingkan dengan riwayat diri sendiri agar peringkat tidak dobel
            if ($userId === $attempt->user_id) {
                continue; 
            }

            $isPassed = ($firstAttempt->twk_score >= $pgTwk && $firstAttempt->tiu_score >= $pgTiu && $firstAttempt->tkp_score >= $pgTkp) ? 1 : 0;
            $compareScoreString = sprintf('%d-%03d-%03d-%03d-%03d', $isPassed, $firstAttempt->total_score, $firstAttempt->tkp_score, $firstAttempt->tiu_score, $firstAttempt->twk_score);

            // Jika nilai percobaan pertama orang lain lebih tinggi, peringkat kita turun (bertambah 1)
            if (strcmp($compareScoreString, $currentScoreString) > 0) {
                $rank++;
            }
        }
        // =====================================================================

        $scoreDetails = [
            ['category' => 'Tes Wawasan Kebangsaan (TWK)', 'score' => $attempt->twk_score, 'passing_grade' => $pgTwk, 'is_passed' => $attempt->twk_score >= $pgTwk],
            ['category' => 'Tes Intelegensia Umum (TIU)', 'score' => $attempt->tiu_score, 'passing_grade' => $pgTiu, 'is_passed' => $attempt->tiu_score >= $pgTiu],
            ['category' => 'Tes Karakteristik Pribadi (TKP)', 'score' => $attempt->tkp_score, 'passing_grade' => $pgTkp, 'is_passed' => $attempt->tkp_score >= $pgTkp]
        ];

        $attempt->status = $attempt->is_passed ? 'lulus' : 'tidak_lulus';

        // Hitung total detik pengerjaan asli dari selisih waktu murni database
        $durationSeconds = 0;
        if ($attempt->created_at && $attempt->completed_at) {
            $durationSeconds = \Carbon\Carbon::parse($attempt->created_at)->diffInSeconds($attempt->completed_at);
        }
        
        $maxDurationLimit = ($tryout->duration ?? 110) * 60;
        
        if ($durationSeconds > $maxDurationLimit || $durationSeconds <= 0) {
            $durationSeconds = 45; 
        }
        
        $totalQuestions = $tryout->questions()->count() ?? 110;
        
        $timeStats = [
            'total_seconds' => max(1, $durationSeconds),
            'average_seconds' => $totalQuestions > 0 ? max(0, floor($durationSeconds / $totalQuestions)) : 0,
            'total_questions' => $totalQuestions,
        ];

        return Inertia::render('User/Tryout/Result', [
            'attempt' => $attempt,
            'tryout' => $tryout,
            'totalScore' => $attempt->total_score,
            'scoreDetails' => $scoreDetails,
            'ranking' => ['rank' => $rank, 'total_participants' => $totalParticipants],
            'timeStats' => $timeStats,
            'backUrl' => $backUrl 
        ]);
    }

    public function historyDetail(Tryout $tryout)
    {
        $user = auth()->user();
        $isPremiumMember = $user->membership_expires_at && now()->lt($user->membership_expires_at);

        $hasAccess = ($isPremiumMember && $tryout->type !== 'akbar') || Transaction::where('tryout_id', $tryout->id)
            ->whereIn('status', ['paid', 'success'])
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereJsonContains('participants_data', $user->email);
            })
            ->exists();

        if (!$hasAccess) abort(403, 'Akses ditolak.');

        if ($tryout->type === 'akbar') {
            $attempt = ExamAttempt::where('user_id', $user->id)->where('tryout_id', $tryout->id)->latest()->first();
            if ($attempt) return redirect()->route('tryout.result', $attempt->id);
        }

        $attempts = ExamAttempt::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('User/Tryout/HistoryDetail', [
            'tryout' => $tryout,
            'attempts' => $attempts
        ]);
    }

    private function validateAccess(Tryout $tryout)
    {
        $user = auth()->user();
        $now = now();
        $isPremiumMember = $user->membership_expires_at && now()->lt($user->membership_expires_at);
        
        $hasAccess = ($isPremiumMember && $tryout->type !== 'akbar') || Transaction::where('tryout_id', $tryout->id)
            ->whereIn('status', ['paid', 'success'])
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereJsonContains('participants_data', $user->email);
            })
            ->exists();

        if (!$hasAccess) {
            return [
                'allowed' => false,
                'route' => 'tryout.register',
                'params' => $tryout->id,
                'message' => 'Anda belum terdaftar atau membership Anda sudah tidak aktif.'
            ];
        }

        if ($tryout->type === 'akbar') {
            if ($now->lt($tryout->started_at)) {
                return ['allowed' => false, 'route' => 'tryout-akbar.index', 'message' => 'Event belum dimulai!'];
            }
        }

        $maxAttempts = ($tryout->type === 'akbar') ? 1 : 3;
        $attemptsCount = ExamAttempt::where('user_id', $user->id)->where('tryout_id', $tryout->id)->count();

        if ($attemptsCount >= $maxAttempts) {
            return [
                'allowed' => false,
                'route' => 'tryout.history.detail',
                'params' => $tryout->id,
                'message' => "Anda sudah mencapai batas maksimal ($maxAttempts x) pengerjaan."
            ];
        }

        return ['allowed' => true];
    }

    public function registerForm(Tryout $tryout)
    {
        if ($tryout->end_date && now()->greaterThan($tryout->end_date)) {
            return redirect()->route('tryout.show', $tryout->id)
                ->with('error', 'Pendaftaran untuk tryout ini telah ditutup.');
        }

        return Inertia::render('User/Tryout/Register', ['tryout' => $tryout]);
    }

    public function wait(Tryout $tryout)
    {
        $check = $this->validateAccess($tryout);
        if (!$check['allowed']) {
            return redirect()->route($check['route'], $check['params'] ?? $tryout->id)->with('error', $check['message']);
        }
        return Inertia::render('User/Tryout/Wait', ['tryout' => $tryout->loadCount('questions')]);
    }

public function examBkn(Tryout $tryout)
    {
        $check = $this->validateAccess($tryout);
        if (!$check['allowed']) return redirect()->route($check['route'], $tryout->id)->with('error', $check['message']);
        
        $user = auth()->user();

        $attempt = ExamAttempt::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->whereNull('completed_at')
            ->first();

        if (!$attempt) {
            $attempt = ExamAttempt::create([
                'user_id' => $user->id,
                'tryout_id' => $tryout->id,
                'answers' => [],
                'twk_score' => 0,
                'tiu_score' => 0,
                'tkp_score' => 0,
                'total_score' => 0,
                'left_count' => 0,
                'completed_at' => null
            ]);
        }

        // KUNCI ABSOLUT: Hitung selisih detik murni dari created_at awal ke waktu SEKARANG (now)
        $durationSeconds = ($tryout->duration ?? 100) * 60;
        $elapsedSeconds = now()->diffInSeconds($attempt->created_at, true); 
        $serverTimeLeft = (int) ($durationSeconds - $elapsedSeconds);

        if ($serverTimeLeft <= 0) {
            $attempt->update(['completed_at' => now()]);
            return redirect()->route('tryout.history.detail', $tryout->id)->with('error', 'Waktu pengerjaan tryout telah habis.');
        }

        return Inertia::render('User/Tryout/ExamSheetBKN', [
            'tryout' => $tryout, 
            'questions' => $tryout->questions()->orderBy('order', 'asc')->get(), 
            'user' => $user,
            'attempt' => $attempt,
            'server_time_left' => $serverTimeLeft
        ]);
    }

    public function exam(Tryout $tryout)
    {
        $check = $this->validateAccess($tryout);
        if (!$check['allowed']) return redirect()->route($check['route'], $tryout->id)->with('error', $check['message']);
        
        $user = auth()->user();

        $attempt = ExamAttempt::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->whereNull('completed_at')
            ->first();

        if (!$attempt) {
            $attempt = ExamAttempt::create([
                'user_id' => $user->id,
                'tryout_id' => $tryout->id,
                'answers' => [],
                'twk_score' => 0,
                'tiu_score' => 0,
                'tkp_score' => 0,
                'total_score' => 0,
                'left_count' => 0,
                'completed_at' => null
            ]);
        }

        // KUNCI ABSOLUT: Hitung selisih detik murni dari created_at awal ke waktu SEKARANG (now)
        $durationSeconds = ($tryout->duration ?? 100) * 60;
        $elapsedSeconds = now()->diffInSeconds($attempt->created_at, true);
        $serverTimeLeft = (int) ($durationSeconds - $elapsedSeconds);

        if ($serverTimeLeft <= 0) {
            $attempt->update(['completed_at' => now()]);
            return redirect()->route('tryout.history.detail', $tryout->id)->with('error', 'Waktu pengerjaan tryout telah habis.');
        }

        return Inertia::render('User/Tryout/ExamSheet', [
            'tryout' => $tryout, 
            'questions' => $tryout->questions()->orderBy('order', 'asc')->get(), 
            'user' => $user,
            'attempt' => $attempt,
            'server_time_left' => $serverTimeLeft
        ]);
    }   

public function incrementPenalty(Tryout $tryout)
{
    $attempt = ExamAttempt::where('user_id', auth()->id())
        ->where('tryout_id', $tryout->id)
        ->whereNull('completed_at')
        ->first();

    if ($attempt) {
        $attempt->increment('left_count');
        
        // Jika kesempatan habis saat dipanggil
        if ($attempt->left_count >= 3) {
            $attempt->update(['completed_at' => now()]);
            return response()->json(['status' => 'kicked']);
        }
    }

    return response()->json(['status' => 'ok']);
}

public function finish(Request $request, Tryout $tryout)
    {
        $user = auth()->user();
        
        // Cari sesi attempt aktif yang dibuat di awal halaman tadi
        $attempt = ExamAttempt::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->whereNull('completed_at')
            ->first();

        // Antisipasi jika data attempt sudah terkunci otomatis oleh sistem/tidak ditemukan
        if (!$attempt) {
            return redirect()->route('tryout.history.detail', $tryout->id)
                ->with('error', 'Sesi ujian Anda telah berakhir atau sudah tersimpan sebelumnya.');
        }

        $answers = $request->answers ?? [];
        $questions = $tryout->questions;
        $twk = 0; $tiu = 0; $tkp = 0;

        foreach ($questions as $q) {
            $ans = $answers[$q->id] ?? null;
            if ($q->type === 'TKP') {
                $tkpScoreMap = is_string($q->tkp_scores) ? json_decode($q->tkp_scores, true) : $q->tkp_scores;
                $tkp += (int) ($tkpScoreMap[$ans] ?? 0);
            } else {
                if ($ans === $q->correct_answer) {
                    if ($q->type === 'TWK') $twk += 5;
                    if ($q->type === 'TIU') $tiu += 5;
                }
            }
        }

        // Lakukan UPDATE data, bukan membuat data baru (mencegah double attempts)
        $attempt->update([
            'answers' => $answers, 
            'twk_score' => $twk, 
            'tiu_score' => $tiu, 
            'tkp_score' => $tkp, 
            'total_score' => $twk + $tiu + $tkp, 
            'completed_at' => now() 
        ]);
        
        return redirect()->route('tryout.result', $attempt->id);
    }

// Menjadi
public function history()
    {
        $attempts = ExamAttempt::where('user_id', auth()->id())
            ->with('tryout')
            ->latest()
            ->get();

        $pgTwk = ExamAttempt::PASSING_GRADE_TWK ?? 65; 
        $pgTiu = ExamAttempt::PASSING_GRADE_TIU ?? 80; 
        $pgTkp = ExamAttempt::PASSING_GRADE_TKP ?? 166;

        // Optimasi: Ambil pengerjaan pertama secara massal untuk menghindari query lambat (N+1)
        $tryoutIds = $attempts->pluck('tryout_id')->unique();
        $firstAttemptsByTryout = collect();
        
        foreach ($tryoutIds as $tId) {
            $firstAttemptsByTryout[$tId] = ExamAttempt::where('tryout_id', $tId)
                ->orderBy('created_at', 'asc')
                ->get()
                ->groupBy('user_id')
                ->map->first();
        }

        $histories = $attempts->map(function ($attempt) use ($pgTwk, $pgTiu, $pgTkp, $firstAttemptsByTryout) {
            $attempt->is_passed = ($attempt->twk_score >= $pgTwk && $attempt->tiu_score >= $pgTiu && $attempt->tkp_score >= $pgTkp);
            
            // Ambil seluruh percobaan pertama dari database yang dicache sebelumnya
            $firstAttempts = $firstAttemptsByTryout[$attempt->tryout_id] ?? collect();
            
            $currentPassed = $attempt->is_passed ? 1 : 0;
            $currentScoreString = sprintf('%d-%03d-%03d-%03d-%03d', $currentPassed, $attempt->total_score, $attempt->tkp_score, $attempt->tiu_score, $attempt->twk_score);

            $rank = 1;
            foreach ($firstAttempts as $userId => $firstAttempt) {
                // Abaikan percobaan diri sendiri
                if ($userId === $attempt->user_id) continue;

                $isPassed = ($firstAttempt->twk_score >= $pgTwk && $firstAttempt->tiu_score >= $pgTiu && $firstAttempt->tkp_score >= $pgTkp) ? 1 : 0;
                $compareScoreString = sprintf('%d-%03d-%03d-%03d-%03d', $isPassed, $firstAttempt->total_score, $firstAttempt->tkp_score, $firstAttempt->tiu_score, $firstAttempt->twk_score);

                // Jika nilai pertama orang lain lebih tinggi, rank bertambah
                if (strcmp($compareScoreString, $currentScoreString) > 0) {
                    $rank++;
                }
            }

            $attempt->rank = $rank;
            return $attempt;
        });

        $stats = [
            'total' => $histories->count(),
            'average_score' => $histories->count() > 0 ? round($histories->avg('total_score')) : 0,
            'passed' => $histories->where('is_passed', true)->count(),
        ];

        return Inertia::render('User/Tryout/History', [
            'histories' => $histories,
            'stats' => $stats
        ]);
    }

    public function review(ExamAttempt $attempt) 
    {
        if ($attempt->user_id !== auth()->id()) abort(403);
        $attempt->load('tryout');
        $userAnswers = $attempt->answers ?? [];
        $questions = $attempt->tryout->questions()->orderBy('order', 'asc')->get()->map(function ($q) use ($userAnswers) {
            $selected = $userAnswers[$q->id] ?? null;
            $q->user_answer = $selected; 
            $q->is_correct_user = (string)$selected === (string)$q->correct_answer;
            return $q;
        });
        return Inertia::render('User/Tryout/Review', ['attempt' => $attempt, 'questions' => $questions, 'tryout' => $attempt->tryout]);
    }

public function bundlingIndex()
    {
        // Mengambil tryout untuk halaman bundling
        $archivedTryouts = Tryout::query()
            ->where('is_published', true) // Wajib pastikan tryout sudah di-publish
            ->where(function ($query) {
                // Mengecualikan tryout event khusus (sesuaikan dengan logika di index)
                $query->whereNotIn('type', ['akbar', 'adidaya'])
                      ->orWhereNull('type');
            })
            // KOMENTARI ATAU HAPUS kode di bawah jika Anda ingin tryout yang masih aktif (baru dibuat) juga muncul di halaman bundling
            // ->where('end_date', '<', now()) 
            ->select('id', 'title', 'price', 'end_date', 'created_at')
            ->orderBy('created_at', 'desc') // Mengurutkan dari yang terbaru dibuat
            ->get();

        return Inertia::render('User/Bundling/Index', [
            'tryouts' => $archivedTryouts
        ]);
    }

public function leaderboard(Request $request, Tryout $tryout)
    {
        $pgTwk = ExamAttempt::PASSING_GRADE_TWK ?? 65; 
        $pgTiu = ExamAttempt::PASSING_GRADE_TIU ?? 80; 
        $pgTkp = ExamAttempt::PASSING_GRADE_TKP ?? 166;

        // 1. Ambil SEMUA riwayat pengerjaan, urutkan dari waktu paling awal
        $allAttempts = ExamAttempt::with('user')
            ->where('tryout_id', $tryout->id)
            ->orderBy('created_at', 'asc')
            ->get();

        // 2. Filter HANYA Pengerjaan PERTAMA tiap user
        $firstAttempts = $allAttempts->groupBy('user_id')->map->first()->values();

        // 3. Format data (Tidak perlu di-sort di sini, Vue yang akan mengurutkan)
        $rankings = $firstAttempts->map(function($a) use ($pgTwk, $pgTiu, $pgTkp) {
            $isPassed = ($a->twk_score >= $pgTwk && $a->tiu_score >= $pgTiu && $a->tkp_score >= $pgTkp);
            return [
                'id' => $a->id,
                'user_id' => $a->user_id,
                'name' => $a->user ? $a->user->name : 'User', 
                'avatar' => $a->user ? $a->user->avatar : null,
                'agency_name' => $a->user ? ($a->user->agency_name ?? $a->user->instansi) : null, 
                'province_code' => $a->user ? $a->user->province_code : null,
                'gender' => $a->user ? $a->user->gender : null, 
                'score' => $a->total_score, 
                'twk' => $a->twk_score, 
                'tiu' => $a->tiu_score, 
                'tkp' => $a->tkp_score, 
                'is_passed' => $isPassed, 
                'duration' => $a->created_at 
                    ? max(1, \Carbon\Carbon::parse($a->created_at)->diffInSeconds($a->completed_at ?? $a->updated_at ?? $a->created_at))
                    : 0,
                'is_me' => $a->user_id === auth()->id()
            ];
        });

        return Inertia::render('User/Tryout/Leaderboard', [
            'tryout' => $tryout, 
            'rankings' => $rankings, 
            'my_rank' => $rankings->firstWhere('is_me', true),
            'filters' => ['scope' => 'nasional', 'search' => '']
        ]);
    }

    public function certificate(ExamAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) abort(403);
        
        $attempt->load(['user', 'tryout']);

        $pgTwk = ExamAttempt::PASSING_GRADE_TWK ?? 65; 
        $pgTiu = ExamAttempt::PASSING_GRADE_TIU ?? 80; 
        $pgTkp = ExamAttempt::PASSING_GRADE_TKP ?? 166;
        
        $isPassed = (
            $attempt->twk_score >= $pgTwk && 
            $attempt->tiu_score >= $pgTiu && 
            $attempt->tkp_score >= $pgTkp
        );

        return view('pdf.certificate', [
            'attempt' => $attempt, 
            'isPassed' => $isPassed
        ]);
    }

    public function collectiveRegister(Tryout $tryout)
    {
        return Inertia::render('User/Tryout/CollectiveRegister', ['tryout' => $tryout]);
    }

public function checkEmail(Request $request)
    {
        // 1. Validasi request, pastikan tryout_id juga dikirim dari Vue
        $request->validate([
            'email' => 'required|email',
            'tryout_id' => 'required|exists:tryouts,id'
        ]);

        // 2. Cari pengguna berdasarkan email
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email belum terdaftar di aplikasi.'
            ], 404);
        }

        // 3. CEK TRANSAKSI: Apakah user ini sudah pernah membeli tryout ini dan lunas?
        $hasPurchased = \App\Models\Transaction::where('user_id', $user->id)
            ->where('tryout_id', $request->tryout_id)
            ->whereIn('status', ['paid', 'success'])
            ->exists();

        // Cek juga jika dia adalah pembuat tim / ada di dalam tim transaksi lunas (opsional, jika data tim disimpan di JSON metadata)
        // $inOtherTeam = \App\Models\Transaction::where('tryout_id', $request->tryout_id)->whereJsonContains('metadata->team_members', $user->email)->whereIn('status', ['paid', 'success'])->exists();

        if ($hasPurchased) {
            return response()->json([
                'status' => 'already_purchased',
                'message' => 'Email ini sudah memiliki tryout ini.'
            ], 400); // Kode 400 memicu blok catch() di frontend (berwarna merah)
        }

        // 4. Jika aman, kembalikan data user
        return response()->json([
            'status' => 'success',
            'message' => 'Email tersedia.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ], 200);
    }

    // ==========================================
    // FUNGSI CEK VOUCHER REAL-TIME (AJAX)
    // ==========================================
    public function checkVoucher(Request $request)
    {
        $code = trim($request->voucher_code);
        if (!$code) {
            return response()->json(['valid' => false, 'message' => 'Kode tidak boleh kosong.']);
        }

        $referrer = User::where('affiliate_code', $code)->first();

        if (!$referrer) {
            return response()->json(['valid' => false, 'message' => 'Kode voucher tidak valid / tidak terdaftar.']);
        }

        if ($referrer->id === auth()->id()) {
            return response()->json(['valid' => false, 'message' => 'Anda tidak dapat menggunakan kode afiliasi milik sendiri.']);
        }

        return response()->json(['valid' => true, 'message' => 'Kode valid.']);
    }

    /**
     * Menyimpan laporan soal dari user
     */
    public function reportQuestion(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        \App\Models\QuestionReport::create([
            'user_id' => auth()->id(),
            'question_id' => $request->question_id,
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Laporan berhasil dikirim. Terima kasih atas masukan Anda!');
    }
}