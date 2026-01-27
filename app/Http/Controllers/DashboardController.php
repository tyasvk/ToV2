<?php

namespace App\Http\Controllers;

use App\Models\Tryout;
use App\Models\User;
use App\Models\ExamAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Dashboard untuk Peserta (User)
     */
    public function index()
    {
        $user = auth()->user();

        // Ambil Tryout yang tersedia untuk user
        $availableTryouts = Tryout::withCount('questions')
            ->where('is_active', true)
            ->where('published_at', '<=', Carbon::now())
            ->latest()
            ->take(3)
            ->get();

        return Inertia::render('Dashboard', [
            'availableTryouts' => $availableTryouts,
            'stats' => [
                'completed_count' => ExamAttempt::where('user_id', $user->id)->count(),
                'average_score' => round(ExamAttempt::where('user_id', $user->id)->avg('total_score') ?? 0),
                'global_rank' => '-', // Bisa dihitung jika sudah ada sistem ranking
            ]
        ]);
    }

    /**
     * Dashboard untuk Administrator
     */
public function adminIndex()
{
    // Cek role manual sebagai pertahanan terakhir
    if (!auth()->user()->hasRole('admin')) {
        abort(403);
    }

    return Inertia::render('Admin/Dashboard', [
        'stats' => [
            'total_users' => \App\Models\User::role('user')->count(),
            'total_tryouts' => \App\Models\Tryout::count(),
        ]
    ]);
}
}