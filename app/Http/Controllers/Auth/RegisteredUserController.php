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
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validasi Input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'instance_type' => 'required|in:1,2', // 1=Pusat, 2=Daerah
            'agency_name' => 'required|string|max:255',
            'province_code' => 'required|string|size:2',
            'gender' => 'required|in:1,2', // 1=Laki, 2=Perempuan
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. Buat User (Simpan dulu untuk mendapatkan ID Auto Increment)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'instance_type' => $request->instance_type,
            'agency_name' => $request->agency_name,
            'province_code' => $request->province_code,
            'gender' => $request->gender,
        ]);

        // 3. GENERATE NOMOR PESERTA (9 DIGIT)
        // Format: Instansi(1) + Provinsi(2) + Gender(1) + Urutan(5)
        // Contoh: 1 (Pusat) + 11 (Aceh) + 1 (Laki) + 00005 (Urutan) = 111100005
        
        $prefix = $request->instance_type . $request->province_code . $request->gender;
        
        // Pad ID user menjadi 5 digit angka (misal ID 5 jadi 00005)
        // Jika ID > 99999, string akan memanjang otomatis
        $sequence = str_pad($user->id, 5, '0', STR_PAD_LEFT);
        
        $finalCode = $prefix . $sequence; 

        // Update user dengan nomor peserta yang digenerate
        $user->participant_number = $finalCode;
        $user->save();

        // 4. Assign Role & Trigger Event
        // Pastikan Spatie Permission sudah terinstall dan dikonfigurasi
        $user->assignRole('user'); 

        event(new Registered($user));

        // 5. Login & Redirect
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}