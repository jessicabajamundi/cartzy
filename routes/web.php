<?php

\Illuminate\Support\Facades\Route::get('/registration-documents/{user}/{document}', function (\App\Models\User $user, string $document) {
    abort_unless(auth()->user()->role === 'admin' || auth()->id() === $user->id, 403);
    abort_unless(in_array($document, ['sec_certificate', 'bir_certificate'], true), 404);
    $path = $user->{$document};
    abort_unless($path && \Illuminate\Support\Facades\Storage::disk('local')->exists($path), 404);
    return \Illuminate\Support\Facades\Storage::disk('local')->response($path);
})->middleware('auth')->name('registration.document');

use App\Http\Controllers\Admin\AdminPortalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Buyer\AccountController;
use App\Http\Controllers\Buyer\CartController;
use App\Http\Controllers\Buyer\CheckoutController;
use App\Http\Controllers\Buyer\DashboardController as BuyerDashboardController;
use App\Http\Controllers\Courier\DashboardController as CourierDashboardController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public Marketplace Homepage (Buyer facing)
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/sale', [\App\Http\Controllers\HomeController::class, 'sale'])->name('sale');
Route::get('/products/{id}', [\App\Http\Controllers\ProductController::class, 'show'])->name('products.show');
Route::post('/products/{id}/comments', [\App\Http\Controllers\ProductController::class, 'comment'])->whereNumber('id')->middleware(['auth', 'role:buyer,seller', 'throttle:10,1'])->name('products.comments.store');
Route::post('/comments/{commentId}/react', [\App\Http\Controllers\ProductController::class, 'react'])->whereNumber('commentId')->middleware(['auth', 'role:buyer,seller', 'throttle:60,1'])->name('products.comments.react');
Route::get('/category/{slug}', [\App\Http\Controllers\HomeController::class, 'category'])->name('category.show');
Route::get('/store/{slug}', [\App\Http\Controllers\StoreController::class, 'show'])->name('store.show');
Route::post('/store/{slug}/follow', [\App\Http\Controllers\StoreController::class, 'toggleFollow'])->middleware(['auth', 'role:buyer', 'throttle:30,1'])->name('store.follow');

Route::view('/buyer-account-required', 'auth.buyer-required')->middleware('auth')->name('buyer.required');

// Shopping Cart Routes (Available to all visitors & registered buyers)
Route::get('/cart', [CartController::class, 'index'])->middleware(\App\Http\Middleware\EnsureBuyerShopping::class)->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->middleware(\App\Http\Middleware\EnsureBuyerShopping::class)->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->middleware(\App\Http\Middleware\EnsureBuyerShopping::class)->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->middleware(\App\Http\Middleware\EnsureBuyerShopping::class)->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->middleware(\App\Http\Middleware\EnsureBuyerShopping::class)->name('cart.clear');
Route::post('/cart/selection', [CartController::class, 'selection'])->middleware('auth')->middleware(\App\Http\Middleware\EnsureBuyerShopping::class)->name('cart.selection');

// Quick Demo Login (Works 100% without XAMPP MySQL Database)
Route::get('/demo-login/{role?}', [AuthController::class, 'demoLogin'])->name('demo.login');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/seller/login', [AuthController::class, 'showLoginForm'])->defaults('portal', 'seller')->name('login.seller');
    Route::get('/logistics/login', [AuthController::class, 'showLoginForm'])->defaults('portal', 'logistics')->name('login.logistics');
    Route::get('/courier/login', [AuthController::class, 'showLoginForm'])->defaults('portal', 'courier')->name('login.courier');
    Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->defaults('portal', 'admin')->name('login.admin');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::get('/register/rider', [AuthController::class, 'showRiderRegisterForm'])->name('register.rider');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
    Route::post('/register/request-otp', [AuthController::class, 'requestOtp'])->name('register.request_otp');
    Route::post('/register/verify-otp', [AuthController::class, 'verifyOtp'])->name('register.verify_otp');
    Route::get('/register/otp-status', [AuthController::class, 'checkOtpStatus'])->name('register.otp_status');
    Route::post('/register/check-phone', [AuthController::class, 'checkPhone'])->name('register.check_phone');
    Route::get('/register/pending', [AuthController::class, 'showPendingApproval'])->name('register.pending');

    // Google OAuth Routes
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

