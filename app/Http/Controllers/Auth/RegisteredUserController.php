<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction; // Pastikan model ini dipanggil
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'instance_type' => 'required|in:1,2',
            'agency_name' => 'required|string|max:255',
            'province_code' => 'required|string|size:2',
            'gender' => 'required|in:1,2',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_code' => 'nullable|string|exists:users,affiliate_code', // Validasi kode referral
        ], [
            'referral_code.exists' => 'Kode referral tidak valid atau tidak ditemukan.',
        ]);

        // Buat User Baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'instance_type' => $request->instance_type,
            'agency_name' => $request->agency_name,
            'province_code' => $request->province_code,
            'gender' => $request->gender,
            'balance' => 0, // Set awal 0
        ]);

        // Generate Nomor Peserta
        $prefix = $request->instance_type . $request->province_code . $request->gender;
        $sequence = str_pad($user->id, 5, '0', STR_PAD_LEFT);
        $finalCode = $prefix . $sequence; 
        $user->participant_number = $finalCode;

        // ==========================================
        // LOGIKA BONUS AFILIASI & DOMPET
        // ==========================================
    // ==========================================
        // LOGIKA BONUS AFILIASI & DOMPET
        // ==========================================
        $referralCode = $request->referral_code;

        if (!empty($referralCode)) {
            $referrer = User::where('affiliate_code', $referralCode)->first();
            
            if ($referrer && $referrer->id !== $user->id) {
                
                // 1. Tambah saldo Dompet Pendaftar
                $user->balance += 2500;
                
                // --- INI YANG WAJIB DIAKTIFKAN ---
                // Simpan ID perujuk ke database
                $user->referred_by = $referrer->id;
                // ---------------------------------
                
                // Catat ke Riwayat Transaksi Dompet
                \App\Models\WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'in',
                    'amount' => 2500,
                    'description' => 'Bonus Saldo Pendaftaran (Kode Referral)',
                    'status' => 'success',
                    'proof_payment' => 'BONUS-REF-' . time()
                ]);
                
                // 2. Tambah saldo Komisi Pemilik Kode
                $referrer->increment('affiliate_balance', 2500);
            }
        }
        // ==========================================

        // Simpan pembaruan saldo & nomor peserta
        $user->save();

        $user->assignRole('user'); 
        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}