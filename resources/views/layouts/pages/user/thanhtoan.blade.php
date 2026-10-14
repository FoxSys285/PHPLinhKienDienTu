@extends('layouts.user')

@section('title', 'Thanh toán')

@section('content')
    <nav class="breadcrumb" aria-label="Đường dẫn">
        <a href="{{ route('home') }}">Trang chủ</a><span>›</span><a href="{{ route('cart.index') }}">Giỏ hàng</a><span>›</span><span>Thanh toán</span>
    </nav>

    <section class="cart-page">
        <header class="cart-heading">
            <div><span class="eyebrow">HOÀN TẤT ĐƠN HÀNG</span><h1>Thông tin giao hàng</h1><p>Nhập thông tin nhận hàng và chọn phương thức thanh toán.</p></div>
            <span class="cart-item-count">{{ count($cartItems) }} sản phẩm</span>
        </header>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif

        <form id="checkoutForm" action="{{ route('checkout.store') }}" method="post">
            @csrf
            <div class="cart-layout">
                <div>
                    <section class="cart-items-panel mb-3" aria-label="Thông tin nhận hàng">
                        <div class="cart-panel-heading"><strong>Người nhận hàng</strong><span>Thông tin liên hệ</span></div>
                        <div class="p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="customerName">Họ và tên</label>
                                    <input class="form-control" id="customerName" name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}" maxlength="120" autocomplete="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="customerPhone">Số điện thoại</label>
                                    <input class="form-control" id="customerPhone" name="customer_phone" value="{{ old('customer_phone') }}" maxlength="40" autocomplete="tel" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="customerEmail">Email</label>
                                    <input class="form-control" id="customerEmail" name="customer_email" type="email" value="{{ old('customer_email', auth()->user()?->email) }}" maxlength="255" autocomplete="email" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="shippingAddress">Địa chỉ giao hàng</label>
                                    <textarea class="form-control" id="shippingAddress" name="shipping_address" rows="3" minlength="10" maxlength="1000" autocomplete="street-address" required>{{ old('shipping_address') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="orderNote">Ghi chú đơn hàng <span class="text-muted">(không bắt buộc)</span></label>
                                    <textarea class="form-control" id="orderNote" name="note" rows="2" maxlength="1000">{{ old('note') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="cart-items-panel" aria-label="Sản phẩm thanh toán">
                        <div class="cart-panel-heading"><strong>Sản phẩm trong đơn</strong><span>{{ count($cartItems) }} mặt hàng</span></div>
                        <div class="cart-items-list">
                            @foreach ($cartItems as $item)
                                @php($product = $item->product)
                                <input type="hidden" name="selected[]" value="{{ $product->id }}">
                                <input type="hidden" name="quantities[{{ $product->id }}]" value="{{ $item->quantity }}">
                                <article class="cart-item">
                                    <span class="cart-item-select" aria-hidden="true">✓</span>
                                    <a class="cart-product-image" href="{{ route('products.show', $product) }}"><img src="{{ $product->image }}" alt="{{ $product->name }}"></a>
                                    <div class="cart-product-info">
                                        <span class="cart-product-category">{{ $product->category?->name }}</span>
                                        <a class="cart-product-name" href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
                                        <span class="cart-product-code">Số lượng: {{ $item->quantity }}</span>
                                    </div>
                                    <div class="cart-unit-price"><span>Đơn giá</span><strong>{{ $product->formatted_price }}</strong></div>
                                    <div class="cart-line-total"><span>Tạm tính</span><strong>{{ number_format((float) $product->price * $item->quantity, 0, ',', '.') }}₫</strong></div>
                                </article>
                            @endforeach
                        </div>
                        <a class="cart-continue-link" href="{{ route('cart.index') }}"><span aria-hidden="true">←</span> Quay lại giỏ hàng</a>
                    </section>
                </div>

            </form>

                <aside class="cart-summary" aria-label="Tóm tắt đơn hàng">
                    <div class="summary-box">
                        <span class="summary-kicker">TÓM TẮT ĐƠN HÀNG</span>
                        <h2>Thanh toán</h2>
                        <form action="{{ route('checkout.voucher.apply') }}" method="post" class="mb-3">
                            @csrf
                            <label class="form-label" for="voucherCode">Mã khuyến mãi</label>
                            <div class="d-flex gap-2">
                                <input class="form-control" id="voucherCode" name="code" value="{{ old('code', $voucher?->code) }}" maxlength="40" placeholder="Nhập mã giảm giá">
                                <button class="button-outline" type="submit">Áp dụng</button>
                            </div>
                        </form>
                        @if ($voucher)
                            <div class="summary-row"><span>Mã {{ $voucher->code }}</span><strong>−{{ number_format($discountAmount, 0, ',', '.') }}₫</strong></div>
                            <form action="{{ route('checkout.voucher.remove') }}" method="post" class="mb-3">
                                @csrf
                                @method('DELETE')
                                <button class="button-outline" type="submit">Gỡ mã</button>
                            </form>
                        @endif
                        <div class="summary-row"><span>Tạm tính</span><strong>{{ number_format($subtotal, 0, ',', '.') }}₫</strong></div>
                        <div class="summary-row"><span>Phí giao hàng</span><strong>{{ $shippingFee === 0 ? 'Miễn phí' : number_format($shippingFee, 0, ',', '.') . '₫' }}</strong></div>
                        <div class="summary-total"><span>Tổng tiền</span><strong>{{ number_format($total, 0, ',', '.') }}₫</strong></div>
                        <label class="form-label" for="paymentMethod">Phương thức thanh toán</label>
                        <select class="form-select mb-3" id="paymentMethod" name="payment_method" form="checkoutForm" required>
                            <option value="cod" @selected(old('payment_method', 'cod') === 'cod')>Thanh toán khi nhận hàng (COD)</option>
                            <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Chuyển khoản sau khi xác nhận</option>
                        </select>
                        <button class="button-primary cart-checkout-button" type="submit" form="checkoutForm">Đặt hàng <span aria-hidden="true">→</span></button>
                        <p class="cart-safe-note"><span aria-hidden="true">✓</span> Đơn hàng sẽ được xác nhận trước khi giao.</p>
                    </div>
                    <div class="cart-help"><span aria-hidden="true">✦</span><div><strong>Cần tư vấn linh kiện?</strong><a href="tel:19006868">Gọi 1900 6868</a></div></div>
                </aside>
            </div>
    </section>
@endsection
