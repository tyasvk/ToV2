<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\HandleInertiaRequests; // Import middleware Inertia
use Illuminate\Http\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
->withMiddleware(function (Middleware $middleware) {
    // Perbaikan: Gunakan namespace yang benar (Illuminate\Cookie\Middleware)
    // ATAU lebih baik lagi, hapus saja karena Laravel sudah menyertakannya secara default di grup 'web'
    $middleware->web(append: [
        HandleInertiaRequests::class,
    ]);

    $middleware->alias([
        'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    ]);
})
    ->withExceptions(function (Exceptions $exceptions) {
        // Konfigurasi penanganan error untuk Inertia (Opsional)
        // Agar saat error 403/404 tetap muncul di UI Vue Anda
    })
    ->create();