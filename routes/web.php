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
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/category/{slug}', [\App\Http\Controllers\HomeController::class, 'category'])->name('category.show');

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

    // Forgot Password & Reset Routes (OTP-based)
    Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])->name('password.forgot');
    Route::post('/forgot-password/send-otp', [AuthController::class, 'sendResetOtp'])->name('password.send_otp');
    Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyResetOtp'])->name('password.verify_otp');
    Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword'])->name('password.reset.submit');

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

    // Seller Centre Routes (With automatic fallback demo auth if MySQL is offline)
    Route::middleware('seller.demo')->prefix('seller')->name('seller.')->group(function () {
        // 1. Dashboard Overview (Stats, Charts)
        Route::get('/', [\App\Http\Controllers\Seller\SellerPortalController::class, 'dashboard']);
        Route::get('/dashboard', [\App\Http\Controllers\Seller\SellerPortalController::class, 'dashboard'])->name('dashboard');

        // 2. Order Management & Notifications
        Route::get('/orders', [\App\Http\Controllers\Seller\SellerPortalController::class, 'orders'])->name('orders');
        Route::post('/orders/{id}/pack', [\App\Http\Controllers\Seller\SellerPortalController::class, 'packOrder'])->name('orders.pack');
        Route::get('/orders/{id}/waybill', [\App\Http\Controllers\Seller\SellerPortalController::class, 'printWaybill'])->name('orders.waybill');

        // 3. Courier Handover & Shipment Tracking
        Route::get('/courier', [\App\Http\Controllers\Seller\SellerPortalController::class, 'courier'])->name('courier');
        Route::post('/courier/{id}/schedule', [\App\Http\Controllers\Seller\SellerPortalController::class, 'schedulePickup'])->name('courier.schedule');

        // 4. Delivery Confirmations (Customer received order)
        Route::get('/deliveries', [\App\Http\Controllers\Seller\SellerPortalController::class, 'deliveries'])->name('deliveries');
        Route::post('/deliveries/{id}/confirm', [\App\Http\Controllers\Seller\SellerPortalController::class, 'markDelivered'])->name('deliveries.confirm');

        // 5. Handle Customer Feedback
        Route::get('/feedback', [\App\Http\Controllers\Seller\SellerPortalController::class, 'feedback'])->name('feedback');
        Route::post('/feedback/{id}/reply', [\App\Http\Controllers\Seller\SellerPortalController::class, 'replyFeedback'])->name('feedback.reply');

        // 6. Manage Inventory (Products, Stock, Vouchers & Discounts)
        Route::get('/inventory', [\App\Http\Controllers\Seller\SellerPortalController::class, 'inventory'])->name('inventory');
        Route::post('/inventory/add', [\App\Http\Controllers\Seller\SellerPortalController::class, 'addProduct'])->name('inventory.add');
        Route::post('/inventory/{id}/update', [\App\Http\Controllers\Seller\SellerPortalController::class, 'updateProduct'])->name('inventory.update');
        Route::post('/inventory/{id}/archive', [\App\Http\Controllers\Seller\SellerPortalController::class, 'toggleArchiveProduct'])->name('inventory.archive');
        Route::post('/inventory/vouchers/add', [\App\Http\Controllers\Seller\SellerPortalController::class, 'addVoucher'])->name('inventory.vouchers.add');

        // 7. Generate Report (Financial, Profit with From-To Date Pickers)
        Route::get('/reports', [\App\Http\Controllers\Seller\SellerPortalController::class, 'reports'])->name('reports');

        // 8. Chat / Messaging
        Route::get('/chat', [\App\Http\Controllers\Seller\SellerPortalController::class, 'chat'])->name('chat');
        Route::post('/chat/{contactId}/send', [\App\Http\Controllers\Seller\SellerPortalController::class, 'sendMessage'])->name('chat.send');

        // 9. Account Management (Store Profile, Pickup Address, Payout)
        Route::get('/account', [\App\Http\Controllers\Seller\SellerPortalController::class, 'account'])->name('account');
        Route::post('/account/profile', [\App\Http\Controllers\Seller\SellerPortalController::class, 'updateProfile'])->name('account.profile');
        Route::post('/account/address', [\App\Http\Controllers\Seller\SellerPortalController::class, 'updateAddress'])->name('account.address');
        Route::post('/account/withdraw', [\App\Http\Controllers\Seller\SellerPortalController::class, 'withdrawFunds'])->name('account.withdraw');
    });

    // Courier / Rider Hub Routes
    Route::middleware('role:courier')->prefix('courier')->name('courier.')->group(function () {
        Route::get('/dashboard', [CourierDashboardController::class, 'index'])->name('dashboard');
    });
});

// Logistics / Sorting Center Portal Routes
Route::middleware('logistics.demo')->prefix('logistics')->name('logistics.')->group(function () {
    // 1. Dashboard
    Route::get('/', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'dashboard']);
    Route::get('/dashboard', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'dashboard'])->name('dashboard');

    // 2. Rider Management (approve/disapprove/activate/deactivate)
    Route::get('/riders', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'riders'])->name('riders');
    Route::post('/riders/{id}/status', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'updateRiderStatus'])->name('riders.status');
    Route::post('/riders/{id}/toggle', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'toggleRiderActive'])->name('riders.toggle');

    // 3. Parcel Pickup Requests (confirm/approve/verify from seller)
    Route::get('/pickups', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'pickups'])->name('pickups');
    Route::post('/pickups/{id}/confirm', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'confirmPickup'])->name('pickups.confirm');

    // 4. Incoming Parcels Management
    Route::get('/parcels', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'parcels'])->name('parcels');
    Route::post('/parcels/{id}/receive', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'receiveParcel'])->name('parcels.receive');

    // 5. Sorting of Parcels
    Route::get('/sorting', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'sorting'])->name('sorting');
    Route::post('/sorting/{id}/sort', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'sortParcel'])->name('sorting.sort');

    // 6. Delivery Assignment (per area and per rider)
    Route::get('/assignments', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'assignments'])->name('assignments');
    Route::post('/assignments/{id}/assign', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'assignDelivery'])->name('assignments.assign');

    // 7. Delivery Monitoring
    Route::get('/monitoring', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'monitoring'])->name('monitoring');

    // 8. Generation of Reports
    Route::get('/reports', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'reports'])->name('reports');

    // 9. Chat / Messaging
    Route::get('/chat', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'chat'])->name('chat');
    Route::post('/chat/{contactId}/send', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'sendMessage'])->name('chat.send');

    // 10. Account Management
    Route::get('/account', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'account'])->name('account');
    Route::post('/account/update', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'updateAccount'])->name('account.update');
});
