<div>
    <!-- Order your soul. Reduce your wants. - Augustine -->
</div>
@php
    $userAttributes = auth()->user()?->getAttributes() ?? [];
    $role = $userAttributes['role'] ?? null;
@endphp
@if ($role === 'Admin')
    <nav class="role-menu role-menu-admin" aria-label="Chức năng Admin">
        <div class="role-menu-heading">QUẢN TRỊ HỆ THỐNG</div>
        <a class="role-menu-item" href="{{ route('admin.dashboard') }}"><span class="role-menu-icon" aria-hidden="true">▦</span><span>Tổng quan</span></a>
        <a class="role-menu-item" href="{{ route('admin.orders.index') }}"><span class="role-menu-icon" aria-hidden="true">▣</span><span>Đơn hàng</span></a>
        <a class="role-menu-item" href="{{ route('admin.users.index') }}"><span class="role-menu-icon" aria-hidden="true">♙</span><span>Người dùng</span></a>
        <a class="role-menu-item" href="{{ route('admin.reviews.index') }}"><span class="role-menu-icon" aria-hidden="true">☆</span><span>Đánh giá</span></a>
        <a class="role-menu-item" href="{{ route('admin.vouchers.index') }}"><span class="role-menu-icon" aria-hidden="true">％</span><span>Khuyến mãi</span></a>
        <a class="role-menu-item" href="{{ route('admin.reports.index') }}"><span class="role-menu-icon" aria-hidden="true">↗</span><span>Thống kê / báo cáo</span></a>
    </nav>
@elseif ($role === 'KhachHang')
    <nav class="role-menu role-menu-customer" aria-label="Chức năng khách hàng">
        <div class="role-menu-heading">MENU KHÁCH HÀNG</div>
        <a class="role-menu-item" href="{{ route('home') }}"><span class="role-menu-icon" aria-hidden="true">⌂</span><span>Trang chủ</span></a>
        <a class="role-menu-item" href="{{ route('products.index') }}"><span class="role-menu-icon" aria-hidden="true">▦</span><span>Danh sách sản phẩm</span></a>
        <a class="role-menu-item" href="{{ route('cart.index') }}"><span class="role-menu-icon" aria-hidden="true">▣</span><span>Giỏ hàng</span></a>
        <div class="role-menu-divider"></div>
        <a class="role-menu-item" href="{{ url('/dang-nhap') }}"><span class="role-menu-icon" aria-hidden="true">→</span><span>Đăng nhập</span></a>
        <a class="role-menu-item" href="{{ url('/dang-ky') }}"><span class="role-menu-icon" aria-hidden="true">+</span><span>Đăng ký</span></a>
        <a class="role-menu-item" href="{{ url('/quen-mat-khau') }}"><span class="role-menu-icon" aria-hidden="true">◇</span><span>Quên mật khẩu</span></a>
        <a class="role-menu-item" href="{{ url('/tai-khoan') }}"><span class="role-menu-icon" aria-hidden="true">♙</span><span>Thông tin cá nhân</span></a>
        <a class="role-menu-item" href="{{ url('/tai-khoan#diaChi') }}"><span class="role-menu-icon" aria-hidden="true">⌖</span><span>Địa chỉ giao hàng</span></a>
    </nav>
@endif
