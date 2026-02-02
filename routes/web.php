<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;

// Import Controller User
use App\Http\Controllers\User\TryoutController as UserTryoutController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\WalletController;
use App\Http\Controllers\User\TryoutAkbarController as UserTryoutAkbarController;
use App\Http\Controllers\User\TryoutController; // Pastikan di-use
use App\Http\Controllers\User\MembershipController;

// Import Controller Admin
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagerController;
use App\Http\Controllers\Admin\TryoutManagerController;
use App\Http\Controllers\Admin\QuestionManagerController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\TryoutAkbarController as AdminTryoutAkbarController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Dashboard Umum (Redirect based on role usually handled in controller)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==========================================
// ROLE: USER
// ==========================================
Route::middleware(['auth', 'verified', 'role:user'])->group(function () {
    
    // --- WALLET ---
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/topup', [WalletController::class, 'topup'])->name('wallet.topup');

// Ganti baris ini di web.php
Route::get('/tryout/adidaya', [TryoutController::class, 'adidaya'])->name('tryout.adidaya');

    // --- TRYOUT KATALOG & DETAIL ---
    Route::get('/tryouts', [UserTryoutController::class, 'index'])->name('tryout.index');
    Route::get('/tryout/{tryout}', [UserTryoutController::class, 'show'])->name('tryout.show');

    // --- PENDAFTARAN TRYOUT (REGULER & KOLEKTIF) ---
    // 1. Halaman Form Pendaftaran
    Route::get('/tryout/{tryout}/register', [UserTryoutController::class, 'registerForm'])->name('tryout.register');
    
    // 2. Proses Simpan Pendaftaran (Masuk ke Logic CheckoutController@store)
    Route::post('/tryout/{tryout}/register', [CheckoutController::class, 'store'])->name('tryout.processRegistration');

    // PERBAIKAN DI SINI:
    Route::post('/tryout/{tryout}/register', [TryoutController::class, 'processRegistration'])
        ->name('tryout.processRegistration');

    // Route Checkout (Hanya method show)
    Route::get('/checkout/{transaction}', [CheckoutController::class, 'show'])
        ->name('checkout.show');
    
    // 3. API Cek Email untuk Kolektif
    Route::post('/check-email-availability', [UserTryoutController::class, 'checkEmail'])->name('api.check.email');

    Route::get('/membership', [MembershipController::class, 'index'])->name('membership.index');
    Route::post('/membership/buy', [MembershipController::class, 'buy'])->name('membership.buy');

    // --- PEMBAYARAN (CHECKOUT) ---
    // Halaman Bayar (Parameter: {transaction} ID Transaksi, bukan Tryout)
    Route::get('/checkout/{transaction}', [CheckoutController::class, 'show'])->name('checkout.show');

    // Pastikan menggunakan POST karena kita mengirim data (payment_method)
Route::post('/checkout/{transaction}/process', [CheckoutController::class, 'process'])->name('checkout.process');
    
    // Callback Internal setelah bayar sukses di Frontend
    Route::post('/checkout/callback', [CheckoutController::class, 'callbackInternal'])->name('checkout.callback.internal');

    // --- TRYOUT SAYA & PROSES UJIAN ---
    Route::get('/my-tryouts', [UserTryoutController::class, 'myTryouts'])->name('tryout.my');
    
    Route::get('/tryouts/{tryout}/wait', [UserTryoutController::class, 'wait'])->name('tryout.wait');
    Route::get('/tryouts/{tryout}/exam', [UserTryoutController::class, 'exam'])->name('tryout.exam');
    Route::get('/tryouts/{tryout}/exam-bkn', [UserTryoutController::class, 'examBkn'])->name('tryout.exam.bkn');
    Route::post('/tryouts/{tryout}/finish', [UserTryoutController::class, 'finish'])->name('tryout.finish');

    // --- HASIL & RIWAYAT ---
    Route::get('/tryouts/result/{attempt}', [UserTryoutController::class, 'result'])->name('tryout.result');
    Route::get('/tryouts/review/{attempt}', [UserTryoutController::class, 'review'])->name('tryout.review');
    Route::get('/tryouts/{tryout}/leaderboard', [UserTryoutController::class, 'leaderboard'])->name('tryout.leaderboard');
    
    Route::get('/history', [UserTryoutController::class, 'history'])->name('tryout.history');
    Route::get('/history/{tryout}', [UserTryoutController::class, 'historyDetail'])->name('tryout.history.detail');
    Route::get('/certificate/{attempt}', [UserTryoutController::class, 'certificate'])->name('tryout.certificate');

    // --- TRYOUT AKBAR ---
    Route::prefix('tryout-akbar')->name('tryout-akbar.')->group(function() {
        Route::get('/', [UserTryoutAkbarController::class, 'index'])->name('index');
        Route::get('/{tryout}', [UserTryoutAkbarController::class, 'show'])->name('show');
        
        // Register & Upload Bukti
        Route::get('/{tryout}/register', [UserTryoutAkbarController::class, 'register'])->name('register');
        Route::post('/{tryout}/register', [UserTryoutAkbarController::class, 'storeRegistration'])->name('store-registration');
        
        // Waiting Room
        Route::get('/{tryout}/waiting-room', [UserTryoutAkbarController::class, 'waitingRoom'])->name('wait');
    });
});

// ==========================================
// ROLE: ADMIN
// ==========================================
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('users', UserManagerController::class);
    Route::post('/users/{user}/add-balance', [UserManagerController::class, 'addBalance'])->name('users.add-balance');
    
    // Tryout Management
    Route::resource('tryouts', TryoutManagerController::class);
    
    // Question Management
    Route::resource('tryouts.questions', QuestionManagerController::class);
    Route::post('/tryouts/{tryout}/questions/import', [QuestionManagerController::class, 'import'])->name('tryouts.questions.import');

    // Transaction Management
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');

    // Tryout Akbar Management
    Route::resource('tryout-akbar', AdminTryoutAkbarController::class);
    Route::get('tryout-akbar/{tryout}/participants', [AdminTryoutAkbarController::class, 'participants'])->name('tryout-akbar.participants');
    Route::post('tryout-akbar/verify/{transaction}', [AdminTryoutAkbarController::class, 'verifyRegistration'])->name('tryout-akbar.verify');

    Route::post('/tryouts/{tryout}/questions/import', [QuestionManagerController::class, 'import'])->name('tryouts.questions.import');

    // Tambahkan baris ini secara eksplisit jika belum ada, atau sesuaikan nama di Vue
    Route::delete('/questions/{question}', [QuestionManagerController::class, 'destroy'])->name('questions.destroy');
});

require __DIR__.'/auth.php';