<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Menentukan data yang dibagikan secara default ke semua halaman.
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'balance' => $request->user()->balance,
                    
                    // --- TAMBAHKAN INI AGAR MUNCUL DI DASHBOARD ---
                    'participant_number' => $request->user()->participant_number,
                    'agency_name' => $request->user()->agency_name,// PERBAIKAN DI SINI (Ganti 'image' jadi 'avatar')
                    'avatar' => $request->user()->avatar, 
                    // ------------------------------------------------
                    'province_code' => $request->user()->province_code, // <--- TAMBAHKAN
                    'gender' => $request->user()->gender,               // <--- TAMBAHKAN
                    'instance_type' => $request->user()->instance_type, // <--- TAMBAHKAN
                    // ----------------------------------------------
                    
                    'roles' => $request->user()->getRoleNames(),
                ] : null,
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'snapToken' => fn () => $request->session()->get('snapToken'),
            ],
        ]);
    }
}