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
use App\Http\Controllers\User\TryoutController;
use App\Http\Controllers\User\MembershipController;
use App\Http\Controllers\User\AffiliateController as UserAffiliateController;

// Import Controller Admin
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagerController;
use App\Http\Controllers\Admin\TryoutManagerController;
use App\Http\Controllers\Admin\QuestionManagerController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\TryoutAkbarController as AdminTryoutAkbarController;
use App\Http\Controllers\Admin\AffiliateManagerController;

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

// Dashboard Umum
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

    // --- TRYOUT ADIDAYA ---
    Route::get('/tryout/adidaya', [TryoutController::class, 'adidaya'])->name('tryout.adidaya');

    // --- TRYOUT KATALOG & DETAIL ---
    Route::get('/tryouts', [UserTryoutController::class, 'index'])->name('tryout.index');
    Route::get('/tryout/{tryout}', [UserTryoutController::class, 'show'])->name('tryout.show');

    // --- PENDAFTARAN TRYOUT ---
    Route::get('/tryout/{tryout}/register', [UserTryoutController::class, 'registerForm'])->name('tryout.register');
    Route::post('/tryout/{tryout}/register', [TryoutController::class, 'processRegistration'])->name('tryout.processRegistration');
    
    // --- API & MEMBERSHIP ---
    Route::post('/check-email-availability', [UserTryoutController::class, 'checkEmail'])->name('api.check.email');
    Route::get('/membership', [MembershipController::class, 'index'])->name('membership.index');
    Route::post('/membership/buy', [MembershipController::class, 'buy'])->name('membership.buy');

    // --- PEMBAYARAN (CHECKOUT) ---
    Route::get('/checkout/{transaction}', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{transaction}/process', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::post('/checkout/callback', [CheckoutController::class, 'callbackInternal'])->name('checkout.callback.internal');

    // --- AFILIASI USER ---
    Route::get('/affiliate', [UserAffiliateController::class, 'index'])->name('affiliate.index');
    Route::post('/affiliate/join', [UserAffiliateController::class, 'join'])->name('affiliate.join');
    Route::post('/affiliate/withdraw', [UserAffiliateController::class, 'withdraw'])->name('affiliate.withdraw');
    Route::post('/affiliate/bank-info', [UserAffiliateController::class, 'updateBankInfo'])->name('affiliate.bank-info');

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
        Route::get('/{tryout}/register', [UserTryoutAkbarController::class, 'register'])->name('register');
        Route::post('/{tryout}/register', [UserTryoutAkbarController::class, 'storeRegistration'])->name('store-registration');
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
    Route::post('/users/{user}/add-membership', [UserManagerController::class, 'addMembership'])->name('users.add-membership');

    // Pastikan parameter rute adalah {withdrawal} agar sinkron dengan Controller
    Route::get('/affiliate/withdrawals', [AffiliateManagerController::class, 'withdrawals'])->name('affiliate.withdrawals');
    Route::post('/affiliate/withdrawals/{withdrawal}/approve', [AffiliateManagerController::class, 'approveWithdrawal'])->name('affiliate.approve');
    Route::post('/affiliate/withdrawals/{withdrawal}/reject', [AffiliateManagerController::class, 'rejectWithdrawal'])->name('affiliate.reject');
    Route::get('/affiliate/transactions', [AffiliateManagerController::class, 'transactions'])->name('affiliate.transactions');
    Route::get('/affiliate/leaderboard', [AffiliateManagerController::class, 'leaderboard'])->name('affiliate.leaderboard');
    Route::post('/affiliate/send-reward', [AffiliateManagerController::class, 'sendIndividualReward'])->name('affiliate.send-reward');
    Route::get('/affiliate/export-pdf', [AffiliateManagerController::class, 'exportPdf'])->name('affiliate.export-pdf');
    Route::post('/affiliate/send-bonus', [AffiliateManagerController::class, 'sendSpecialBonus'])->name('admin.affiliate.send-bonus');

    // Tryout Management
    Route::resource('tryouts', TryoutManagerController::class);
    Route::resource('adidaya-manage', \App\Http\Controllers\Admin\AdidayaManagerController::class);
    
    // Question Management
    Route::patch('/questions/reorder', [QuestionManagerController::class, 'reorder'])->name('questions.reorder');
    Route::resource('tryouts.questions', QuestionManagerController::class);
    Route::post('/tryouts/{tryout}/questions/import', [QuestionManagerController::class, 'import'])->name('tryouts.questions.import');
    Route::delete('/questions/{question}', [QuestionManagerController::class, 'destroy'])->name('questions.destroy');

    // Transaction Management
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');

    // Tryout Akbar Management
    Route::resource('tryout-akbar', AdminTryoutAkbarController::class);
    Route::get('tryout-akbar/{tryout}/participants', [AdminTryoutAkbarController::class, 'participants'])->name('tryout-akbar.participants');
    Route::post('tryout-akbar/verify/{transaction}', [AdminTryoutAkbarController::class, 'verifyRegistration'])->name('tryout-akbar.verify');
});

require __DIR__.'/auth.php';