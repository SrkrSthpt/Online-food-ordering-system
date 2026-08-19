<?php

declare(strict_types=1);

use App\Core\Route;
use App\Controllers\Admin;
use App\Controllers\Auth;
use App\Controllers\CartController;
use App\Controllers\Customer;
use App\Controllers\DeliveryController;
use App\Controllers\HomeController;
use App\Controllers\MenuController;

// ---------------------------------------------------------------------------
// Storefront (public)
// ---------------------------------------------------------------------------

Route::get('/', [HomeController::class, 'index'])->middleware('web')->name('home');
Route::get('/menu', [MenuController::class, 'index'])->middleware('web')->name('menu.index');

// ---------------------------------------------------------------------------
// Authentication
// ---------------------------------------------------------------------------

Route::get('/login', [Auth\SessionController::class, 'create'])->middleware('guest')->name('auth.login');
Route::post('/login', [Auth\SessionController::class, 'store'])->middleware('guest')->name('auth.login.store');
Route::post('/logout', [Auth\SessionController::class, 'destroy'])->middleware('auth')->name('auth.logout');
Route::get('/register', [Auth\RegisterController::class, 'create'])->middleware('guest')->name('auth.register');
Route::post('/register', [Auth\RegisterController::class, 'store'])->middleware('guest')->name('auth.register.store');

// ---------------------------------------------------------------------------
// Authenticated customer area
// ---------------------------------------------------------------------------

Route::get('/cart', [CartController::class, 'index'])->middleware('auth')->name('cart.index');

Route::get('/delivery', [DeliveryController::class, 'index'])->middleware('delivery')->name('delivery.index');

Route::get('/orders', [Customer\OrderController::class, 'index'])->middleware('auth')->name('orders.index');
Route::get('/payment', [Customer\PaymentController::class, 'index'])->middleware('auth')->name('payment.index');
Route::get('/payment/success', [Customer\PaymentController::class, 'success'])->middleware('auth')->name('payment.success');

// ---------------------------------------------------------------------------
// Manager / admin panel
// ---------------------------------------------------------------------------

Route::prefix('admin')->middleware('manager')->group(function (): void {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/restaurants', [Admin\RestaurantController::class, 'index'])->name('admin.restaurants.index');
    Route::post('/restaurants', [Admin\RestaurantController::class, 'store'])->name('admin.restaurants.store');
    Route::post('/restaurants/update', [Admin\RestaurantController::class, 'update'])->name('admin.restaurants.update');

    Route::get('/menu-items', [Admin\MenuItemController::class, 'index'])->name('admin.menu-items.index');
    Route::post('/menu-items', [Admin\MenuItemController::class, 'store'])->name('admin.menu-items.store');
    Route::post('/menu-items/update', [Admin\MenuItemController::class, 'update'])->name('admin.menu-items.update');

    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders/notifications', [Admin\OrderController::class, 'notifications'])->name('admin.orders.notifications');
    Route::post('/orders/update', [Admin\OrderController::class, 'update'])->name('admin.orders.update');

    Route::get('/users', [Admin\UserController::class, 'index'])->middleware('admin')->name('admin.users.index');
    Route::post('/users', [Admin\UserController::class, 'store'])->middleware('admin')->name('admin.users.store');
    Route::post('/users/update', [Admin\UserController::class, 'update'])->middleware('admin')->name('admin.users.update');
});
