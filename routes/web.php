<?php

use App\Http\Controllers\AccountAddressController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\MockShipmentController;
use App\Http\Controllers\PayOSWebhookController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\ViettelPostWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/san-pham', [StorefrontController::class, 'index'])->name('products.index');
Route::get('/danh-muc/{category:slug}', [StorefrontController::class, 'category'])->name('categories.show');
Route::get('/san-pham/{product:slug}', [StorefrontController::class, 'show'])->name('products.show');
Route::post('/san-pham/{product}/thong-bao-co-hang', [StorefrontController::class, 'restock'])->middleware('throttle:5,1')->name('products.restock');
Route::get('/tim-kiem/goi-y', [StorefrontController::class, 'suggest'])->name('search.suggest');
Route::get('/sitemap.xml', [StorefrontController::class, 'sitemap'])->name('sitemap');
Route::view('/khuyen-mai', 'pages.promotions')->name('pages.promotions');
Route::view('/ho-tro', 'pages.support')->name('pages.support');

Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::post('/gio-hang/{product}', [CartController::class, 'store'])->name('cart.store');
Route::patch('/gio-hang/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/gio-hang/{item}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('/thanh-toan', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/thanh-toan', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
Route::get('/thanh-toan/mo-phong/{token}', [CheckoutController::class, 'showMockPayment'])->middleware('throttle:30,1')->name('checkout.mock-payment.show');
Route::post('/thanh-toan/mo-phong/{token}', [CheckoutController::class, 'completeMockPayment'])->middleware('throttle:10,1')->name('checkout.mock-payment.complete');
Route::post('/thanh-toan/payos/webhook', PayOSWebhookController::class)->name('checkout.payos.webhook');
Route::post('/webhooks/viettel-post/{secret}', ViettelPostWebhookController::class)->name('webhooks.viettelpost');
Route::get('/van-don/mo-phong/{shipmentNumber}', [MockShipmentController::class, 'show'])->name('shipping.mock.show');
Route::get('/don-hang/khach/{order:order_number}/{token}', [CheckoutController::class, 'showGuestOrder'])->name('checkout.guest-order.show');
Route::post('/don-hang/khach/{order:order_number}/{token}/huy', [CheckoutController::class, 'cancelGuestOrder'])->name('checkout.guest-order.cancel');
Route::get('/thanh-toan/payos/ket-qua/{token}', [CheckoutController::class, 'payosReturn'])->name('checkout.payos.return');
Route::get('/thanh-toan/payos/trang-thai/{token}', [CheckoutController::class, 'payosStatus'])->name('checkout.payos.status');
Route::get('/dat-hang-thanh-cong/{order:order_number}', [CheckoutController::class, 'success'])->name('checkout.success');

Route::middleware('guest')->group(function (): void {
    Route::get('/dang-nhap', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/dang-nhap', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/dang-ky', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/dang-ky', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/quen-mat-khau', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/quen-mat-khau', [PasswordResetLinkController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/dat-lai-mat-khau/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/dat-lai-mat-khau', [NewPasswordController::class, 'store'])->name('password.update');
});
Route::post('/dang-xuat', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('tai-khoan')->name('account.')->group(function (): void {
    Route::get('/', [AccountController::class, 'index'])->name('index');
    Route::get('/don-hang', [AccountController::class, 'orderHistory'])->name('orders.index');
    Route::get('/ho-so', [AccountController::class, 'edit'])->name('profile.edit');
    Route::put('/ho-so', [AccountController::class, 'update'])->name('profile.update');
    Route::patch('/ho-so/mat-khau', [AccountController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/dia-chi', [AccountAddressController::class, 'store'])->name('addresses.store');
    Route::patch('/dia-chi/{address}/mac-dinh', [AccountAddressController::class, 'setDefault'])->name('addresses.default');
    Route::delete('/dia-chi/{address}', [AccountAddressController::class, 'destroy'])->name('addresses.destroy');
    Route::get('/don-hang/{order:order_number}', [AccountController::class, 'showOrder'])->name('orders.show');
    Route::post('/don-hang/{order:order_number}/huy', [AccountController::class, 'cancelOrder'])->name('orders.cancel');
    Route::post('/don-hang/{order:order_number}/doi-tra', [AccountController::class, 'requestReturn'])->name('orders.return');
});

Route::post('/san-pham/{product}/danh-gia', [StorefrontController::class, 'review'])->middleware('auth')->name('reviews.store');
Route::view('/chinh-sach/doi-tra', 'pages.returns')->name('pages.returns');
Route::view('/chinh-sach/giao-hang', 'pages.shipping')->name('pages.shipping');
Route::view('/chinh-sach/bao-mat', 'pages.privacy')->name('pages.privacy');
Route::view('/lien-he', 'pages.contact')->name('pages.contact');

Route::prefix('quan-tri')->name('admin.')->middleware(['auth', 'staff'])->group(function (): void {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('san-pham', AdminProductController::class)
        ->parameters(['san-pham' => 'product'])
        ->except('show')
        ->names('products')
        ->middleware('staff:admin,sales');
    Route::get('/don-hang', [AdminOrderController::class, 'index'])->name('orders.index')->middleware('staff:admin,sales,warehouse,accountant');
    Route::get('/don-hang/{order}', [AdminOrderController::class, 'show'])->name('orders.show')->middleware('staff:admin,sales,warehouse,accountant');
    Route::post('/don-hang/{order}/xac-nhan', [AdminOrderController::class, 'confirm'])->name('orders.confirm')->middleware('staff:admin,sales');
    Route::post('/don-hang/{order}/huy', [AdminOrderController::class, 'cancel'])->name('orders.cancel')->middleware('staff:admin,sales');
    Route::post('/don-hang/{order}/van-don', [AdminOrderController::class, 'createShipment'])->name('orders.shipment')->middleware('staff:admin,sales,warehouse');
    Route::patch('/don-hang/{order}/van-chuyen', [AdminOrderController::class, 'updateStatus'])->name('orders.shipment-status')->middleware('staff:admin,sales,warehouse');
    Route::patch('/don-hang/{order}/cod', [AdminOrderController::class, 'updateCodStatus'])->name('orders.cod')->middleware('staff:admin,accountant');
    Route::patch('/don-hang/{order}/hoan-tien', [AdminOrderController::class, 'markRefunded'])->name('orders.refund')->middleware('staff:admin,accountant');
    Route::get('/doi-tra/{returnRequest}/evidence', [AdminOrderController::class, 'downloadReturnEvidence'])->name('returns.evidence')->middleware('staff:admin,sales');
    Route::patch('/doi-tra/{returnRequest}', [AdminOrderController::class, 'updateReturn'])->name('returns.update')->middleware('staff:admin,sales');
    Route::patch('/doi-tra/{returnRequest}/nhan-hang', [AdminOrderController::class, 'receiveReturn'])->name('returns.receive')->middleware('staff:admin,sales');
    Route::get('/ton-kho', [AdminInventoryController::class, 'index'])->name('inventory.index')->middleware('staff:admin,warehouse');
    Route::post('/ton-kho/{product}', [AdminInventoryController::class, 'store'])->name('inventory.store')->middleware('staff:admin,warehouse');
    Route::get('/khach-hang', [AdminUserController::class, 'index'])->name('users.index')->middleware('staff:admin');
    Route::patch('/khach-hang/{user}', [AdminUserController::class, 'update'])->name('users.update')->middleware('staff:admin');
    Route::get('/nhat-ky', [AdminAuditLogController::class, 'index'])->name('audit.index')->middleware('staff:admin');
});
