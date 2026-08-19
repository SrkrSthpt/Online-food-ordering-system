<?php

declare(strict_types=1);

use App\Core\Route;
use App\Controllers\Api\v1 as Api;

// ---------------------------------------------------------------------------
// AJAX endpoints used by assets/js/script.js
// ---------------------------------------------------------------------------

Route::get('/api/cart/count', [Api\CartController::class, 'count'])->middleware(['api', 'auth'])->name('api.cart.count');
Route::post('/api/cart/add', [Api\CartController::class, 'add'])->middleware(['api', 'auth'])->name('api.cart.add');
Route::post('/api/cart/update', [Api\CartController::class, 'update'])->middleware(['api', 'auth'])->name('api.cart.update');
Route::post('/api/cart/remove', [Api\CartController::class, 'destroy'])->middleware(['api', 'auth'])->name('api.cart.remove');

Route::post('/api/orders/place', [Api\OrderController::class, 'store'])->middleware(['api', 'auth'])->name('api.orders.place');
Route::get('/api/orders/{id}/track', [Api\OrderController::class, 'track'])->middleware(['api', 'auth'])->name('api.orders.track');
Route::post('/api/payments/process', [Api\PaymentController::class, 'process'])->middleware(['api', 'auth'])->name('api.payments.process');

Route::get('/api/delivery/orders', [Api\DeliveryController::class, 'orders'])->middleware(['api', 'delivery'])->name('api.delivery.orders');
Route::post('/api/delivery/orders/{id}/accept', [Api\DeliveryController::class, 'accept'])->middleware(['api', 'delivery'])->name('api.delivery.orders.accept');
Route::post('/api/delivery/orders/{id}/status', [Api\DeliveryController::class, 'status'])->middleware(['api', 'delivery'])->name('api.delivery.orders.status');
Route::post('/api/delivery/orders/{id}/eta', [Api\DeliveryController::class, 'eta'])->middleware(['api', 'delivery'])->name('api.delivery.orders.eta');

Route::post('/api/admin/delete', [Api\AdminController::class, 'delete'])->middleware(['api', 'manager'])->name('api.admin.delete');