// Forgot Password & Reset Routes (OTP-based, accessible by guests and logged-in users)
Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])->name('password.forgot');
Route::post('/forgot-password/send-otp', [AuthController::class, 'sendResetOtp'])->name('password.send_otp');
Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyResetOtp'])->name('password.verify_otp');
Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword'])->name('password.reset.submit');

// Logout Route
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');

// Admin portal: authenticated database accounts only.
Route::middleware('admin.demo')->prefix('admin')->name('admin.')->group(function () {
    
    // 1. Dashboard & Overview
    Route::get('/', [AdminPortalController::class, 'dashboard']);
    Route::get('/dashboard', [AdminPortalController::class, 'dashboard'])->name('dashboard');

    // 2. Seller Products Workspace & Approvals
    Route::get('/products', [AdminPortalController::class, 'products'])->name('products');
    Route::post('/products/{id}/approve', [AdminPortalController::class, 'approveProduct'])->name('products.approve');
    Route::post('/products/{id}/reject', [AdminPortalController::class, 'rejectProduct'])->name('products.reject');

    // 2b. Manage Merchant & Partner Account Registrations
    Route::get('/registrations', [AdminPortalController::class, 'registrations'])->name('registrations');
    Route::post('/registrations/{id}/status', [AdminPortalController::class, 'updateRegistrationStatus'])->name('registrations.status');

    // 2c. Manage User Government ID Checks & KYC
    Route::get('/id-checks', [AdminPortalController::class, 'idChecks'])->name('id-checks');
    Route::post('/id-checks/{id}/status', [AdminPortalController::class, 'updateIdStatus'])->name('id-checks.status');

    // 2d. Manage Logistics Partners
    Route::get('/logistics', [AdminPortalController::class, 'logistics'])->name('logistics');
    Route::post('/logistics/{id}/status', [AdminPortalController::class, 'updateLogisticsStatus'])->name('logistics.status');

        // 3. Manage User Accounts
        Route::get('/users', [AdminPortalController::class, 'users'])->name('users');
        Route::post('/users/{id}/status', [AdminPortalController::class, 'updateUserStatus'])->name('users.status');

        // 4. Monitor Seller Compliance
        Route::get('/compliance', [AdminPortalController::class, 'compliance'])->name('compliance');
        Route::post('/compliance/{id}/action', [AdminPortalController::class, 'handleComplianceAction'])->name('compliance.action');
        Route::post('/compliance/sellers/{id}/warning', [AdminPortalController::class, 'issueSellerWarning'])->name('compliance.seller.warning');
        Route::post('/compliance/sellers/{id}/suspend', [AdminPortalController::class, 'suspendSeller'])->name('compliance.seller.suspend');

        // 4b. Archive & Historical Records
        Route::get('/archive', [AdminPortalController::class, 'archive'])->name('archive');
        Route::post('/archive/products/{id}/restore', [AdminPortalController::class, 'restoreArchivedProduct'])->name('archive.products.restore');
        Route::post('/archive/products/{id}/archive', [AdminPortalController::class, 'archiveProduct'])->name('archive.products.archive');
        Route::delete('/archive/products/{id}/force', [AdminPortalController::class, 'forceDeleteProduct'])->name('archive.products.force');
        Route::post('/archive/users/{id}/restore', [AdminPortalController::class, 'restoreArchivedUser'])->name('archive.users.restore');
        Route::post('/archive/registrations/{id}/restore', [AdminPortalController::class, 'restoreArchivedRegistration'])->name('archive.registrations.restore');
        Route::post('/archive/disputes/{id}/reopen', [AdminPortalController::class, 'reopenArchivedDispute'])->name('archive.disputes.reopen');

        // 5. Manage Complaints and Disputes
          Route::get('/disputes', [AdminPortalController::class, 'disputes'])->name('disputes');
          Route::post('/disputes', [AdminPortalController::class, 'storeDispute'])->name('disputes.store');
        Route::post('/disputes/{id}/resolve', [AdminPortalController::class, 'resolveDispute'])->name('disputes.resolve');

        // 6. Manage Commission (10%)
        Route::get('/commission', [AdminPortalController::class, 'commission'])->name('commission');

        // 6b. Vouchers (platform-funded discounts and free shipping)
        Route::get('/vouchers', [\App\Http\Controllers\Admin\VoucherController::class, 'index'])->name('vouchers');
        Route::post('/vouchers', [\App\Http\Controllers\Admin\VoucherController::class, 'store'])->name('vouchers.store');
        Route::get('/vouchers/{voucher}', [\App\Http\Controllers\Admin\VoucherController::class, 'show'])->whereNumber('voucher')->name('vouchers.show');
        Route::post('/vouchers/{voucher}/update', [\App\Http\Controllers\Admin\VoucherController::class, 'update'])->whereNumber('voucher')->name('vouchers.update');
        Route::post('/vouchers/{voucher}/toggle', [\App\Http\Controllers\Admin\VoucherController::class, 'toggle'])->whereNumber('voucher')->name('vouchers.toggle');
        Route::post('/vouchers/{voucher}/assign', [\App\Http\Controllers\Admin\VoucherController::class, 'assign'])->whereNumber('voucher')->name('vouchers.assign');
        Route::delete('/vouchers/{voucher}', [\App\Http\Controllers\Admin\VoucherController::class, 'destroy'])->whereNumber('voucher')->name('vouchers.destroy');

        // 6c. Location-based shipping fees per zone (each store ships its own parcel)
        Route::get('/shipping', [\App\Http\Controllers\Admin\ShippingRateController::class, 'index'])->name('shipping');
        Route::post('/shipping', [\App\Http\Controllers\Admin\ShippingRateController::class, 'update'])->name('shipping.update');

        // 7. Generate Reports
        Route::get('/reports', [AdminPortalController::class, 'reports'])->name('reports');

        // 7b. Activity Logs (sign-in / sign-out history)
        Route::get('/activity-logs', [AdminPortalController::class, 'activityLogs'])->name('activity-logs');

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
    Route::get('/buyer/navigation-counts', \App\Http\Controllers\Buyer\NavigationCountsController::class)->middleware('role:buyer')->name('buyer.navigation-counts');
    
    // Buyer Dashboard
    Route::get('/dashboard', [BuyerDashboardController::class, 'index'])->name('buyer.dashboard');
    Route::post('/wishlist/toggle', [BuyerDashboardController::class, 'toggleWishlist'])->name('wishlist.toggle');
    Route::post('/wishlist/remove', [BuyerDashboardController::class, 'removeWishlist'])->name('wishlist.remove');
    Route::post('/reviews', [BuyerDashboardController::class, 'submitReview'])->name('reviews.store');
    Route::post('/orders/{reference}/pay', [BuyerDashboardController::class, 'payOrder'])->middleware('role:buyer')->name('orders.pay');
    Route::post('/seller-orders/{id}/cancel', [BuyerDashboardController::class, 'cancelOrder'])->name('orders.cancel');
    Route::get('/seller-orders/{id}/report', [\App\Http\Controllers\Buyer\ProblemReportController::class, 'create'])->whereNumber('id')->middleware('role:buyer')->name('orders.report');
    Route::post('/seller-orders/{id}/report', [\App\Http\Controllers\Buyer\ProblemReportController::class, 'store'])->whereNumber('id')->middleware(['role:buyer', 'throttle:10,1'])->name('orders.report.store');
    Route::get('/dispute-evidence/{dispute}/{index}', [\App\Http\Controllers\Buyer\ProblemReportController::class, 'evidence'])->whereNumber(['dispute', 'index'])->name('disputes.evidence');
    Route::post('/notifications/{id}/read', [BuyerDashboardController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [BuyerDashboardController::class, 'markAllNotificationsRead'])->name('notifications.markAllRead');
    Route::post('/notifications/{id}/archive', [BuyerDashboardController::class, 'archiveNotification'])->whereNumber('id')->name('notifications.archive');
    Route::post('/notifications/{id}/restore', [BuyerDashboardController::class, 'restoreNotification'])->whereNumber('id')->name('notifications.restore');

    // Buyer / User Account Portal
    Route::prefix('user')->name('account.')->group(function () {
        Route::get('/account', [AccountController::class, 'index'])->name('index');
        Route::get('/account/profile', [AccountController::class, 'profile'])->name('profile');
        Route::post('/account/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
        Route::post('/account/avatar', [AccountController::class, 'updateAvatar'])->name('avatar.update');
        Route::post('/account/verify-id', [AccountController::class, 'submitIdVerification'])->name('id.submit');
        Route::post('/account/password', [AccountController::class, 'updatePassword'])->name('password.update');
        Route::post('/account/address', [AccountController::class, 'updateAddress'])->name('address.update');
        Route::post('/account/addresses/save', [\App\Http\Controllers\Buyer\SettingsController::class, 'saveAddress'])->name('addresses.save');
        Route::post('/account/addresses/{address}/default', [\App\Http\Controllers\Buyer\SettingsController::class, 'defaultAddress'])->name('addresses.default');
        Route::delete('/account/addresses/{address}', [\App\Http\Controllers\Buyer\SettingsController::class, 'deleteAddress'])->name('addresses.delete');
        Route::post('/account/devices/logout', [\App\Http\Controllers\Buyer\SettingsController::class, 'logoutOtherDevices'])->middleware('throttle:6,1')->name('devices.logout');
        Route::get('/account/purchases', [AccountController::class, 'purchases'])->name('purchases');
        Route::get('/account/cards', [AccountController::class, 'cards'])->name('cards');
        Route::get('/account/addresses', [AccountController::class, 'addresses'])->name('addresses');
    });

    // Buyer Checkout Routes (ID Verification Required)
    Route::get('/checkout', [CheckoutController::class, 'index'])->middleware(\App\Http\Middleware\EnsureBuyerShopping::class)->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'process'])->middleware(\App\Http\Middleware\EnsureBuyerShopping::class)->name('checkout.process');
    Route::post('/checkout/voucher', [CheckoutController::class, 'applyCode'])->middleware([\App\Http\Middleware\EnsureBuyerShopping::class, 'throttle:20,1'])->name('checkout.voucher');
    Route::post('/vouchers/claim', [BuyerDashboardController::class, 'claimVoucher'])->middleware(['role:buyer', 'throttle:20,1'])->name('vouchers.claim');

    // Seller portal uses the authenticated account.
    Route::middleware('role:seller')->prefix('seller')->name('seller.')->group(function () {
        Route::get('/notifications', [\App\Http\Controllers\Seller\NotificationController::class, 'index'])->name('notifications');
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Seller\NotificationController::class, 'read'])->name('notifications.read');
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Seller\NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
        Route::post('/notifications/{id}/archive', [\App\Http\Controllers\Seller\NotificationController::class, 'archive'])->whereNumber('id')->name('notifications.archive');
        Route::post('/notifications/{id}/restore', [\App\Http\Controllers\Seller\NotificationController::class, 'restore'])->whereNumber('id')->name('notifications.restore');
        Route::delete('/notifications/{id}', [\App\Http\Controllers\Seller\NotificationController::class, 'destroy'])->whereNumber('id')->name('notifications.destroy');
        // 1. Dashboard Overview (Stats, Charts)
        Route::get('/', [\App\Http\Controllers\Seller\SellerPortalController::class, 'dashboard']);
        Route::get('/dashboard', [\App\Http\Controllers\Seller\SellerPortalController::class, 'dashboard'])->name('dashboard');

        // 2. Order management and packing slips
        Route::get('/orders', [\App\Http\Controllers\Seller\SellerPortalController::class, 'orders'])->name('orders');
        Route::post('/orders/{id}/pack', [\App\Http\Controllers\Seller\SellerPortalController::class, 'packOrder'])->name('orders.pack');
        Route::post('/orders/{id}/cancellation', [\App\Http\Controllers\Seller\SellerPortalController::class, 'reviewCancellation'])->name('orders.cancellation');
        Route::get('/orders/{id}/waybill', [\App\Http\Controllers\Seller\SellerPortalController::class, 'printWaybill'])->name('orders.waybill');

        // 3. Courier Handover & Shipment Tracking
        Route::get('/courier', [\App\Http\Controllers\Seller\SellerPortalController::class, 'courier'])->name('courier');
        Route::post('/courier/{id}/schedule', [\App\Http\Controllers\Seller\SellerPortalController::class, 'schedulePickup'])->name('courier.schedule');

        // 4. Recorded delivery history
        Route::get('/deliveries', [\App\Http\Controllers\Seller\SellerPortalController::class, 'deliveries'])->name('deliveries');

        // 5. Handle Customer Feedback
        Route::get('/feedback', [\App\Http\Controllers\Seller\SellerPortalController::class, 'feedback'])->name('feedback');
        Route::post('/feedback/{id}/reply', [\App\Http\Controllers\Seller\SellerPortalController::class, 'replyFeedback'])->name('feedback.reply');

        // 6. Products, variants, stock and visibility
        Route::get('/inventory', [\App\Http\Controllers\Seller\SellerPortalController::class, 'inventory'])->name('inventory');
        Route::get('/products', [\App\Http\Controllers\Seller\SellerPortalController::class, 'inventory'])->name('products');
        Route::post('/products/add', [\App\Http\Controllers\Seller\SellerPortalController::class, 'addProduct'])->name('products.add');
        Route::get('/products/{id}/sale', [\App\Http\Controllers\Seller\SellerPortalController::class, 'productSale'])->name('products.sale');
        Route::post('/products/{id}/sale', [\App\Http\Controllers\Seller\SellerPortalController::class, 'saveProductSale'])->name('products.sale.save');
        Route::post('/products/{id}/update', [\App\Http\Controllers\Seller\SellerPortalController::class, 'updateProduct'])->name('products.update');
        Route::get('/products/{id}/edit', [\App\Http\Controllers\Seller\SellerPortalController::class, 'editProduct'])->whereNumber('id')->name('products.edit');
        Route::post('/products/{id}/details', [\App\Http\Controllers\Seller\SellerPortalController::class, 'saveProductDetails'])->whereNumber('id')->name('products.details');
        Route::post('/products/{id}/options', [\App\Http\Controllers\Seller\SellerPortalController::class, 'saveProductOptions'])->whereNumber('id')->name('products.options');
        Route::delete('/products/{id}', [\App\Http\Controllers\Seller\SellerPortalController::class, 'deleteProduct'])->whereNumber('id')->name('products.delete');
        Route::post('/products/{id}/archive', [\App\Http\Controllers\Seller\SellerPortalController::class, 'toggleArchiveProduct'])->name('products.archive');
        Route::post('/inventory/{id}/stock', [\App\Http\Controllers\Seller\SellerPortalController::class, 'updateStock'])->name('inventory.stock');
        Route::post('/inventory/add', [\App\Http\Controllers\Seller\SellerPortalController::class, 'addProduct'])->name('inventory.add');
        Route::post('/inventory/{id}/update', [\App\Http\Controllers\Seller\SellerPortalController::class, 'updateProduct'])->name('inventory.update');
        Route::post('/inventory/{id}/archive', [\App\Http\Controllers\Seller\SellerPortalController::class, 'toggleArchiveProduct'])->name('inventory.archive');
        Route::get('/archive', [\App\Http\Controllers\Seller\SellerPortalController::class, 'archive'])->name('archive');
        Route::delete('/archive/{id}', [\App\Http\Controllers\Seller\SellerPortalController::class, 'deleteArchivedProduct'])->name('archive.delete');

        // 7. Paid sales reports and CSV export
        Route::get('/reports', [\App\Http\Controllers\Seller\SellerPortalController::class, 'reports'])->name('reports');

        // 7b. Shop vouchers (seller-funded, usable only on this store's items)
        Route::get('/vouchers', [\App\Http\Controllers\Seller\VoucherController::class, 'index'])->name('vouchers');
        Route::get('/vouchers/create', [\App\Http\Controllers\Seller\VoucherController::class, 'create'])->name('vouchers.create');
        Route::post('/vouchers', [\App\Http\Controllers\Seller\VoucherController::class, 'store'])->name('vouchers.store');
        Route::get('/vouchers/{voucher}/edit', [\App\Http\Controllers\Seller\VoucherController::class, 'edit'])->whereNumber('voucher')->name('vouchers.edit');
        Route::post('/vouchers/{voucher}/update', [\App\Http\Controllers\Seller\VoucherController::class, 'update'])->whereNumber('voucher')->name('vouchers.update');
        Route::post('/vouchers/{voucher}/toggle', [\App\Http\Controllers\Seller\VoucherController::class, 'toggle'])->whereNumber('voucher')->name('vouchers.toggle');
        Route::delete('/vouchers/{voucher}', [\App\Http\Controllers\Seller\VoucherController::class, 'destroy'])->whereNumber('voucher')->name('vouchers.destroy');

        // 8. Chat / Messaging
        Route::get('/chat', [\App\Http\Controllers\Seller\MessageController::class, 'index'])->name('chat');
        Route::post('/chat/{contactId}/send', [\App\Http\Controllers\Seller\MessageController::class, 'send'])->middleware('throttle:30,1')->name('chat.send');
        Route::post('/chat/inquiries/{inquiryId}/send', [\App\Http\Controllers\Seller\MessageController::class, 'sendInquiry'])->whereNumber('inquiryId')->middleware('throttle:30,1')->name('chat.inquiry.send');

        Route::get('/chat-popup', [\App\Http\Controllers\Seller\MessageController::class, 'popupConversations'])->name('popup.conversations');
        Route::get('/chat-popup/inquiries/{inquiryId}', [\App\Http\Controllers\Seller\MessageController::class, 'popupInquiry'])->whereNumber('inquiryId')->name('popup.inquiry');
        Route::get('/chat-popup/orders/{contactId}', [\App\Http\Controllers\Seller\MessageController::class, 'popupOrder'])->whereNumber('contactId')->name('popup.order');

        // 8b. Logistics hub chat (page + floating popup)
        Route::get('/logistics-chat', [\App\Http\Controllers\Seller\LogisticsChatController::class, 'index'])->name('logistics');
        Route::get('/logistics-chat/hubs', [\App\Http\Controllers\Seller\LogisticsChatController::class, 'conversations'])->name('logistics.conversations');
        Route::get('/logistics-chat/hubs/{provider}', [\App\Http\Controllers\Seller\LogisticsChatController::class, 'show'])->whereNumber('provider')->name('logistics.thread');
        Route::post('/logistics-chat/hubs/{provider}', [\App\Http\Controllers\Seller\LogisticsChatController::class, 'send'])->whereNumber('provider')->middleware('throttle:30,1')->name('logistics.send');

        // 9. Store profile and pickup address
        Route::get('/account', [\App\Http\Controllers\Seller\SellerPortalController::class, 'account'])->name('account');
        Route::post('/account/profile', [\App\Http\Controllers\Seller\SellerPortalController::class, 'updateProfile'])->name('account.profile');
        Route::post('/account/address', [\App\Http\Controllers\Seller\SellerPortalController::class, 'updateAddress'])->name('account.address');
    });

    Route::post('/chat/typing', [\App\Http\Controllers\Seller\MessageController::class, 'recordTyping'])->middleware('auth')->name('chat.typing');

    Route::middleware('role:buyer')->group(function () {
        Route::get('/buyer/messages', [\App\Http\Controllers\Seller\MessageController::class, 'index'])->name('buyer.messages');
        Route::post('/buyer/messages/{contactId}', [\App\Http\Controllers\Seller\MessageController::class, 'send'])->middleware('throttle:30,1')->name('buyer.messages.send');
        Route::post('/buyer/inquiries/{inquiryId}', [\App\Http\Controllers\Seller\MessageController::class, 'sendInquiry'])->whereNumber('inquiryId')->middleware('throttle:30,1')->name('buyer.inquiries.send');
        Route::post('/products/{productId}/message-seller', [\App\Http\Controllers\Seller\MessageController::class, 'startInquiry'])->whereNumber('productId')->middleware('throttle:20,1')->name('products.inquire');
        Route::get('/buyer/chat-popup', [\App\Http\Controllers\Seller\MessageController::class, 'popupConversations'])->name('buyer.popup.conversations');
        Route::get('/buyer/chat-popup/inquiries/{inquiryId}', [\App\Http\Controllers\Seller\MessageController::class, 'popupInquiry'])->whereNumber('inquiryId')->name('buyer.popup.inquiry');
        Route::get('/buyer/chat-popup/orders/{contactId}', [\App\Http\Controllers\Seller\MessageController::class, 'popupOrder'])->whereNumber('contactId')->name('buyer.popup.order');
        Route::post('/store/{slug}/message', [\App\Http\Controllers\Seller\MessageController::class, 'startStoreInquiry'])->middleware('throttle:20,1')->name('store.message');
    });

    // Courier / Rider Hub Routes
    Route::middleware('role:courier')->prefix('courier')->name('courier.')->group(function () {
        // 1. Dashboard Overview
        Route::get('/', [CourierDashboardController::class, 'index']);
        Route::get('/dashboard', [CourierDashboardController::class, 'index'])->name('dashboard');

        // 2. Items for Pickup & Pickup Process
        Route::get('/pickups', [CourierDashboardController::class, 'pickups'])->name('pickups');
        Route::post('/pickups/{id}/accept', [CourierDashboardController::class, 'acceptPickup'])->name('pickups.accept');

        // 3. Items for Delivery & Delivery Process
        Route::get('/deliveries', [CourierDashboardController::class, 'deliveries'])->name('deliveries');
        Route::post('/deliver/{id}', [CourierDashboardController::class, 'confirmDelivery'])->name('deliver');
        Route::post('/scan', [CourierDashboardController::class, 'scan'])->name('scan');

        // 4. Profit & Earnings Dashboard
        Route::get('/earnings', [CourierDashboardController::class, 'earnings'])->name('earnings');
        Route::post('/earnings/remit', [CourierDashboardController::class, 'recordRemittance'])->name('earnings.remit');

        // 5. Delivery History
        Route::get('/history', [CourierDashboardController::class, 'history'])->name('history');

        // 6. Chat / Messaging
        Route::get('/chat', [CourierDashboardController::class, 'chat'])->name('chat');
        Route::post('/chat/{contactId}/send', [CourierDashboardController::class, 'sendMessage'])->name('chat.send');

        // 7. Account Management
        Route::get('/account', [CourierDashboardController::class, 'account'])->name('account');
        Route::post('/account/profile', [CourierDashboardController::class, 'updateProfile'])->name('account.profile');
        Route::post('/account/toggle-status', [CourierDashboardController::class, 'toggleOnlineStatus'])->name('account.toggle-status');
    });
});

