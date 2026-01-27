<?php

namespace App\Http\Controllers;

use App\Models\Tryout;
use App\Models\User;
use App\Models\ExamAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Dashboard Utama untuk Peserta (User)
     */
    public function index()
    {
        $user = auth()->user();

        // 1. Logic Smart Redirect: Jika Admin nyasar ke sini, lempar ke Admin Dashboard
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        // 2. Ambil paket tryout terbaru yang aktif & sudah dipublikasikan
        $availableTryouts = Tryout::withCount('questions')
            ->where('is_active', true)
            ->where('published_at', '<=', Carbon::now())
            ->latest()
            ->take(3)
            ->get();

        // 3. Hitung statistik personal peserta
        $attempts = ExamAttempt::where('user_id', $user->id);
        
        // Hitung rata-rata skor menggunakan rumus sederhana
        // $Average = \frac{\sum scores}{total attempts}$
        $averageScore = $attempts->count() > 0 ? round($attempts->avg('total_score')) : 0;

        return Inertia::render('Dashboard', [
            'availableTryouts' => $availableTryouts,
            'stats' => [
                'completed_count' => $attempts->count(),
                'average_score'   => $averageScore,
                'last_activity'   => $attempts->latest('completed_at')->first()?->completed_at?->diffForHumans() ?? 'Belum ada',
            ]
        ]);
    }

    /**
     * Dashboard Khusus Administrator
     */
    public function adminIndex()
    {
        // Keamanan tambahan: Pastikan hanya role admin yang bisa masuk
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_users'    => User::role('user')->count(),
                'total_tryouts'  => Tryout::count(),
                'total_attempts' => ExamAttempt::count(),
                'active_exams'   => Tryout::where('is_active', true)->count(),
            ],
            // Ambil 5 aktivitas pengerjaan terbaru dari seluruh peserta
            'recent_activity' => ExamAttempt::with(['user', 'tryout'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'id'         => $item->id,
                        'user_name'  => $item->user->name,
                        'tryout'     => $item->tryout->title,
                        'score'      => $item->total_score,
                        'date'       => $item->completed_at->format('d M Y H:i'),
                    ];
                })
        ]);
    }

// AttemptController.php atau DashboardController.php
public function history()
{
    $attempts = auth()->user()->attempts()
        ->with('tryout')
        ->latest()
        ->get();

    // Jika pakai Inertia:
    return inertia('User/History', [
        'attempts' => $attempts
    ]);

    // Jika pakai Vue Standalone (API):
    // return response()->json($attempts);
}
}