<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\ExamAttempt; 
use Carbon\Carbon;

class TryoutAkbarController extends Controller
{
    /**
     * Menampilkan katalog khusus Tryout Akbar.
     */
/**
     * Menampilkan katalog khusus Tryout Akbar.
     */
    public function index()
    {
        $user = auth()->user();

        // Ambil tryout tipe 'akbar' yang sudah dipublish
        $tryouts = Tryout::where('type', 'akbar')
            ->where('is_published', true)
            ->where(function($query) use ($user) {
                // KONDISI 1: Tampilkan jika TO belum berakhir (ended_at masih kosong atau masih berjalan)
                $query->whereNull('ended_at')
                      ->orWhere('ended_at', '>=', now())
                      // KONDISI 2: Tetap tampilkan TO yang sudah berakhir HANYA JIKA user sudah pernah mengerjakannya
                      ->orWhereHas('examAttempts', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->withExists(['transactions as is_registered' => function ($query) use ($user) {
                // Mendeteksi apakah user tersebut terdaftar (status: pending, paid, success)
                $query->whereIn('status', ['pending', 'paid', 'success'])
                      ->where(function($q) use ($user) {
                          $q->where('user_id', $user->id)
                            ->orWhereJsonContains('participants_data', $user->email);
                      });
            }])
            // Ambil data pengerjaan (attempt) terakhir user ini
            ->with(['examAttempts' => function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orderBy('id', 'desc')
                      ->select('id', 'tryout_id', 'user_id', 'total_score');
            }])
            ->orderBy('started_at', 'asc') // Urutkan berdasarkan jadwal mulai
            ->get();

        // Map data agar mudah dibaca di frontend
        $tryouts->transform(function ($tryout) {
            // Ambil ID attempt pertama (jika ada)
            $attempt = $tryout->examAttempts->first();
            $tryout->latest_attempt_id = $attempt ? $attempt->id : null;
            
            // Hapus relation agar payload lebih ringan
            unset($tryout->examAttempts);
            
            return $tryout;
        });

        return Inertia::render('User/TryoutAkbar/Index', [
            'tryouts' => $tryouts
        ]);
    }

    public function register(Tryout $tryout)
    {
        // Validasi Ekstra: Cegah pendaftaran jika TO sudah lewat batas end_date
        if ($tryout->end_date && now()->gt(Carbon::parse($tryout->end_date))) {
            return redirect()->route('user.tryout-akbar.index')
                ->with('error', 'Pendaftaran gagal. Jadwal pelaksanaan Tryout Akbar ini telah berakhir.');
        }

        $user = auth()->user();
        $transaction = Transaction::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->latest()
            ->first();

        if ($transaction && in_array($transaction->status, ['pending', 'paid', 'success'])) {
            return redirect()->route('tryout-akbar.wait', $tryout->id);
        }

        return Inertia::render('User/TryoutAkbar/Register', [
            'tryout' => $tryout,
            'transaction' => $transaction,
        ]);
    }

    public function storeRegistration(Request $request, Tryout $tryout)
    {
        // Validasi ekstra: Cegah submit form jika waktu sudah habis
        if ($tryout->end_date && now()->gt(Carbon::parse($tryout->end_date))) {
            return redirect()->route('user.tryout-akbar.index')->with('error', 'Waktu pendaftaran sudah habis.');
        }

        $request->validate([
            'proof' => 'required|array|min:1|max:5',
            'proof.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = auth()->user();
        $paths = [];
        if ($request->hasFile('proof')) {
            foreach ($request->file('proof') as $file) {
                $paths[] = $file->store('payment_proofs', 'public');
            }
        }

        Transaction::create([
            'user_id' => $user->id,
            'tryout_id' => $tryout->id,
            'invoice_code' => 'AKBAR-' . strtoupper(Str::random(6)) . date('dmY'),
            'amount' => $tryout->price,
            'unit_price' => $tryout->price,
            'qty' => 1,
            'total_amount' => $tryout->price,
            'status' => 'pending',
            'proof_payment' => $paths,
            'payment_method' => 'manual',
        ]);

        return redirect()->route('tryout-akbar.wait', $tryout->id);
    }

    public function waitingRoom(Tryout $tryout)
    {
        $user = auth()->user();
        
        // --- Cek apakah user sudah mengerjakan tryout ini ---
        $attempt = ExamAttempt::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->first();

        // JIKA BELUM PERNAH MENGERJAKAN dan JADWAL SUDAH LEWAT
        // Cegah akses ke ruang tunggu agar tidak bisa diklik mulai.
        // Tapi JIKA SUDAH PERNAH MENGERJAKAN, biarkan akses agar dia bisa mengecek status.
        if (!$attempt && $tryout->end_date && now()->gt(Carbon::parse($tryout->end_date))) {
            return redirect()->route('user.tryout-akbar.index')
                ->with('error', 'Mohon maaf, Anda tidak dapat mengakses ruang tunggu karena batas waktu pengerjaan Tryout Akbar ini telah berakhir.');
        }

        $transaction = Transaction::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->latest()
            ->first();

        if (!$transaction || $transaction->status === 'failed') {
            return redirect()->route('tryout-akbar.register', $tryout->id);
        }

        return Inertia::render('User/TryoutAkbar/Wait', [
            'tryout' => $tryout,
            'transaction' => $transaction,
            // Kirim status pengerjaan dan ID pengerjaannya ke Vue
            'has_attempted' => $attempt ? true : false,
            'attempt_id' => $attempt ? $attempt->id : null,
        ]);
    }

    public function show($id)
    {
        $tryout = Tryout::where('id', $id)
            ->where('type', 'akbar')
            ->where('is_published', true)
            ->firstOrFail();

        // Validasi ekstra saat mau masuk ke lembar soal utama
        // Hanya cegah jika user BELUM punya exam_attempt sebelumnya
        $user = auth()->user();
        $hasAttempt = ExamAttempt::where('user_id', $user->id)->where('tryout_id', $tryout->id)->exists();
        
        if (!$hasAttempt && $tryout->end_date && now()->gt(Carbon::parse($tryout->end_date))) {
            return redirect()->route('user.tryout-akbar.index')
                ->with('error', 'Jadwal pengerjaan Tryout Akbar ini telah berakhir.');
        }

        return Inertia::render('User/TryoutAkbar/Show', [
            'tryout' => $tryout
        ]);
    }
}