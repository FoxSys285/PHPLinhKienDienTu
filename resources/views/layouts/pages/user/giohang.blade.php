<div>
    <!-- Nothing in life is to be feared, it is only to be understood. Now is the time to understand more, so that we may fear less. - Maria Skłodowska-Curie -->
</div>
@extends('layouts.user')

@php
    $cartItems = $cartItems ?? [];
    $totalQuantity = $cartQuantity ?? collect($cartItems)->sum(fn ($item) => (int) data_get($item, 'quantity', 0));
@endphp

@section('title', 'Giỏ hàng')

@section('content')
    <nav class="breadcrumb" aria-label="Đường dẫn">
        <a href="{{ route('home') }}">Trang chủ</a><span>›</span><span>Giỏ hàng</span>
    </nav>

    <section class="cart-page">
        <header class="cart-heading">
            <div><span class="eyebrow">GIỎ HÀNG CỦA BẠN</span><h1>Sẵn sàng cho dự án tiếp theo?</h1><p>Kiểm tra sản phẩm và số lượng trước khi thanh toán.</p></div>
            <span class="cart-item-count">{{ $totalQuantity }} sản phẩm</span>
        </header>

        @if (session('checkout_error'))
            <div class="alert alert-warning" role="alert">{{ session('checkout_error') }}</div>
        @endif
        @if (session('cart_status'))
            <div class="alert alert-success" role="status">{{ session('cart_status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif

        @if (count($cartItems) === 0)
            <div class="empty-cart">
                <span class="empty-cart-icon" aria-hidden="true">🛒</span>
                <h2>Giỏ hàng đang trống</h2>
                <p>Khám phá linh kiện và thêm món đồ đầu tiên cho dự án của bạn.</p>
                <a class="button-primary" href="{{ route('products.index') }}">Khám phá sản phẩm <span aria-hidden="true">→</span></a>
            </div>
        @else
            <form action="{{ route('cart.update') }}" method="post" id="checkoutForm">
                @csrf
                <div class="cart-layout">
                    <section class="cart-items-panel" aria-label="Sản phẩm trong giỏ">
                        <div class="cart-panel-heading">
                            <label class="cart-select-all"><input type="checkbox" id="selectAll"> Chọn tất cả</label>
                            <span>Sản phẩm ({{ count($cartItems) }})</span>
                        </div>
                        <div class="cart-items-list">
                            @foreach ($cartItems as $item)
                                @php($product = data_get($item, 'product'))
                                @php($quantity = (int) data_get($item, 'quantity', 1))
                                @php($unitPrice = (float) data_get($product, 'price', 0))
                                @php($productId = data_get($product, 'id'))
                                <article class="cart-item" data-unit="{{ $unitPrice }}">
                                    <label class="cart-item-select" aria-label="Chọn {{ data_get($product, 'name') }}">
                                        <input type="checkbox" name="selected[]" value="{{ $productId }}" class="select-item" checked>
                                    </label>
                                    <a class="cart-product-image" href="{{ route('products.show', $productId) }}"><img src="{{ data_get($product, 'image') }}" alt="{{ data_get($product, 'name') }}"></a>
                                    <div class="cart-product-info">
                                        <span class="cart-product-category">{{ data_get($product, 'category.name', data_get($product, 'category')) }}</span>
                                        <a class="cart-product-name" href="{{ route('products.show', $productId) }}">{{ data_get($product, 'name') }}</a>
                                        <span class="cart-product-code">Mã sản phẩm: {{ data_get($product, 'sku', $productId) }}</span>
                                        <span class="cart-mobile-price">{{ data_get($product, 'formatted_price', number_format($unitPrice, 0, ',', '.') . '₫') }}</span>
                                    </div>
                                    <div class="cart-unit-price"><span>Đơn giá</span><strong>{{ data_get($product, 'formatted_price', number_format($unitPrice, 0, ',', '.') . '₫') }}</strong></div>
                                    <label class="cart-quantity-label">Số lượng
                                        <input type="number" name="quantities[{{ $productId }}]" value="{{ $quantity }}" min="1" max="{{ min(99, (int) data_get($product, 'stock', 99)) }}" class="cart-qty-input" aria-label="Số lượng {{ data_get($product, 'name') }}">
                                    </label>
                                    <div class="cart-line-total"><span>Tạm tính</span><strong class="cart-subtotal">{{ number_format($unitPrice * $quantity, 0, ',', '.') }}₫</strong></div>
                                    <button type="submit" formaction="{{ route('cart.remove') }}" name="product_id" value="{{ $productId }}" class="cart-remove" aria-label="Xóa {{ data_get($product, 'name') }} khỏi giỏ">Xóa</button>
                                </article>
                            @endforeach
                        </div>
                        <a class="cart-continue-link" href="{{ route('products.index') }}"><span aria-hidden="true">←</span> Tiếp tục mua sắm</a>
                    </section>

                    <aside class="cart-summary" aria-label="Tóm tắt đơn hàng">
                        <div class="summary-box">
                            <span class="summary-kicker">TÓM TẮT ĐƠN HÀNG</span>
                            <h2>Thanh toán</h2>
                            <div class="summary-row"><span>Sản phẩm đã chọn</span><strong id="selectedCount">0</strong></div>
                            <div class="summary-row"><span>Tạm tính</span><strong id="grandTotal">0₫</strong></div>
                            <div class="summary-row"><span>Phí giao hàng</span><span class="summary-muted">Tính ở bước tiếp theo</span></div>
                            <div class="summary-total"><span>Tổng tiền</span><strong id="selectedTotal">0₫</strong></div>
                            <button class="button-primary cart-checkout-button" type="submit" formaction="{{ route('checkout.start') }}">Tiến hành thanh toán <span aria-hidden="true">→</span></button>
                            <p class="cart-safe-note"><span aria-hidden="true">✓</span> Thông tin đơn hàng được xác nhận trước khi đặt</p>
                        </div>
                        <div class="cart-help"><span aria-hidden="true">✦</span><div><strong>Cần tư vấn linh kiện?</strong><a href="tel:19006868">Gọi 1900 6868</a></div></div>
                    </aside>
                </div>
            </form>
        @endif
    </section>
@endsection

@push('scripts')
    @if (count($cartItems) > 0)
        <script>
            (function () {
                var form = document.getElementById('checkoutForm');
                var selectAll = document.getElementById('selectAll');
                if (!form || !selectAll) {
                    return;
                }
                var rows = Array.from(form.querySelectorAll('.cart-item'));
                var formatVnd = function (value) {
                    return Math.round(value).toLocaleString('vi-VN') + '₫';
                };
                var update = function () {
                    var selectedRows = rows.filter(function (row) {
                        return row.querySelector('.select-item').checked;
                    });
                    var total = 0;
                    rows.forEach(function (row) {
                        var quantityInput = row.querySelector('.cart-qty-input');
                        var quantity = Math.max(1, parseInt(quantityInput.value, 10) || 1);
                        quantityInput.value = quantity;
                        var subtotal = parseFloat(row.dataset.unit) * quantity;
                        row.querySelector('.cart-subtotal').textContent = formatVnd(subtotal);
                        if (row.querySelector('.select-item').checked) {
                            total += subtotal;
                        }
                    });
                    document.getElementById('selectedCount').textContent = selectedRows.length;
                    document.getElementById('grandTotal').textContent = formatVnd(total);
                    document.getElementById('selectedTotal').textContent = formatVnd(total);
                    selectAll.checked = selectedRows.length === rows.length;
                    selectAll.indeterminate = selectedRows.length > 0 && selectedRows.length < rows.length;
                };
                form.addEventListener('input', update);
                form.addEventListener('change', update);
                selectAll.addEventListener('change', function () {
                    rows.forEach(function (row) {
                        row.querySelector('.select-item').checked = selectAll.checked;
                    });
                    update();
                });
                update();
            }());
        </script>
    @endif
@endpush
