<?php

use App\Http\Controllers\Clients\AccountController;
use App\Http\Controllers\Clients\AuthController;
use App\Http\Controllers\Clients\CartController;
use App\Http\Controllers\Clients\CheckoutController;
use App\Http\Controllers\Clients\FacebookController;
use App\Http\Controllers\Clients\GoogleController;
use App\Http\Controllers\Clients\HomeController;
use App\Http\Controllers\Clients\ProductController;
use App\Http\Controllers\Clients\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/about', function () {
    return view('clients.pages.about');
})->name('about');

Route::get('/service', function () {
    return view('clients.pages.service');
})->name('service');

Route::get('/faq', function () {
    return view('clients.pages.faq');
})->name('faq');

// Quyền sử dụng của Guest
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('post-register');

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('post-login');
    Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('google.callback');
    // Route::get('/auth/facebook', [FacebookController::class, 'redirectToFacebook'])->name('facebook.redirect');
    // Route::get('/auth/facebook/callback', [FacebookController::class, 'handleFacebookCallback'])->name('facebook.callback');
});

Route::get('/activate/{token}', [AuthController::class, 'activate'])->name('activate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Quyền sử dụng khi đã login
Route::middleware('auth.custom')->group(function () {

    Route::prefix('account')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('account');

        Route::post('addresses', [AccountController::class, 'addAddress'])->name('account.addresses.add');
        Route::put('addresses/{id}', [AccountController::class, 'updatePrimaryAddress'])->name('account.addresses.update');
        Route::delete('addresses/{id}', [AccountController::class, 'deleteAddress'])->name('account.addresses.delete');
    });

    // Đơn hàng
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::get('/checkout/get-address', [CheckoutController::class, 'getAddress']);
    Route::post('/checkout', [CheckoutController::class, 'placeOrder'])->name('checkout.placeOrder');
    Route::post('/checkout/sepay/{orderId}/status', [CheckoutController::class, 'sePayStatus'])
        ->whereNumber('orderId')
        // ->middleware('throttle:30,1')
        ->name('checkout.sepay.status');
    Route::get('/checkout/sepay/{orderId}/return/{result}', [CheckoutController::class, 'sePayReturn'])
        ->where([
            'orderId' => '[0-9]+',
            'result' => 'success|error|cancel',
        ])
        ->name('checkout.sepay.return');
});

// Lọc sản phẩm
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/filter', [ProductController::class, 'filter'])->name('products.filter');

// Chi tiết sản phẩm
Route::get('/products/{categorySlug}/{productSlug}', [ProductController::class, 'detail'])
    ->name('product.detail');
Route::get('/products/{categorySlug}', [ProductController::class, 'filterByCategory'])
    ->name('products.byCategory');
// Route::get('/product/{slug}', [ProductController::class, 'legacyDetail'])
//     ->name('product.detail.legacy');

// Thêm vào giỏ hàng
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
// Xóa SP khỏi mini cart
Route::post('/cart/remove', [CartController::class, 'removeFromMiniCart'])->name('cart.remove');
// Load mini cart
Route::get('/mini-cart', [CartController::class, 'loadMiniCart'])->name('cart.mini');
// Xem giỏ hàng
Route::get('/cart', [CartController::class, 'viewCart'])->name('cart.index');
Route::post('/cart/update', [CartController::class, 'updateCart'])->name('cart.update');
Route::post('/cart/remove-cart', [CartController::class, 'removeCartItem'])->name('cart.remove');

Route::get('/search', [SearchController::class, 'index'])->name('search');

require __DIR__.'/admin.php';
