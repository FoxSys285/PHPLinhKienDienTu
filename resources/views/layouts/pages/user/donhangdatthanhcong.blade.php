@extends('layouts.user')

@section('title', 'Đặt hàng thành công')

@section('content')
    <nav class="breadcrumb" aria-label="Đường dẫn">
        <a href="{{ route('home') }}">Trang chủ</a><span>›</span><span>Đặt hàng thành công</span>
    </nav>

    <section class="empty-cart">
        <span class="empty-cart-icon" aria-hidden="true">✓</span>
        <span class="eyebrow">CẢM ƠN BẠN ĐÃ MUA SẮM</span>
        <h1>Đơn hàng đã được ghi nhận</h1>
        <p>Mã đơn hàng <strong>{{ $order->order_number }}</strong>. Tổng thanh toán: <strong>{{ number_format((float) $order->total, 0, ',', '.') }}₫</strong>.</p>
        <p>Nhân viên Mạch Việt sẽ liên hệ để xác nhận thông tin giao hàng.</p>
        <a class="button-primary" href="{{ route('products.index') }}">Tiếp tục mua sắm <span aria-hidden="true">→</span></a>
    </section>
@endsection
