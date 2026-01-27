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
                // PENTING: Mengirim array role (misal: ['admin'] atau ['user'])
                'roles' => $request->user()->getRoleNames(), 
            ] : null,
        ],
        'flash' => [
            'message' => $request->session()->get('message'),
        ],
    ]);
}
}