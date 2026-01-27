<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTryoutAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
// app/Http/Middleware/CheckTryoutAccess.php
public function handle(Request $request, Closure $next)
{
    $tryoutId = $request->route('tryout');
    $tryout = Tryout::findOrFail($tryoutId);

    if ($tryout->is_paid && !auth()->user()->purchasedTryouts()->where('tryout_id', $tryoutId)->exists()) {
        return redirect()->route('tryout.index')->with('error', 'Silakan beli paket ini terlebih dahulu.');
    }

    return $next($request);
}
}
