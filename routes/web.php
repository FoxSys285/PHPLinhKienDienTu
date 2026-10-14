<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::get('/san-pham/{product}', [ProductController::class, 'show'])->name('products.show');
Route::post('/san-pham/{product}/danh-gia', [ReviewController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('products.reviews.store');

Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::post('/gio-hang/them', [CartController::class, 'store'])->name('cart.store');
Route::post('/gio-hang/cap-nhat', [CartController::class, 'update'])->name('cart.update');
Route::post('/gio-hang/xoa', [CartController::class, 'destroy'])->name('cart.remove');

Route::get('/thanh-toan', [CheckoutController::class, 'create'])->name('checkout.index');
Route::post('/thanh-toan/bat-dau', [CheckoutController::class, 'start'])->name('checkout.start');
Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/don-hang/{order:order_number}/thanh-cong', [CheckoutController::class, 'success'])->name('orders.success');
