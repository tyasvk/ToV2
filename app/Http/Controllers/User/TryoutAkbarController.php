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
use Illuminate\Support\Facades\Storage;

class TryoutAkbarController extends Controller
{
    /**
     * Menampilkan katalog khusus Tryout Akbar.
     */
    public function index()
    {
        $user = auth()->user();

        // Menggunakan eger-loading secara aman dan membatasi kolom agar tidak terjadi SQL Injection
        $tryouts = Tryout::where('type', 'akbar')
            ->where('is_published', true)
            ->where(function($query) use ($user) {
                $query->whereNull('ended_at')
                      ->orWhere('ended_at', '>=', now())
                      ->orWhereHas('examAttempts', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->withExists(['transactions as is_registered' => function ($query) use ($user) {
                $query->whereIn('status', ['pending', 'paid', 'success'])
                      ->where(function($q) use ($user) {
                          $q->where('user_id', $user->id)
                            ->orWhereJsonContains('participants_data', $user->email);
                      });
            }])
            ->with(['examAttempts' => function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orderBy('id', 'desc')
                      ->select('id', 'tryout_id', 'user_id', 'total_score');
            }])
            ->orderBy('started_at', 'asc')
            ->get();

        $tryouts->transform(function ($tryout) {
            $attempt = $tryout->examAttempts->first();
            $tryout->latest_attempt_id = $attempt ? $attempt->id : null;
            unset($tryout->examAttempts);
            return $tryout;
        });

        return Inertia::render('User/TryoutAkbar/Index', [
            'tryouts' => $tryouts
        ]);
    }

    public function register(Tryout $tryout)
    {
        // PENGAMANAN: Pastikan jenis tryout yang diakses benar-benar tipe 'akbar'
        if ($tryout->type !== 'akbar' || !$tryout->is_published) {
            abort(404, 'Tryout tidak ditemukan.');
        }

        if ($tryout->ended_at && now()->gt(Carbon::parse($tryout->ended_at))) {
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
        // PENGAMANAN: Validasi tipe untuk mencegah tampering ID lintas fitur
        if ($tryout->type !== 'akbar' || !$tryout->is_published) {
            abort(403, 'Aksi tidak sah.');
        }

        if ($tryout->ended_at && now()->gt(Carbon::parse($tryout->ended_at))) {
            return redirect()->route('user.tryout-akbar.index')->with('error', 'Waktu pendaftaran sudah habis.');
        }

        // PENGAMANAN KETAT VALIDASI FILE:
        // Menambahkan 'dimensions' untuk memastikan file benar-benar berupa gambar (bukan malware berkamuflase)
        $request->validate([
            'proof' => 'required|array|min:1|max:5',
            'proof.*' => 'required|image|mimes:jpeg,png,jpg|max:2048|dimensions:min_width=10,min_height=10',
        ], [
            'proof.*.image' => 'File harus berupa gambar asli.',
            'proof.*.mimes' => 'Format gambar harus jpeg, png, atau jpg.',
        ]);

        $user = auth()->user();
        
        // PENGAMANAN: Mencegah double submit jika user menembak API secara brutal bersamaan
        $existingRegistration = Transaction::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->whereIn('status', ['pending', 'paid', 'success'])
            ->exists();

        if ($existingRegistration) {
            return redirect()->route('tryout-akbar.wait', $tryout->id)->with('error', 'Anda sudah terdaftar.');
        }

        $paths = [];
        if ($request->hasFile('proof')) {
            foreach ($request->file('proof') as $file) {
                // PENGAMANAN: generate nama acak bawaan Laravel (store) untuk memutus payload penamaan file exploit
                $paths[] = $file->store('payment_proofs', 'public');
            }
        }

        Transaction::create([
            'user_id' => $user->id,
            'tryout_id' => $tryout->id,
            'invoice_code' => 'AKBAR-' . strtoupper(Str::random(8)) . date('dmYHis'),
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
        if ($tryout->type !== 'akbar' || !$tryout->is_published) {
            abort(404);
        }

        $user = auth()->user();
        
        $attempt = ExamAttempt::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->first();

        if (!$attempt && $tryout->ended_at && now()->gt(Carbon::parse($tryout->ended_at))) {
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
            'has_attempted' => $attempt ? true : false,
            'attempt_id' => $attempt ? $attempt->id : null,
        ]);
    }

    /**
     * PENGAMANAN: Mengubah parameter $id menjadi Route Model Binding (Tryout $tryout)
     * Untuk mencegah user jahat mengakses data tryout tipe lain melalui manipulasi ID numerik URL
     */
    public function show(Tryout $tryout)
    {
        if ($tryout->type !== 'akbar' || !$tryout->is_published) {
            abort(404, 'Tryout tidak ditemukan.');
        }

        $user = auth()->user();
        
        // PENGAMANAN: Pastikan user sudah membayar/terdaftar sebelum diizinkan melihat lembar soal
        $isRegistered = Transaction::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->whereIn('status', ['paid', 'success'])
            ->exists();

        if (!$isRegistered) {
            return redirect()->route('user.tryout-akbar.index')
                ->with('error', 'Anda harus menyelesaikan pembayaran terlebih dahulu untuk melihat tryout ini.');
        }

        $hasAttempt = ExamAttempt::where('user_id', $user->id)->where('tryout_id', $tryout->id)->exists();
        
        if (!$hasAttempt && $tryout->ended_at && now()->gt(Carbon::parse($tryout->ended_at))) {
            return redirect()->route('user.tryout-akbar.index')
                ->with('error', 'Jadwal pengerjaan Tryout Akbar ini telah berakhir.');
        }

        return Inertia::render('User/TryoutAkbar/Show', [
            'tryout' => $tryout
        ]);
    }
}