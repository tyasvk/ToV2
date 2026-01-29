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
// app/Http/Middleware/HandleInertiaRequests.php

public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'balance' => $request->user()->balance, // Pastikan balance dikirim
                'roles' => $request->user()->getRoleNames(), // Jika pakai Spatie
            ] : null,
        ],
       // --- BAGIAN INI SANGAT PENTING ---
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'snapToken' => fn () => $request->session()->get('snapToken'), // <--- PASTIKAN INI ADA
            ],
    ]);
}
}