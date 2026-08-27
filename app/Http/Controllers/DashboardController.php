<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Tryout;
use App\Models\User;
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

        // ==========================================================
        // 2. LOGIC TOTAL USER (Bisa diatur manual dari Pengaturan Admin)
        // ==========================================================
        $manualTotalUsersSetting = Setting::where('key', 'manual_total_users')->value('value');
        $realTotalUsers = User::count();
        
        // Jika manual_total_users diset > 0, gunakan angka itu. Jika 0, gunakan angka asli.
        $displayTotalUsers = ($manualTotalUsersSetting && (int)$manualTotalUsersSetting > 0) 
            ? (int)$manualTotalUsersSetting 
            : $realTotalUsers;

        // 3. STATISTIK USER
        $completedAttempts = ExamAttempt::where('user_id', $user->id)->whereNotNull('completed_at')->get();
        $stats = [
            'completed_count' => $completedAttempts->count(),
            'average_score' => $completedAttempts->count() > 0 ? round($completedAttempts->avg('total_score')) : 0,
        ];

        return Inertia::render('Dashboard', [
            'activeExam' => $activeExam, // Dioper ke frontend
            'balance' => $user->balance ?? 0,
            'stats' => $stats,
            // Format angka menjadi ribuan (Contoh: 1.500)
            'total_user_display' => number_format($displayTotalUsers, 0, ',', '.'), 
            'announcement' => Setting::where('key', 'announcement')->value('value') ?? ''
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