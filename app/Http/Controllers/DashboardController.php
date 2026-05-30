<?php

namespace App\Http\Controllers;

use App\Models\Tryout;
use App\Models\User;
use App\Models\ExamAttempt;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Dashboard Utama untuk Peserta (User)
     */
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

        // 2. Ambil Pengumuman Pusat dari tabel settings (jika ada)
        $announcement = \App\Models\Setting::where('key', 'announcement')->value('value') 
            ?? 'Selamat datang di CPNS Nusantara! Terus tingkatkan kemampuan Anda dan persiapkan diri untuk ujian sesungguhnya.';

        // 3. Ambil Tryout terbaru yang BENAR-BENAR BELUM DIBELI & BELUM DIKERJAKAN
        $unpurchasedTryouts = Tryout::withCount('questions')
            ->whereNotIn('id', function($query) use ($user) {
                $query->select('tryout_id')
                      ->from('purchases')
                      ->where('user_id', $user->id);
            })
            ->whereNotIn('id', function($query) use ($user) {
                $query->select('tryout_id')
                      ->from('exam_attempts')
                      ->where('user_id', $user->id);
            })
            ->where('is_active', true)
            ->where('published_at', '<=', Carbon::now())
            ->latest()
            ->take(3)
            ->get();

        // 4. Hitung statistik personal peserta
        $attempts = ExamAttempt::where('user_id', $user->id);
        $averageScore = $attempts->count() > 0 ? round($attempts->avg('total_score')) : 0;

        return Inertia::render('Dashboard', [
            'announcement' => $announcement,
            'unpurchased_tryouts' => $unpurchasedTryouts,
            'balance' => $user->balance, // <-- Tambahkan baris ini untuk mengirim saldo dompet
            'stats' => [
                'completed_count' => $attempts->count(),
                'average_score'   => $averageScore,
            ]
        ]);
    }

    /**
     * Dashboard Khusus Administrator
     */
    public function adminIndex()
    {
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
}