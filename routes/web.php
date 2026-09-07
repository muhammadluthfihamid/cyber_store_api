<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpeditionController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockMovementController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProductReviewController;
use Illuminate\Support\Facades\Route;

// Redirect root ke admin
Route::get('/', fn () => redirect()->route('admin.dashboard'));

// ─── Admin Auth (tanpa middleware) ───────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login',   [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',  [AuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ─── Admin Panel (dengan middleware admin) ───────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Users Management ──────────────────────────────────────────────────────
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'edit'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->name('users.destroy');

    // ── Categories ────────────────────────────────────────────────────────────
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/suggestions', [CategoryController::class, 'suggestions'])->name('categories.suggestions');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::patch('/categories/{category}/toggle', [CategoryController::class, 'toggleActive'])->name('categories.toggle');

    // ── Products ──────────────────────────────────────────────────────────────
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/suggestions', [ProductController::class, 'suggestions'])->name('products.suggestions');
    Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
    Route::get('/products/import-template', [ProductController::class, 'downloadTemplate'])->name('products.import-template');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/bulk-delete', [ProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::patch('/products/{product}/toggle', [ProductController::class, 'toggleActive'])->name('products.toggle');
    // ── Banners ──────────────────────────────────────────────────────────────
    Route::resource('banners', App\Http\Controllers\Admin\BannerController::class)->names('banners');
    // ── Expeditions ───────────────────────────────────────────────────────────
    Route::get('/expeditions', [ExpeditionController::class, 'index'])->name('expeditions.index');
    Route::get('/expeditions/create', [ExpeditionController::class, 'create'])->name('expeditions.create');
    Route::post('/expeditions', [ExpeditionController::class, 'store'])->name('expeditions.store');
    Route::get('/expeditions/{expedition}/edit', [ExpeditionController::class, 'edit'])->name('expeditions.edit');
    Route::put('/expeditions/{expedition}', [ExpeditionController::class, 'update'])->name('expeditions.update');
    Route::delete('/expeditions/{expedition}', [ExpeditionController::class, 'destroy'])->name('expeditions.destroy');
    Route::patch('/expeditions/{expedition}/toggle', [ExpeditionController::class, 'toggleActive'])->name('expeditions.toggle');

    // ── Orders & Notifications ────────────────────────────────────────────────
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/suggestions', [OrderController::class, 'suggestions'])->name('orders.suggestions');
    Route::get('/orders-unread-count', [OrderController::class, 'unreadCount'])->name('orders.unread-count');
    Route::get('/all-unread-counts', [OrderController::class, 'allUnreadCounts'])->name('all-unread-counts');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('/orders/{order}/cancel-approve', [OrderController::class, 'approveCancel'])->name('orders.cancel-approve');
    Route::patch('/orders/{order}/cancel-reject', [OrderController::class, 'rejectCancel'])->name('orders.cancel-reject');
    Route::patch('/orders/{order}/resi', [OrderController::class, 'updateResi'])->name('orders.resi');
    Route::post('/orders/{order}/track', [OrderController::class, 'trackWaybill'])->name('orders.track');
    Route::post('/orders/{order}/simulate-pod', [OrderController::class, 'simulateCourierPod'])->name('orders.simulate-pod');

    // ── Payments ──────────────────────────────────────────────────────────────
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/suggestions', [PaymentController::class, 'suggestions'])->name('payments.suggestions');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');

    // ── Settings ──────────────────────────────────────────────────────────────
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    // ── Stock Movements ───────────────────────────────────────────────────────
    Route::get('/stock-movements/pdf', [StockMovementController::class, 'downloadPdf'])->name('stock-movements.pdf');
    Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');
    Route::post('/stock-movements', [StockMovementController::class, 'store'])->name('stock-movements.store');

    // ── Chat Customer ────────────────────────────────────────────────────────
    Route::get('/chats', [ChatController::class, 'index'])->name('chats.index');
    Route::get('/chats/suggestions', [ChatController::class, 'suggestions'])->name('chats.suggestions');
    Route::get('/chats/{chat}', [ChatController::class, 'show'])->name('chats.show');
    Route::post('/chats/{chat}/reply', [ChatController::class, 'reply'])->name('chats.reply');
    Route::post('/chats/{chat}/close', [ChatController::class, 'close'])->name('chats.close');
    Route::post('/chats/{chat}/reopen', [ChatController::class, 'reopen'])->name('chats.reopen');
    Route::get('/chats-unread-count', [ChatController::class, 'unreadCount'])->name('chats.unread-count');

    // ── Review Chats ─────────────────────────────────────────────────────────
    Route::get('/review-chats', [ProductReviewController::class, 'index'])->name('review-chats.index');
    Route::get('/review-chats/suggestions', [ProductReviewController::class, 'suggestions'])->name('review-chats.suggestions');
    Route::get('/review-chats-unread-count', [ProductReviewController::class, 'unreadCount'])->name('review-chats.unread-count');
    Route::get('/review-chats/{review}', [ProductReviewController::class, 'show'])->name('review-chats.show');
    Route::post('/review-chats/{review}/reply', [ProductReviewController::class, 'reply'])->name('review-chats.reply');

    // ── Announcements / Push Notifications ───────────────────────────────────
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

    // ── Cache Management ─────────────────────────────────────────────────────
    Route::post('/cache/flush-products', function () {
        ProductController::flushRedisCache();
        return back()->with('success', '✅ Cache produk di Redis berhasil dibersihkan.');
    })->name('cache.flush-products');

    Route::post('/cache/flush-categories', function () {
        CategoryController::flushRedisCache();
        return back()->with('success', '✅ Cache kategori di Redis berhasil dibersihkan.');
    })->name('cache.flush-categories');

    Route::post('/cache/flush-orders', function () {
        OrderController::flushRedisCache();
        return back()->with('success', '✅ Cache pesanan di Redis berhasil dibersihkan.');
    })->name('cache.flush-orders');

    Route::post('/cache/flush-payments', function () {
        PaymentController::flushRedisCache();
        return back()->with('success', '✅ Cache pembayaran di Redis berhasil dibersihkan.');
    })->name('cache.flush-payments');

    Route::post('/cache/flush-chats', function () {
        ChatController::flushRedisCache();
        return back()->with('success', '✅ Cache chat customer di Redis berhasil dibersihkan.');
    })->name('cache.flush-chats');

    Route::post('/cache/flush-review-chats', function () {
        ProductReviewController::flushRedisCache();
        return back()->with('success', '✅ Cache ulasan produk di Redis berhasil dibersihkan.');
    })->name('cache.flush-review-chats');
});


// Rute verifikasi email via signed URL
Route::get('/verify-email/{id}/{hash}', function ($id, $hash) {
    try {
        $userId = is_numeric($id) ? $id : \Illuminate\Support\Facades\Crypt::decryptString(urldecode((string) $id));
    } catch (\Exception $e) {
        $userId = $id;
    }

    $user = \App\Models\User::findOrFail($userId);

    if (! hash_equals(sha1($user->email), (string) $hash)) {
        abort(403, 'Email hash signature mismatched.');
    }

    $user->update([
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    return view('auth.verified', ['name' => $user->name]);
})->middleware(['signed'])->name('verification.verify');
