<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CustomerAddressController;
use App\Http\Controllers\Api\ExpeditionController;
use App\Http\Controllers\Api\InfoController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductReviewController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\MidtransCallbackController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // ── Auth publik (dengan rate limiting) ───────────────────────────────────
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,5');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,10');
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:5,5');
    Route::post('/auth/google', [GoogleAuthController::class, 'loginWithGoogle'])->middleware('throttle:10,1');
    Route::match(['get', 'post'], '/auth/google/callback', [GoogleAuthController::class, 'handleCallback'])->middleware('throttle:10,1');

    // ── Forgot Password (alur 3 langkah, dengan rate limiting) ─────────
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,15');
    Route::post('/verify-reset-otp', [AuthController::class, 'verifyResetOtp'])->middleware('throttle:10,10');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,10');

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/products/{product}/reviews', [ProductReviewController::class, 'index']);
    Route::get('/expeditions', [ExpeditionController::class, 'index']);
    Route::get('/about', [InfoController::class, 'about']);
    Route::get('/help', [InfoController::class, 'help']);
    Route::get('/store-info', [InfoController::class, 'storeInfo']);
    Route::get('/banners', [BannerController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/profile', [ProfileController::class, 'update']);
        Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store']);
        Route::post('/reviews/{review}/reply', [ProductReviewController::class, 'reply']);
        Route::get('/reviews/{review}/replies', [ProductReviewController::class, 'replies']);

        Route::apiResource('/addresses', CustomerAddressController::class);

        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart/add', [CartController::class, 'add']);
        Route::post('/cart/items/delete-bulk', [CartController::class, 'removeBulk']);
        Route::patch('/cart/items/{itemId}', [CartController::class, 'update']);
        Route::delete('/cart/items/{itemId}', [CartController::class, 'remove']);
        Route::delete('/cart', [CartController::class, 'clear']);

        Route::post('/checkout', [CheckoutController::class, 'store']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::post('/orders/{order}/complete', [OrderController::class, 'complete']);
        Route::post('/orders/{order}/track', [OrderController::class, 'trackWaybill']);
        Route::post('/orders/{order}/simulate-courier-pod', [OrderController::class, 'simulateCourierPod']);
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
        Route::post('/payments/{payment}/check-status', [PaymentController::class, 'checkStatus']);

        // ── Reviews ───────────────────────────────────────────────────────────
        Route::get('/my-reviews', [ProductReviewController::class, 'myReviews']);

        // ── Chat ──────────────────────────────────────────────────────────────
        Route::get('/chats', [ChatController::class, 'index']);
        Route::post('/chats', [ChatController::class, 'store']);
        Route::get('/chats/{chat}/messages', [ChatController::class, 'messages']);
        Route::post('/chats/{chat}/messages', [ChatController::class, 'sendMessage']);

        // ── Notifications ─────────────────────────────────────────────────────
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::post('/users/fcm-token', [NotificationController::class, 'updateFcmToken']);
    });

    // Public Midtrans Callback Webhook
    Route::post('/payments/midtrans-callback', [MidtransCallbackController::class, 'handle']);
});
