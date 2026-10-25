<?php

use App\Http\Controllers\Clients\AccountController;
use App\Http\Controllers\Clients\AuthController;
use App\Http\Controllers\Clients\HomeController;
use App\Http\Controllers\Clients\ProductController;
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
});

Route::get('/activate/{token}', [AuthController::class, 'activate'])->name('activate');

// Quyền sử dụng khi đã login
Route::middleware('auth.custom')->group(function () {
    Route::get('/logout', function () {
        return redirect()->route('home');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('account')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('account');
    });
});

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/filter', [ProductController::class, 'filter'])->name('products.filter');

// Chi tiết sản phẩm
Route::get('/product/{slug}', [ProductController::class, 'detail'])->name('product.detail');

require __DIR__ . '/admin.php';