// Logistics / Sorting Center Portal Routes
Route::middleware(['logistics.demo', 'auth', 'role:logistics'])->prefix('logistics')->name('logistics.')->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\Logistics\NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Logistics\NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Logistics\NotificationController::class, 'read'])->whereNumber('id')->name('notifications.read');
    Route::post('/notifications/{id}/archive', [\App\Http\Controllers\Logistics\NotificationController::class, 'archive'])->whereNumber('id')->name('notifications.archive');
    Route::post('/notifications/{id}/restore', [\App\Http\Controllers\Logistics\NotificationController::class, 'restore'])->whereNumber('id')->name('notifications.restore');
    // 1. Dashboard
    Route::get('/', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'dashboard']);
    Route::get('/dashboard', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'dashboard'])->name('dashboard');

    // 2. Rider Management (approve/disapprove/activate/deactivate)
    Route::get('/riders', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'riders'])->name('riders');
    Route::post('/riders/{id}/status', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'updateRiderStatus'])->name('riders.status');
    Route::post('/riders/{id}/toggle', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'toggleRiderActive'])->name('riders.toggle');

    // 3. Parcel Pickup Requests (confirm/approve/verify from seller & dispatch rider)
    Route::get('/pickups', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'pickups'])->name('pickups');
    Route::post('/pickups/dispatch-all', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'dispatchAllPickups'])->name('pickups.dispatch-all');
    Route::post('/pickups/{id}/confirm',[\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'confirmPickup'])->name('pickups.confirm');
    Route::post('/pickups/{id}/assign-rider', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'assignPickupRider'])->name('pickups.assign');
    Route::post('/pickups/{id}/auto-assign', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'autoAssignPickupRider'])->name('pickups.auto-assign');
    Route::post('/pickups/{id}/unassign-rider', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'unassignPickupRider'])->name('pickups.unassign');

    // 4. Incoming Parcels Management
    Route::get('/parcels', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'parcels'])->name('parcels');
    // Hub Desk: receive, sort and assign on one page (the old pages below redirect to its tabs)
    Route::get('/hub-desk', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'hub'])->name('hub');
    Route::post('/hub-desk/process-all', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'processAll'])->name('hub.process-all');
    Route::post('/parcels/receive-all',[\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'receiveAllParcels'])->name('parcels.receive-all');
    Route::post('/parcels/{id}/receive',[\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'receiveParcel'])->name('parcels.receive');

    // 5. Sorting of Parcels
    Route::get('/sorting', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'sorting'])->name('sorting');
    Route::post('/sorting/sort-suggested', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'sortAllSuggested'])->name('sorting.sort-suggested');
    Route::post('/sorting/{id}/sort',[\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'sortParcel'])->name('sorting.sort');

    // 6. Delivery Assignment (per area and per rider)
    Route::get('/assignments', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'assignments'])->name('assignments');
    Route::post('/assignments/auto-assign', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'autoAssignDeliveries'])->name('assignments.auto-assign');
    Route::post('/assignments/{id}/assign',[\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'assignDelivery'])->name('assignments.assign');

    // 7. Delivery Monitoring
    Route::get('/monitoring', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'monitoring'])->name('monitoring');

    // 8. Generation of Reports
    Route::get('/reports', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'reports'])->name('reports');

    // 9. Chat / Messaging
    Route::get('/chat', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'chat'])->name('chat');
    Route::post('/chat/{contactId}/send', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'sendMessage'])->whereNumber('contactId')->name('chat.send');
    Route::get('/chat-popup/conversations', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'popupConversations'])->name('popup.conversations');
    Route::get('/chat-popup/messages/{contactId}', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'popupMessages'])->whereNumber('contactId')->name('popup.messages');

    // 10. Account Management
    Route::get('/account', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'account'])->name('account');
    Route::post('/account/update', [\App\Http\Controllers\Logistics\LogisticsPortalController::class, 'updateAccount'])->name('account.update');
});
