<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MidtransCallbackController; // <--- JANGAN LUPA IMPORT INI

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// TAMBAHKAN INI:
Route::post('/midtrans-callback', [MidtransCallbackController::class, 'handle']);