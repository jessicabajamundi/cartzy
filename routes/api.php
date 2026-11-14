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
    Route::middleware(['api.role:buyer,seller,logistics,admin', 'throttle:60,1'])->prefix('returns')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReturnController::class, 'index']);
        Route::get('/{id}/proof', [\App\Http\Controllers\ReturnController::class, 'proof'])->whereNumber('id');
        Route::post('/{id}/{action}', [\App\Http\Controllers\ReturnController::class, 'action'])->whereNumber('id');
    });

    Route::put('/rider/availability', [\App\Http\Controllers\Api\DeliveryAssignmentController::class, 'availability'])
        ->middleware(['api.role:courier', 'throttle:20,1']);
    Route::middleware(['api.role:logistics,admin', 'throttle:60,1'])->prefix('logistics')->group(function () {
        Route::get('/shipments/{id}/riders', [\App\Http\Controllers\Api\DeliveryAssignmentController::class, 'candidates'])->whereNumber('id');
        Route::post('/shipments/{id}/auto-assign', [\App\Http\Controllers\Api\DeliveryAssignmentController::class, 'autoAssign'])->whereNumber('id');
        Route::put('/shipments/{id}/rider', [\App\Http\Controllers\Api\DeliveryAssignmentController::class, 'override'])->whereNumber('id');
        Route::put('/riders/{id}/designation', [\App\Http\Controllers\Api\DeliveryAssignmentController::class, 'designation'])->whereNumber('id');
    });

    Route::middleware('api.role:courier')->prefix('rider/attendance')->group(function () {
        Route::get('/', [RiderAttendanceController::class, 'show']);
        Route::post('/scan', [RiderAttendanceController::class, 'scan'])->middleware('throttle:20,1');
    });

    // First Mile courier dashboard in the app (real data only).
    Route::get('/courier/dashboard', [\App\Http\Controllers\Api\CourierDashboardController::class, 'show'])
        ->middleware('api.role:courier');

    // Rider scans a waybill barcode or QR code (shows the parcel, changes nothing), then accepts or
    // declines it. Accepting hands the parcel to the rider and notifies seller, buyer and hub.
    Route::middleware(['api.role:courier', 'throttle:60,1'])->prefix('rider/parcels')->group(function () {
        Route::post('/scan', [\App\Http\Controllers\Api\RiderParcelController::class, 'scan']);
        Route::post('/accept', [\App\Http\Controllers\Api\RiderParcelController::class, 'accept']);
        Route::post('/decline', [\App\Http\Controllers\Api\RiderParcelController::class, 'decline']);
    });

    // Riders and couriers set their home barangay, used to match them to nearby work.
    Route::put('/rider/home-address', [\App\Http\Controllers\Api\RiderHomeAddressController::class, 'update'])
        ->middleware(['api.role:courier', 'throttle:20,1']);

    // Last Mile rider's delivery queue: list, delivered, not delivered. Saved on the server.
    Route::middleware(['api.role:courier', 'throttle:60,1'])->prefix('rider/deliveries')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\RiderDeliveryController::class, 'index']);
        Route::post('/{id}/deliver', [\App\Http\Controllers\Api\RiderDeliveryController::class, 'deliver'])->whereNumber('id');
        Route::post('/{id}/fail', [\App\Http\Controllers\Api\RiderDeliveryController::class, 'fail'])->whereNumber('id');
    });

    // Notifications: buyers and riders each see their own (the bell in the rider app too).
    Route::middleware('api.role:buyer,courier')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->whereNumber('id');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->whereNumber('id');
    });

    Route::middleware('api.role:buyer')->group(function () {
        Route::get('/addresses', [AddressController::class, 'index']);
        Route::get('/addresses/{userId}', [AddressController::class, 'index'])->whereNumber('userId');
        Route::post('/addresses', [AddressController::class, 'store']);
        Route::put('/addresses/{id}', [AddressController::class, 'update'])->whereNumber('id');
        Route::put('/addresses/{id}/default', [AddressController::class, 'setDefault'])->whereNumber('id');
        Route::delete('/addresses/{id}', [AddressController::class, 'destroy'])->whereNumber('id');

        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:10,1');
        Route::get('/orders/{id}/tracking', [OrderController::class, 'tracking']);
        Route::get('/orders/{id}/realtime-location', [OrderController::class, 'liveLocation']);
        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
        Route::post('/orders/{id}/report', [OrderController::class, 'report'])->middleware('throttle:10,1');
        Route::post('/orders/pay/{reference}', [OrderController::class, 'pay']);
        Route::post('/reviews', [OrderController::class, 'review']);

        Route::post('/stores/{identifier}/follow', [StoreController::class, 'toggleFollow'])->middleware('throttle:30,1');

        // Buyer Messages & Chat
        Route::get('/messages', [MessageController::class, 'index']);
        Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);
        Route::get('/messages/thread', [MessageController::class, 'thread']);
        Route::post('/messages/send', [MessageController::class, 'send'])->middleware('throttle:60,1');
        Route::post('/messages/inquire', [MessageController::class, 'inquire'])->middleware('throttle:20,1');

        // Buyer Wishlist
        Route::get('/wishlist', [WishlistController::class, 'index']);
        Route::get('/wishlist/refs', [WishlistController::class, 'refs']);
        Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->middleware('throttle:60,1');
        Route::delete('/wishlist/{ref}', [WishlistController::class, 'destroy'])->middleware('throttle:60,1');
    });
});

