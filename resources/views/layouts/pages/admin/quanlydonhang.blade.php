@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')

@section('content')
    @php($statusLabels = ['pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'shipping' => 'Đang giao', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'])
    <div class="admin-page-heading"><div><span class="admin-eyebrow">BÁN HÀNG</span><h1>Quản lý đơn hàng</h1><p>Tìm kiếm đơn, xem sản phẩm và cập nhật tiến trình xử lý.</p></div></div>

    <section class="admin-panel">
        <form class="admin-toolbar" method="get" action="{{ route('admin.orders.index') }}">
            <div class="admin-field" style="flex:1 1 220px"><label for="orderSearch">Tìm đơn hàng</label><input id="orderSearch" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Mã đơn, tên, email, điện thoại"></div>
            <div class="admin-field"><label for="orderStatus">Trạng thái</label><select id="orderStatus" name="status"><option value="">Tất cả trạng thái</option>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="admin-field"><label for="orderFrom">Từ ngày</label><input id="orderFrom" type="date" name="from" value="{{ $filters['from'] ?? '' }}"></div>
            <div class="admin-field"><label for="orderTo">Đến ngày</label><input id="orderTo" type="date" name="to" value="{{ $filters['to'] ?? '' }}"></div>
            <button class="admin-button" type="submit">Lọc đơn</button>
            @if (count($filters))<a class="admin-button-secondary" href="{{ route('admin.orders.index') }}">Xóa lọc</a>@endif
        </form>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Ngày đặt</th><th>Thanh toán</th><th>Tổng tiền</th><th>Trạng thái</th><th></th></tr></thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong></td>
                            <td><strong>{{ $order->customer_name }}</strong><small>{{ $order->customer_phone }}</small></td>
                            <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ ['unpaid' => 'Chưa thanh toán', 'awaiting_transfer' => 'Chờ chuyển khoản', 'paid' => 'Đã thanh toán'][$order->payment_status] ?? $order->payment_status }}</td>
                            <td><strong>{{ number_format((float) $order->total, 0, ',', '.') }}₫</strong></td>
                            <td><span class="admin-status {{ $order->status === 'completed' ? 'admin-status-success' : ($order->status === 'cancelled' ? 'admin-status-danger' : 'admin-status-warning') }}">{{ $statusLabels[$order->status] ?? $order->status }}</span></td>
                            <td><a class="admin-button-link" href="{{ route('admin.orders.show', $order) }}">Xem chi tiết</a></td>
                        </tr>
                    @empty
                        <tr><td class="admin-empty" colspan="7">Không tìm thấy đơn hàng phù hợp.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())<div class="admin-pagination">{{ $orders->links() }}</div>@endif
    </section>
@endsection
