<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Transaction; // <--- WAJIB ADA BARIS INI
use Illuminate\Support\Str; // <--- WAJIB DITAMBAHKAN AGAR ERROR HILANG

class TryoutAkbarController extends Controller
{
    /**
     * Menampilkan katalog khusus Tryout Akbar.
     */
    public function index()
    {
        $user = auth()->user();

        // Ambil tryout tipe 'akbar' yang sudah dipublish
        // Kita juga bisa mengecek status kepesertaan user di sini jika perlu
        $tryouts = Tryout::where('type', 'akbar')
            ->where('is_published', true)
            ->withExists(['transactions as is_registered' => function ($query) use ($user) {
                $query->whereIn('status', ['paid', 'success'])
                      ->where(function($q) use ($user) {
                          $q->where('user_id', $user->id)
                            ->orWhereJsonContains('participants_data', $user->email);
                      });
            }])
            ->latest()
            ->get();

        return Inertia::render('User/TryoutAkbar/Index', [
            'tryouts' => $tryouts
        ]);
    }

    public function register(Tryout $tryout)
    {
        $user = auth()->user();
        $transaction = Transaction::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->latest()
            ->first();

        // JIKA SUDAH DAFTAR (Pending / Paid), LANGSUNG KE WAITING ROOM
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

        // REDIRECT KE WAITING ROOM SETELAH UPLOAD
        return redirect()->route('tryout-akbar.wait', $tryout->id);
    }

    // METHOD BARU: Waiting Room
    public function waitingRoom(Tryout $tryout)
    {
        $user = auth()->user();
        $transaction = Transaction::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->latest()
            ->first();

        // Jika belum daftar atau ditolak, kembalikan ke register
        if (!$transaction || $transaction->status === 'failed') {
            return redirect()->route('tryout-akbar.register', $tryout->id);
        }

        return Inertia::render('User/TryoutAkbar/Wait', [
            'tryout' => $tryout,
            'transaction' => $transaction,
        ]);
    }

    /**
     * Menampilkan detail spesifik Tryout Akbar (Opsional).
     * Jika Anda ingin halaman detail sebelum register.
     */
    public function show($id)
    {
        $tryout = Tryout::where('id', $id)
            ->where('type', 'akbar')
            ->where('is_published', true)
            ->firstOrFail();

        return Inertia::render('User/TryoutAkbar/Show', [
            'tryout' => $tryout
        ]);
    }
}