<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RoleMiddleware; // Import middleware lokal Anda

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1. Tambahkan Middleware Inertia ke grup 'web'
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        // 2. Daftarkan Alias Middleware (GABUNGKAN DI SINI)
        $middleware->alias([
            'role'               => RoleMiddleware::class, // Mengarah ke App\Http\Middleware\RoleMiddleware
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // 3. Pengecualian CSRF (GABUNGKAN DI SINI)
        $middleware->validateCsrfTokens(except: [
            'api/midtrans-callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();