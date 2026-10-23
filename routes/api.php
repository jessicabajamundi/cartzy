<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
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
    Route::post('/google', [AuthController::class, 'googleLogin'])->middleware('throttle:10,1');
});

Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/products', [CatalogController::class, 'products']);
Route::get('/products/{id}', [CatalogController::class, 'product'])->whereNumber('id');
Route::get('/hero-slides', [CatalogController::class, 'heroSlides']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::middleware('api.role:buyer')->group(function () {
        Route::get('/addresses', [AddressController::class, 'index']);
        Route::get('/addresses/{userId}', [AddressController::class, 'index'])->whereNumber('userId');
        Route::post('/addresses', [AddressController::class, 'store']);
        Route::put('/addresses/{id}', [AddressController::class, 'update'])->whereNumber('id');
        Route::put('/addresses/{id}/default', [AddressController::class, 'setDefault'])->whereNumber('id');
        Route::delete('/addresses/{id}', [AddressController::class, 'destroy'])->whereNumber('id');
    });
});

