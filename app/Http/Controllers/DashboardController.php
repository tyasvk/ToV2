<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use App\Models\Transaction;
use App\Models\Tryout;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1. CARI SESI UJIAN YANG BERJALAN DENGAN STATUS BELUM SELESAI
        $activeAttempt = ExamAttempt::where('user_id', $user->id)
            ->whereNull('completed_at')
            ->with('tryout')
            ->latest()
            ->first();

        $activeExam = null;

        if ($activeAttempt && $activeAttempt->tryout) {
            $tryout = $activeAttempt->tryout;
            
            // Kalkulasi selisih sisa waktu nyata dari server
            $durationSeconds = ($tryout->duration ?? 100) * 60;
            $elapsedSeconds = now()->diffInSeconds($activeAttempt->created_at, true);
            $serverTimeLeft = (int) ($durationSeconds - $elapsedSeconds);

            // Jika ternyata waktu pengerjaan aslinya sudah habis saat ditinggal, langsung kunci sepihak
            if ($serverTimeLeft <= 0) {
                $activeAttempt->update(['completed_at' => now()]);
            } else {
                // Siapkan data ringkas untuk dioper ke Dashboard Vue
                $activeExam = [
                    'id' => $tryout->id,
                    'title' => $tryout->title,
                    // Kita kirim detik mentahnya agar bisa dihitung mundur oleh Vue
                    'time_left_seconds' => max(0, $serverTimeLeft) 
                ];
            }
        }

        // 2. RETRIEVE DATA BAWAAN DASHBOARD SEBELUMNYA
        $isPremiumMember = $user->membership_expires_at && now()->lt($user->membership_expires_at);

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
            ->latest()
            ->take(3) // Batasi rekomendasi awal di dashboard
            ->get();

        // Logic stats sederhana
        $completedAttempts = ExamAttempt::where('user_id', $user->id)->whereNotNull('completed_at')->get();
        $stats = [
            'completed_count' => $completedAttempts->count(),
            'average_score' => $completedAttempts->count() > 0 ? round($completedAttempts->avg('total_score')) : 0,
        ];

        return Inertia::render('Dashboard', [
            'activeExam' => $activeExam, // Dioper ke frontend
            'unpurchased_tryouts' => $catalogTryouts,
            'balance' => $user->balance ?? 0,
            'stats' => $stats,
            'announcement' => \App\Models\Setting::where('key', 'announcement')->first()?->value ?? null
        ]);
    }

    /**
     * Memformat sisa detik menjadi Jam:Menit:Detik
     */
    private function formatSecondsToTime($seconds)
    {
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
}