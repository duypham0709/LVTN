<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UsersController;
use Illuminate\Support\Facades\Route;

// Quyền trang admin
Route::prefix('admin')->group(function () {

    Route::middleware('check.auth.admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
    });

    // Quyền user sau khi login
    Route::middleware('auth.custom')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.pages.dashboard');
        })->name('admin.dashboard');

        Route::get('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

        Route::middleware(['permission:manage_users'])->group(function () {
            Route::get('/users', [UsersController::class, 'index'])->name('admin.users.index');
        });

        Route::middleware(['permission:manage_categories'])->group(function () {
            Route::get('/categories/add', [CategoryController::class, 'showFormAdd'])->name('admin.categories.add');
            Route::post('/categories/add', [CategoryController::class, 'addCategory'])->name('admin.categories.add');
            Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
            Route::post('/categories/update', [CategoryController::class, 'updateCategory']);
            Route::post('/categories/delete', [CategoryController::class, 'deleteCategory']);
        });

        Route::middleware(['permission:manage_products'])->group(function () {
            Route::get('/product/add', [ProductController::class, 'showFormAddProduct'])->name('admin.product.add');
            Route::post('/product/add', [ProductController::class, 'addProduct'])->name('admin.product.add');

            Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index');
            Route::post('/product/update', [ProductController::class, 'updateProduct']);
            Route::post('/product/delete', [ProductController::class, 'deleteProduct']);
        });
    });
});
