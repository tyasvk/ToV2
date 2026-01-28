<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\User\TryoutController as UserTryoutController;
use App\Http\Controllers\Admin\TryoutManagerController;
use App\Http\Controllers\Admin\QuestionManagerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\TryoutController;
use App\Http\Controllers\User\CheckoutController;
// TAMBAHKAN IMPORT INI:
use App\Http\Controllers\User\WalletController; 

// 1. Rute Publik (Landing Page)
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. Rute Terproteksi (Wajib Login & Verifikasi)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard Utama (Redirect otomatis ke Admin/User via Controller)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- AREA USER (PESERTA) ---
    Route::middleware(['role:user'])->group(function () {
        // Katalog & Ujian
        Route::get('/tryouts', [UserTryoutController::class, 'index'])->name('tryout.index');
        Route::get('/tryouts/{tryout}/show', [UserTryoutController::class, 'show'])->name('tryout.start');
        
    Route::get('/tryouts/{tryout}/collective', [App\Http\Controllers\User\TryoutController::class, 'collectiveRegister'])->name('tryout.collective');
    Route::get('/tryout/{tryout}/collective', [TryoutController::class, 'collectiveRegister'])->name('tryout.collective');
    Route::get('/tryout/{tryout}/register', [App\Http\Controllers\User\TryoutController::class, 'registerForm'])->name('tryout.register');
    Route::post('/tryout/{tryout}/register', [App\Http\Controllers\User\TryoutController::class, 'processRegistration'])->name('tryout.register.store');

    // WALLET ROUTES
    Route::get('/wallet', [App\Http\Controllers\User\WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/topup', [App\Http\Controllers\User\WalletController::class, 'topUp'])->name('wallet.topup');

    // === TAMBAHKAN ROUTE WALLET DI SINI ===
        Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
        Route::post('/wallet/topup', [WalletController::class, 'topUp'])->name('wallet.topup');

    // API internal untuk cek email peserta
    Route::post('/internal-api/check-participant-email', function (\Illuminate\Http\Request $request) {
        $request->validate(['email' => 'required|email']);

        // Cek apakah email ada di tabel users
        $exists = \App\Models\User::where('email', $request->email)->exists();

        if (!$exists) {
            return response()->json(['message' => 'Peserta tidak terdaftar di CPNS Nusantara.'], 404);
        }

        return response()->json(['message' => 'Email valid.'], 200);
    })->name('api.check.email');
    
        // Tambahkan ini
    Route::get('/checkout/{tryout}', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{tryout}', [CheckoutController::class, 'store'])->name('checkout.store');
        // TAMBAHKAN RUTE INI (Halaman Lembar Ujian)
    Route::get('/tryouts/{tryout}/exam', [UserTryoutController::class, 'exam'])->name('tryout.exam');

    Route::get('/tryouts/{tryout}', [TryoutController::class, 'show'])->name('tryout.show');

    Route::get('/tryouts/{tryout}', [TryoutController::class, 'show'])
    ->middleware('check.access') // Pakai middleware di sini
    ->name('tryout.show');

    Route::get('/riwayat-tryout', [App\Http\Controllers\User\TryoutController::class, 'history'])->name('user.history');
    Route::get('/tryouts/{tryout}', [TryoutController::class, 'show'])->name('tryout.show');

    // Rute Baru untuk Fitur Hasil & Review
    Route::get('/results/{attempt}/review', [UserTryoutController::class, 'review'])->name('tryout.review');
    Route::get('/tryouts/{tryout}/leaderboard', [UserTryoutController::class, 'leaderboard'])->name('tryout.leaderboard');
    Route::get('/results/{attempt}/certificate', [UserTryoutController::class, 'certificate'])->name('tryout.certificate');

    // TAMBAHKAN: Mode CAT BKN (Klasik)
    Route::get('/tryouts/{tryout}/exam-bkn', [UserTryoutController::class, 'examBkn'])->name('tryout.exam.bkn');
        
        Route::post('/tryouts/{tryout}/finish', [UserTryoutController::class, 'finish'])->name('tryout.finish');
        Route::get('/results/{attempt}', [UserTryoutController::class, 'result'])->name('tryout.result');
    });

    // --- AREA ADMINISTRATOR ---
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminIndex'])->name('dashboard');
        
        // Kelola Tryout (CRUD)
        Route::resource('tryouts', TryoutManagerController::class);
        
        // Kelola Soal (Bank Soal per Paket)
        Route::get('/tryouts/{tryout}/questions', [QuestionManagerController::class, 'index'])->name('questions.index');
        Route::post('/tryouts/{tryout}/questions', [QuestionManagerController::class, 'store'])->name('questions.store');
        Route::post('/questions/{question}', [QuestionManagerController::class, 'update'])->name('questions.update');
        Route::delete('/questions/{question}', [QuestionManagerController::class, 'destroy'])->name('questions.destroy');
        Route::patch('/questions/reorder', [QuestionManagerController::class, 'reorder'])->name('questions.reorder');

        // Kelola Pengguna
        Route::get('/users', function() { return inertia('Admin/Users/Index'); })->name('users.index');
        // Tambahkan rute ini:
    Route::get('/transactions', [App\Http\Controllers\Admin\TransactionController::class, 'index'])->name('transactions.index');
    });

    // Profil (Bisa diakses Admin & User)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
// Tambahkan di LUAR middleware 'auth' karena Midtrans yang memanggil
Route::post('/midtrans/callback', [CheckoutController::class, 'handleNotification']);

require __DIR__.'/auth.php';