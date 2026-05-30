<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'instance_type' => $request->instance_type,
            'agency_name' => $request->agency_name,
            'province_code' => $request->province_code,
            'gender' => $request->gender,
            'balance' => 0, // Pastikan ada agar tidak terjadi error saat penambahan saldo
        ]);

        $prefix = $request->instance_type . $request->province_code . $request->gender;
        $sequence = str_pad($user->id, 5, '0', STR_PAD_LEFT);
        $finalCode = $prefix . $sequence; 

        $user->participant_number = $finalCode;

        // ==========================================
        // LOGIKA BARU: BONUS SALDO PENDAFTARAN AFILIASI
        // ==========================================
        if ($request->filled('ref')) {
            $referrer = User::where('affiliate_code', $request->ref)->first();
            
            if ($referrer) {
                // 1. Pendaftar dapat 2500 di Dompet
                $user->balance += 2500;
                
                // 2. Pemilik referal dapat 2500 di Saldo Komisi Afiliasi
                $referrer->increment('affiliate_balance', 2500);
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