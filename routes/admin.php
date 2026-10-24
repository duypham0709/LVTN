<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\UsersController;

// Quyền trang admin
Route::prefix('admin')->group(function () {

    Route::middleware('check.auth.admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
    });

    //Quyền user sau khi login
    Route::middleware('auth.custom')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.pages.dashboard');
        })->name('admin.dashboard');

        Route::get('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

        Route::middleware(['permission:manage_users'])->group(function () {
            Route::get('/users', [UsersController::class, 'index'])->name('admin.users.index');
        });
    });
});
