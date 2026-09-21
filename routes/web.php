<?php

use App\Http\Controllers\Admin\AdminPortalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Buyer\AccountController;
use App\Http\Controllers\Buyer\CartController;
use App\Http\Controllers\Buyer\CheckoutController;
use App\Http\Controllers\Courier\DashboardController as CourierDashboardController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public Marketplace Homepage (Buyer facing)
Route::get('/', function () {
    return view('home');
})->name('home');

// Shopping Cart Routes (Available to all visitors & registered buyers)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// Quick Demo Login (Works 100% without XAMPP MySQL Database)
Route::get('/demo-login/{role?}', [AuthController::class, 'demoLogin'])->name('demo.login');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
    Route::post('/register/request-otp', [AuthController::class, 'requestOtp'])->name('register.request_otp');
    Route::post('/register/verify-otp', [AuthController::class, 'verifyOtp'])->name('register.verify_otp');
    Route::get('/register/otp-status', [AuthController::class, 'checkOtpStatus'])->name('register.otp_status');
    Route::get('/register/pending', [AuthController::class, 'showPendingApproval'])->name('register.pending');

    // Google OAuth Routes
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

// Logout Route
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');

// Super Admin Management Portal Routes (With automatic fallback demo auth if MySQL is offline)
Route::middleware('admin.demo')->prefix('admin')->name('admin.')->group(function () {
    
    // 1. Dashboard & Overview
    Route::get('/', [AdminPortalController::class, 'dashboard']);
    Route::get('/dashboard', [AdminPortalController::class, 'dashboard'])->name('dashboard');

    // 2. Manage Account Registrations (KYC)
    Route::get('/registrations', [AdminPortalController::class, 'registrations'])->name('registrations');
    Route::post('/registrations/{id}/status', [AdminPortalController::class, 'updateRegistrationStatus'])->name('registrations.status');

        // 3. Manage User Accounts
        Route::get('/users', [AdminPortalController::class, 'users'])->name('users');
        Route::post('/users/{id}/status', [AdminPortalController::class, 'updateUserStatus'])->name('users.status');

        // 4. Monitor Seller Compliance
        Route::get('/compliance', [AdminPortalController::class, 'compliance'])->name('compliance');
        Route::post('/compliance/{id}/action', [AdminPortalController::class, 'handleComplianceAction'])->name('compliance.action');

        // 5. Manage Complaints and Disputes
        Route::get('/disputes', [AdminPortalController::class, 'disputes'])->name('disputes');
        Route::post('/disputes/{id}/resolve', [AdminPortalController::class, 'resolveDispute'])->name('disputes.resolve');

        // 6. Manage Commission (10%)
        Route::get('/commission', [AdminPortalController::class, 'commission'])->name('commission');

        // 7. Generate Reports
        Route::get('/reports', [AdminPortalController::class, 'reports'])->name('reports');

        // 8. Manage Platform Settings
        Route::get('/settings', [AdminPortalController::class, 'settings'])->name('settings');
        Route::post('/settings/announcement', [AdminPortalController::class, 'saveAnnouncement'])->name('settings.announcement');
        Route::post('/settings/policies', [AdminPortalController::class, 'updatePolicies'])->name('settings.policies');

        // 9. Chat / Messaging
        Route::get('/chat', [AdminPortalController::class, 'chat'])->name('chat');
        Route::post('/chat/{contactId}/send', [AdminPortalController::class, 'sendMessage'])->name('chat.send');

        // 10. Account Management
        Route::get('/account', [AdminPortalController::class, 'account'])->name('account');
        Route::post('/account/update', [AdminPortalController::class, 'updateAccount'])->name('account.update');
});

// Authenticated Routes for Buyer, Seller & Courier
Route::middleware('auth')->group(function () {
    
    // Buyer / User Account Portal
    Route::prefix('user')->name('account.')->group(function () {
        Route::get('/account', [AccountController::class, 'index'])->name('index');
        Route::get('/account/profile', [AccountController::class, 'profile'])->name('profile');
        Route::post('/account/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
        Route::post('/account/avatar', [AccountController::class, 'updateAvatar'])->name('avatar.update');
        Route::post('/account/verify-id', [AccountController::class, 'submitIdVerification'])->name('id.submit');
        Route::post('/account/password', [AccountController::class, 'updatePassword'])->name('password.update');
        Route::post('/account/address', [AccountController::class, 'updateAddress'])->name('address.update');
        Route::get('/account/purchases', [AccountController::class, 'purchases'])->name('purchases');
        Route::get('/account/cards', [AccountController::class, 'cards'])->name('cards');
        Route::get('/account/addresses', [AccountController::class, 'addresses'])->name('addresses');
    });

    // Buyer Checkout Routes (ID Verification Required)
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');

    // Seller Centre Routes
    Route::middleware('role:seller')->prefix('seller')->name('seller.')->group(function () {
        Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');
    });

    // Courier / Rider Hub Routes
    Route::middleware('role:courier')->prefix('courier')->name('courier.')->group(function () {
        Route::get('/dashboard', [CourierDashboardController::class, 'index'])->name('dashboard');
    });
});
