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
use App\Http\Controllers\User\TryoutController;

// Import Controller Admin
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagerController;
use App\Http\Controllers\Admin\TryoutManagerController;
use App\Http\Controllers\Admin\QuestionManagerController;
use App\Http\Controllers\Admin\TransactionController;


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==========================================
// ROLE: USER
// ==========================================
Route::middleware(['auth', 'verified', 'role:user'])->group(function () {
    // Wallet
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/topup', [WalletController::class, 'topup'])->name('wallet.topup');

    // Tryout Public List
    Route::get('/tryouts', [UserTryoutController::class, 'index'])->name('tryout.index');
    Route::get('/tryout/{tryout}', [UserTryoutController::class, 'show'])->name('tryout.show');

// URL: /tryout/{id}/register
Route::get('/tryout/{tryout}/register', [TryoutController::class, 'register'])->name('tryout.register');

// 2. PROSES Simpan Pendaftaran (POST)
// URL: /tryout/{id}/register (Method POST)
Route::post('/tryout/{tryout}/register', [CheckoutController::class, 'store'])->name('tryout.processRegistration');

// 3. Halaman Pembayaran/Checkout (View Show.vue)
// URL: /checkout/{transaction} (Perhatikan: Parameternya transaction, bukan tryout)
Route::get('/checkout/{transaction}', [CheckoutController::class, 'show'])->name('checkout.show');

    // Rute Tryout Akbar
    Route::get('/tryout-akbar', [\App\Http\Controllers\User\TryoutAkbarController::class, 'index'])->name('tryout-akbar.index');
    
    // HALAMAN REGISTRASI & UPLOAD
    Route::get('/tryout-akbar/{tryout}/register', [\App\Http\Controllers\User\TryoutAkbarController::class, 'register'])->name('tryout-akbar.register');
    Route::post('/tryout-akbar/{tryout}/register', [\App\Http\Controllers\User\TryoutAkbarController::class, 'storeRegistration'])->name('tryout-akbar.store-registration');
    
    Route::get('/tryout-akbar/{tryout}', [\App\Http\Controllers\User\TryoutAkbarController::class, 'show'])->name('tryout-akbar.show');
 
    // ROUTE BARU: HALAMAN WAIT / LOBBY
    Route::get('/tryout-akbar/{tryout}/waiting-room', [\App\Http\Controllers\User\TryoutAkbarController::class, 'waitingRoom'])->name('tryout-akbar.wait');
    
    // Register Tryout
    Route::get('/tryout/{tryout}/register', [UserTryoutController::class, 'registerForm'])->name('tryout.register');
    Route::post('/tryout/{tryout}/register', [UserTryoutController::class, 'processRegistration'])->name('tryout.register.store'); // Alias lama
    Route::post('/tryout/{tryout}/process-register', [UserTryoutController::class, 'processRegistration'])->name('tryout.processRegistration'); // Alias baru sesuai Vue

    // Register Kolektif
    Route::get('/tryout/{tryout}/collective-register', [UserTryoutController::class, 'collectiveRegister'])->name('tryout.collectiveRegister');
    
    // --- TAMBAHAN PENTING DI SINI ---
    // Rute untuk pengecekan email realtime (Vue)
    Route::post('/check-email-availability', [UserTryoutController::class, 'checkEmail'])->name('api.check.email');
    // --------------------------------

    // Checkout
    Route::get('/checkout/{tryout}', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{tryout}/process', [CheckoutController::class, 'process'])->name('checkout.process');

    // Route untuk memproses form pendaftaran (POST)
    Route::post('/tryout/{tryout_id}/register', [CheckoutController::class, 'store'])->name('tryout.processRegistration');

    // Route untuk menampilkan halaman pembayaran (GET)
    // Perhatikan parameternya {id} (ID Transaksi)
    Route::get('/checkout/{id}', [CheckoutController::class, 'show'])->name('checkout.show');

    // Route Callback (POST)
    Route::post('/checkout/callback', [CheckoutController::class, 'callbackInternal'])->name('checkout.callback.internal');

    // My Tryouts
    Route::get('/my-tryouts', [UserTryoutController::class, 'myTryouts'])->name('tryout.my');
    
    // Exam Process
    Route::get('/tryouts/{tryout}/wait', [UserTryoutController::class, 'wait'])->name('tryout.wait');
    Route::get('/tryouts/{tryout}/exam', [UserTryoutController::class, 'exam'])->name('tryout.exam');
    Route::get('/tryouts/{tryout}/exam-bkn', [UserTryoutController::class, 'examBkn'])->name('tryout.exam.bkn'); // Tambahan untuk mode BKN
    Route::post('/tryouts/{tryout}/finish', [UserTryoutController::class, 'finish'])->name('tryout.finish');
    
    // Result & Review
    Route::get('/tryouts/result/{attempt}', [UserTryoutController::class, 'result'])->name('tryout.result');
    Route::get('/tryouts/review/{attempt}', [UserTryoutController::class, 'review'])->name('tryout.review');
    Route::get('/tryouts/{tryout}/leaderboard', [UserTryoutController::class, 'leaderboard'])->name('tryout.leaderboard');
    
    // History
    Route::get('/history', [UserTryoutController::class, 'history'])->name('tryout.history');
    Route::get('/history/{tryout}', [UserTryoutController::class, 'historyDetail'])->name('tryout.history.detail');
    
    // Certificate
    Route::get('/certificate/{attempt}', [UserTryoutController::class, 'certificate'])->name('tryout.certificate');

    // Halaman List Tryout Akbar
    Route::get('/tryout-akbar', [\App\Http\Controllers\User\TryoutAkbarController::class, 'index'])->name('tryout-akbar.index');
    Route::get('/tryout-akbar/{tryout}', [\App\Http\Controllers\User\TryoutAkbarController::class, 'show'])->name('tryout-akbar.show');
});

// ==========================================
// ROLE: ADMIN
// ==========================================
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('users', UserManagerController::class);
    
    // Tryout Management
    Route::resource('tryouts', TryoutManagerController::class);
    
    // Question Management (Nested Resource)
    Route::resource('tryouts.questions', QuestionManagerController::class);
    Route::post('/tryouts/{tryout}/questions/import', [QuestionManagerController::class, 'import'])->name('tryouts.questions.import');

    // Transaction Management
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');

    // TAMBAHKAN INI: Route khusus untuk tambah saldo
    Route::post('/users/{user}/add-balance', [UserManagerController::class, 'addBalance'])->name('users.add-balance');

    // Resource Controller khusus Tryout Akbar
    Route::resource('tryout-akbar', \App\Http\Controllers\Admin\TryoutAkbarController::class);

    // TAMBAHKAN ROUTE BARU INI:
    // Halaman list peserta per tryout
    Route::get('tryout-akbar/{tryout}/participants', [\App\Http\Controllers\Admin\TryoutAkbarController::class, 'participants'])
        ->name('tryout-akbar.participants');

    // Action untuk Approve/Reject
    Route::post('tryout-akbar/verify/{transaction}', [\App\Http\Controllers\Admin\TryoutAkbarController::class, 'verifyRegistration'])
        ->name('tryout-akbar.verify');
});

require __DIR__.'/auth.php';