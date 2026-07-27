<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL; // <-- Tambahkan ini

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
   public function boot(): void
{
    // Berikan semua akses ke role 'admin'
    Gate::before(function ($user, $ability) {
        return $user->hasRole('admin') ? true : null;
    });
    if (env('APP_ENV') !== 'local') {
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }
    // Paksa HTTPS jika aplikasi tidak berjalan di environment local
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }
}
}
