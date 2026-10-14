@extends('layouts.admin')

@section('title', 'Chi tiết đơn hàng')

@section('content')
    @php
        $statusLabels = ['pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'shipping' => 'Đang giao', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'];
        $paymentLabels = ['unpaid' => 'Chưa thanh toán', 'awaiting_transfer' => 'Chờ chuyển khoản', 'paid' => 'Đã thanh toán'];
        $transitions = ['pending' => ['confirmed', 'cancelled'], 'confirmed' => ['shipping', 'cancelled'], 'shipping' => ['completed'], 'completed' => [], 'cancelled' => []];
        $availableStatuses = array_values(array_unique([$order->status, ...($transitions[$order->status] ?? [])]));
    @endphp
    <a class="admin-back-link" href="{{ route('admin.orders.index') }}"><span aria-hidden="true">←</span> Danh sách đơn hàng</a>
    <div class="admin-page-heading"><div><span class="admin-eyebrow">CHI TIẾT ĐƠN HÀNG</span><h1>{{ $order->order_number }}</h1><p>Đặt lúc {{ $order->created_at?->format('d/m/Y H:i') }}</p></div><span class="admin-status {{ $order->status === 'completed' ? 'admin-status-success' : ($order->status === 'cancelled' ? 'admin-status-danger' : 'admin-status-warning') }}">{{ $statusLabels[$order->status] ?? $order->status }}</span></div>

    <div class="admin-detail-grid">
        <div>
            <section class="admin-panel">
                <div class="admin-panel-header"><div><h2>Sản phẩm trong đơn</h2><p>{{ $order->items->sum('quantity') }} sản phẩm</p></div></div>
                <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Sản phẩm</th><th>SKU</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th></tr></thead><tbody>
                    @foreach ($order->items as $item)
                        <tr><td><strong>{{ $item->product_name }}</strong></td><td>{{ $item->sku ?: '—' }}</td><td>{{ number_format((float) $item->unit_price, 0, ',', '.') }}₫</td><td>{{ $item->quantity }}</td><td><strong>{{ number_format((float) $item->line_total, 0, ',', '.') }}₫</strong></td></tr>
                    @endforeach
                </tbody></table></div>
                <div class="admin-panel-body" style="max-width:360px;margin-left:auto">
                    <div class="admin-summary-total"><span>Tiền hàng</span><strong>{{ number_format((float) $order->subtotal, 0, ',', '.') }}₫</strong></div>
                    @if ($order->voucher_code)<div class="admin-summary-total"><span>Mã {{ $order->voucher_code }}</span><strong>−{{ number_format((float) $order->discount_amount, 0, ',', '.') }}₫</strong></div>@endif
                    <div class="admin-summary-total"><span>Phí giao hàng</span><strong>{{ number_format((float) $order->shipping_fee, 0, ',', '.') }}₫</strong></div>
                    <div class="admin-summary-total"><span>Tổng thanh toán</span><strong>{{ number_format((float) $order->total, 0, ',', '.') }}₫</strong></div>
                </div>
            </section>
            <section class="admin-panel">
                <div class="admin-panel-header"><div><h2>Thông tin giao hàng</h2></div></div>
                <div class="admin-panel-body"><dl class="admin-info-list">
                    <div><dt>Người nhận</dt><dd>{{ $order->customer_name }}</dd></div>
                    <div><dt>Số điện thoại</dt><dd>{{ $order->customer_phone }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $order->customer_email }}</dd></div>
                    <div><dt>Phương thức thanh toán</dt><dd>{{ $order->payment_method === 'cod' ? 'Thanh toán khi nhận hàng (COD)' : 'Chuyển khoản' }}</dd></div>
                    <div style="grid-column:1/-1"><dt>Địa chỉ</dt><dd>{{ $order->shipping_address }}</dd></div>
                    @if ($order->note)<div style="grid-column:1/-1"><dt>Ghi chú</dt><dd>{{ $order->note }}</dd></div>@endif
                </dl></div>
            </section>
        </div>

        <section class="admin-panel">
            <div class="admin-panel-header"><div><h2>Cập nhật đơn</h2><p>Chỉ chuyển theo tiến trình xử lý hợp lệ.</p></div></div>
            <div class="admin-panel-body">
                <form method="post" action="{{ route('admin.orders.update', $order) }}">
                    @csrf
                    @method('PATCH')
                    <div class="admin-field" style="margin-bottom:15px"><label for="orderStatus">Trạng thái đơn</label><select id="orderStatus" name="status" required>@foreach ($availableStatuses as $status)<option value="{{ $status }}" @selected(old('status', $order->status) === $status)>{{ $statusLabels[$status] ?? $status }}</option>@endforeach</select></div>
                    <div class="admin-field" style="margin-bottom:17px"><label for="paymentStatus">Thanh toán</label><select id="paymentStatus" name="payment_status">@foreach ($paymentStatuses as $status)<option value="{{ $status }}" @selected(old('payment_status', $order->payment_status) === $status)>{{ $paymentLabels[$status] ?? $status }}</option>@endforeach</select></div>
                    @if ($order->status === 'pending' && $order->payment_method === 'cod')<p class="admin-page-heading"><small>Hủy đơn sẽ hoàn số lượng sản phẩm về kho. Đơn COD được đánh dấu đã thanh toán khi hoàn thành.</small></p>@endif
                    <button class="admin-button" type="submit">Lưu cập nhật</button>
                </form>
            </div>
        </section>
    </div>
@endsection
