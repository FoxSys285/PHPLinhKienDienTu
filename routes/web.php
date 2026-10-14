<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/dang-ky', fn () => view('layouts.pages.user.dangky'))->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
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

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/don-hang', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/don-hang/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/don-hang/{order}', [AdminOrderController::class, 'update'])->name('orders.update');

    Route::get('/nguoi-dung', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/nguoi-dung/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::patch('/nguoi-dung/{user}/trang-thai', [AdminUserController::class, 'updateStatus'])->name('users.status');

    Route::get('/danh-gia', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/danh-gia/{review}', [AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::delete('/danh-gia/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('/khuyen-mai', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::get('/khuyen-mai/tao-moi', [VoucherController::class, 'create'])->name('vouchers.create');
    Route::post('/khuyen-mai', [VoucherController::class, 'store'])->name('vouchers.store');
    Route::get('/khuyen-mai/{voucher}/sua', [VoucherController::class, 'edit'])->name('vouchers.edit');
    Route::put('/khuyen-mai/{voucher}', [VoucherController::class, 'update'])->name('vouchers.update');
    Route::delete('/khuyen-mai/{voucher}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');

    Route::get('/thong-ke', [ReportController::class, 'index'])->name('reports.index');
});

Route::post('/thanh-toan/ma-khuyen-mai', [CheckoutController::class, 'applyVoucher'])->name('checkout.voucher.apply');
Route::delete('/thanh-toan/ma-khuyen-mai', [CheckoutController::class, 'removeVoucher'])->name('checkout.voucher.remove');
