<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
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
            'referral_code' => 'nullable|string|exists:users,affiliate_code', 
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
            'balance' => 0, 
            // Tambahkan baris ini agar sistem menyimpan siapa upline-nya saat register!
            'referred_by' => $request->referred_by ?? null,
        ]);

        // Generate Nomor Peserta
        $prefix = $request->instance_type . $request->province_code . $request->gender;
        $sequence = str_pad($user->id, 5, '0', STR_PAD_LEFT);
        $finalCode = $prefix . $sequence; 
        $user->participant_number = $finalCode;

        // ==========================================
        // LOGIKA BONUS DOMPET USER BARU LANGSUNG AKTIF
        // ==========================================
        $referralCode = $request->referral_code;

        if (!empty($referralCode)) {
            $referrer = User::where('affiliate_code', $referralCode)->first();
            
            if ($referrer && $referrer->id !== $user->id) {
                
                // 1. Tambah saldo Dompet Pendaftar LANGSUNG
                $user->balance += 2500;
                
                // Simpan ID perujuk untuk mencairkan komisi upline saat pembelian pertama nanti
                $user->referred_by = $referrer->id;
                
                // Catat ke Riwayat Transaksi Dompet Pendaftar
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'in',
                    'amount' => 2500,
                    'description' => 'Bonus Saldo Pendaftaran (Kode Referral)',
                    'status' => 'success',
                    'proof_payment' => 'BONUS-REF-' . time()
                ]);
                
                // PENTING: Saldo Upline ($referrer) tidak ditambah di sini (DIPENDING)
            }
        }
        // ==========================================

        $user->save();

        $user->assignRole('user'); 
        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}