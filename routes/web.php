<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'layouts.pages.user.trangchu')->name('home');
Route::view('/san-pham', 'layouts.pages.user.danhsachsanpham')->name('products.index');
Route::view('/san-pham/{productId}', 'layouts.pages.user.chitietsanpham')->name('products.show');
Route::view('/gio-hang', 'layouts.pages.user.giohang')->name('cart.index');
