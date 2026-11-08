<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RiderAttendanceController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\BuddyController;
use Illuminate\Support\Facades\Route;

/*
| Cartzy mobile app API (prefix: /api). Uses the same database and accounts as the website.
| Authenticated routes expect "Authorization: Bearer <token>" from POST /api/auth/login.
*/

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/otp/request', [AuthController::class, 'requestOtp'])->middleware('throttle:5,1');
    Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
    Route::post('/register/buyer', [AuthController::class, 'registerBuyer'])->middleware('throttle:5,1');
    Route::post('/register/rider', [AuthController::class, 'registerRider'])->middleware('throttle:5,1');
    Route::post('/password/forgot', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('/password/verify', [AuthController::class, 'verifyResetCode'])->middleware('throttle:10,1');
    Route::post('/password/reset', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
    Route::post('/google',[AuthController::class, 'googleLogin'])->middleware('throttle:10,1');
});

Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/products', [CatalogController::class, 'products']);
Route::get('/products/{id}', [CatalogController::class, 'product'])->whereNumber('id');
Route::get('/hero-slides', [CatalogController::class, 'heroSlides']);
Route::get('/stores/{identifier}', [StoreController::class, 'show']);

// Cozy the teddy bear chatbot: open to guests; a Bearer token unlocks the buyer's own orders.
Route::post('/buddy/chat', [BuddyController::class, 'chat'])->middleware('throttle:buddy');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
    Route::post('/account/password', [AuthController::class, 'changePassword']);
    Route::get('/account/activity', [ActivityController::class, 'index']);

    // Rider time in / time out: scan the QR code shown on the hub's Time in / time out page.
    Route::middleware('api.role:courier')->prefix('rider/attendance')->group(function () {
        Route::get('/', [RiderAttendanceController::class, 'show']);
        Route::post('/scan', [RiderAttendanceController::class, 'scan'])->middleware('throttle:20,1');
    });

    // Rider scans a waybill barcode or QR code to take the parcel; seller, buyer and hub are notified.
    Route::post('/rider/parcels/scan', [\App\Http\Controllers\Api\RiderParcelController::class, 'scan'])
        ->middleware(['api.role:courier', 'throttle:60,1']);

    Route::middleware('api.role:buyer')->group(function () {
        Route::get('/addresses', [AddressController::class, 'index']);
        Route::get('/addresses/{userId}', [AddressController::class, 'index'])->whereNumber('userId');
        Route::post('/addresses', [AddressController::class, 'store']);
        Route::put('/addresses/{id}', [AddressController::class, 'update'])->whereNumber('id');
        Route::put('/addresses/{id}/default', [AddressController::class, 'setDefault'])->whereNumber('id');
        Route::delete('/addresses/{id}', [AddressController::class, 'destroy'])->whereNumber('id');

        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:10,1');
        Route::get('/orders/{id}/tracking', [OrderController::class, 'tracking'])->whereNumber('id');
        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel'])->whereNumber('id');
        Route::post('/orders/{id}/report', [OrderController::class, 'report'])->whereNumber('id')->middleware('throttle:10,1');
        Route::post('/orders/pay/{reference}', [OrderController::class, 'pay']);
        Route::post('/reviews', [OrderController::class, 'review']);

        Route::post('/stores/{identifier}/follow', [StoreController::class, 'toggleFollow'])->middleware('throttle:30,1');

        // Buyer Messages & Chat
        Route::get('/messages', [MessageController::class, 'index']);
        Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);
        Route::get('/messages/thread', [MessageController::class, 'thread']);
        Route::post('/messages/send', [MessageController::class, 'send'])->middleware('throttle:60,1');
        Route::post('/messages/inquire', [MessageController::class, 'inquire'])->middleware('throttle:20,1');

        // Buyer Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->whereNumber('id');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->whereNumber('id');

        // Buyer Wishlist
        Route::get('/wishlist', [WishlistController::class, 'index']);
        Route::get('/wishlist/refs', [WishlistController::class, 'refs']);
        Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->middleware('throttle:60,1');
        Route::delete('/wishlist/{ref}', [WishlistController::class, 'destroy'])->middleware('throttle:60,1');
    });
});

