@extends('layouts.admin')

@section('title', 'Tổng quan')

@section('content')
    <div class="admin-page-heading">
        <div><span class="admin-eyebrow">MẠCH VIỆT · QUẢN TRỊ</span><h1>Tổng quan</h1><p>Tình hình đơn hàng và hoạt động cửa hàng.</p></div>
        <a class="admin-button" href="{{ route('admin.reports.index') }}">Xem báo cáo <span aria-hidden="true">→</span></a>
    </div>

    <div class="admin-stats-grid">
        <article class="admin-stat-card"><span>Đơn mới hôm nay</span><strong>{{ number_format($stats['orders_today']) }}</strong><small>Đơn tạo trong ngày</small></article>
        <article class="admin-stat-card"><span>Đơn đang xử lý</span><strong>{{ number_format($stats['pending_orders']) }}</strong><small>Chờ xác nhận, đang giao</small></article>
        <article class="admin-stat-card"><span>Khách hàng</span><strong>{{ number_format($stats['customers']) }}</strong><small>Tài khoản không thuộc Admin</small></article>
        <article class="admin-stat-card"><span>Doanh thu đã thu</span><strong>{{ number_format((float) $stats['revenue'], 0, ',', '.') }}₫</strong><small>Đơn hoàn thành, đã thanh toán</small></article>
    </div>

    <section class="admin-panel">
        <div class="admin-panel-header"><div><h2>Đơn hàng gần đây</h2><p>Những đơn được tạo gần nhất.</p></div><a class="admin-button-link" href="{{ route('admin.orders.index') }}">Tất cả đơn hàng</a></div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Trạng thái</th><th></th></tr></thead>
                <tbody>
                    @forelse ($latestOrders as $order)
                        @php($statusLabels = ['pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'shipping' => 'Đang giao', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'])
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong></td>
                            <td>{{ $order->customer_name }}</td>
                            <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                            <td><strong>{{ number_format((float) $order->total, 0, ',', '.') }}₫</strong></td>
                            <td><span class="admin-status {{ in_array($order->status, ['completed'], true) ? 'admin-status-success' : (in_array($order->status, ['cancelled'], true) ? 'admin-status-danger' : 'admin-status-warning') }}">{{ $statusLabels[$order->status] ?? $order->status }}</span></td>
                            <td><a class="admin-button-link" href="{{ route('admin.orders.show', $order) }}">Chi tiết</a></td>
                        </tr>
                    @empty
                        <tr><td class="admin-empty" colspan="6">Chưa có đơn hàng.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel-header"><div><h2>Sắp hết hàng</h2><p>Sản phẩm còn tối đa 5 đơn vị trong kho.</p></div><a class="admin-button-link" href="{{ route('admin.reviews.index') }}">{{ number_format($stats['pending_reviews']) }} đánh giá chờ duyệt</a></div>
        <div class="admin-table-wrap">
            <table class="admin-table"><thead><tr><th>Sản phẩm</th><th>Mã SKU</th><th>Tồn kho</th></tr></thead><tbody>
                @forelse ($lowStockProducts as $product)
                    <tr><td><strong>{{ $product->name }}</strong></td><td>{{ $product->sku }}</td><td><span class="admin-status admin-status-danger">{{ $product->stock }} còn lại</span></td></tr>
                @empty
                    <tr><td class="admin-empty" colspan="3">Kho hiện không có sản phẩm sắp hết.</td></tr>
                @endforelse
            </tbody></table>
        </div>
    </section>
@endsection
