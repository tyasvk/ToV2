<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\User\TryoutController as UserTryoutController;
use App\Http\Controllers\Admin\TryoutManagerController;
use App\Http\Controllers\Admin\QuestionManagerController;
use App\Http\Controllers\Admin\UserManagerController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\WalletController;
use Illuminate\Support\Facades\Route;

// --- 1. RUTE PUBLIK ---
Route::get('/', function () {
    return redirect()->route('login');
});

// --- 2. RUTE TERPROTEKSI (WAJIB LOGIN & VERIFIKASI) ---
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard Utama (Otomatis Redirect via Controller)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- AREA USER (PESERTA) ---
    Route::middleware(['role:user'])->group(function () {
        
        // Katalog & Pendaftaran Tryout
        Route::get('/tryouts/{tryout}', [UserTryoutController::class, 'show'])->name('tryout.show');
        Route::get('/tryout/{tryout}/register', [UserTryoutController::class, 'registerForm'])->name('tryout.register');
        Route::post('/tryout/{tryout}/register', [UserTryoutController::class, 'processRegistration'])->name('tryout.register.store');
        Route::get('/tryout/{tryout}/collective', [UserTryoutController::class, 'collectiveRegister'])->name('tryout.collective');

        // Katalog (Index)
        Route::get('/tryouts', [UserTryoutController::class, 'index'])->name('tryout.index');
    
        // Tryout Saya (Baru)
        Route::get('/my-tryouts', [UserTryoutController::class, 'myTryouts'])->name('tryout.my');

        // Rute Baru: Detail Riwayat per Tryout
        Route::get('/tryouts/{tryout}/history', [UserTryoutController::class, 'historyDetail'])->name('tryout.history.detail');

        // Rute Check Email (AJAX)
        Route::post('/check-email-availability', [App\Http\Controllers\User\TryoutController::class, 'checkEmail'])
        ->name('api.check.email');

        // Ujian (Exam)
        Route::get('/tryouts/{tryout}/exam', [UserTryoutController::class, 'exam'])->name('tryout.exam');
        Route::get('/tryouts/{tryout}/exam-bkn', [UserTryoutController::class, 'examBkn'])->name('tryout.exam.bkn');
        Route::post('/tryouts/{tryout}/finish', [UserTryoutController::class, 'finish'])->name('tryout.finish');
        // routes/web.php
        Route::get('/tryouts/{tryout}/wait', [UserTryoutController::class, 'wait'])->name('tryout.wait');

        Route::get('/tryouts/{tryout}/leaderboard', [UserTryoutController::class, 'leaderboard'])->name('tryout.leaderboard');

        // Hasil, Riwayat, & Sertifikat
        Route::get('/riwayat-tryout', [UserTryoutController::class, 'history'])->name('user.history');
        Route::get('/results/{attempt}', [UserTryoutController::class, 'result'])->name('tryout.result');
        Route::get('/results/{attempt}/review', [UserTryoutController::class, 'review'])->name('tryout.review');
        Route::get('/results/{attempt}/certificate', [UserTryoutController::class, 'certificate'])->name('tryout.certificate');
        Route::get('/tryouts/{tryout}/leaderboard', [UserTryoutController::class, 'leaderboard'])->name('tryout.leaderboard');

        // Pembayaran (Checkout)
        Route::get('/checkout/{tryout}', [CheckoutController::class, 'show'])->name('checkout.show');
        Route::post('/checkout/{tryout}', [CheckoutController::class, 'store'])->name('checkout.store');

        // Dompet (Wallet)
        Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
        Route::post('/wallet/topup', [WalletController::class, 'topUp'])->name('wallet.topup');
        Route::post('/wallet/{transaction}/pay', [WalletController::class, 'payPending'])->name('wallet.pay');
    });

    // --- AREA ADMINISTRATOR ---
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        
        // Dashboard Admin
        Route::get('/dashboard', [DashboardController::class, 'adminIndex'])->name('dashboard');
        
        // Kelola Tryout (CRUD)
        Route::resource('tryouts', TryoutManagerController::class);
        
        // Kelola Soal (Bank Soal per Paket)
        Route::get('/tryouts/{tryout}/questions', [QuestionManagerController::class, 'index'])->name('questions.index');
        Route::post('/tryouts/{tryout}/questions', [QuestionManagerController::class, 'store'])->name('questions.store');
        Route::post('/questions/{question}', [QuestionManagerController::class, 'update'])->name('questions.update');
        Route::delete('/questions/{question}', [QuestionManagerController::class, 'destroy'])->name('questions.destroy');
        Route::patch('/questions/reorder', [QuestionManagerController::class, 'reorder'])->name('questions.reorder');

        // Kelola Pengguna & Saldo
        Route::get('/users', [UserManagerController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}', [UserManagerController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagerController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/add-balance', [UserManagerController::class, 'addBalance'])->name('users.add-balance');

        // Laporan Transaksi
        Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    });

    // Profil (Admin & User)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- 3. CALLBACK MIDTRANS (DI LUAR AUTH) ---
Route::post('/midtrans/callback', [CheckoutController::class, 'handleNotification']);

require __DIR__.'/auth.php';