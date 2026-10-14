@extends('layouts.admin')

@section('title', 'Chi tiết người dùng')

@section('content')
    <a class="admin-back-link" href="{{ route('admin.users.index') }}"><span aria-hidden="true">←</span> Danh sách người dùng</a>
    <div class="admin-page-heading"><div><span class="admin-eyebrow">HỒ SƠ KHÁCH HÀNG</span><h1>{{ $user->name }}</h1><p>{{ $user->email }}</p></div><span class="admin-status {{ $user->is_blocked ? 'admin-status-danger' : 'admin-status-success' }}">{{ $user->is_blocked ? 'Đã khóa' : 'Hoạt động' }}</span></div>
    <div class="admin-detail-grid">
        <section class="admin-panel"><div class="admin-panel-header"><div><h2>Thông tin tài khoản</h2></div></div><div class="admin-panel-body"><dl class="admin-info-list">
            <div><dt>Họ tên</dt><dd>{{ $user->name }}</dd></div><div><dt>Email</dt><dd>{{ $user->email }}</dd></div><div><dt>Ngày đăng ký</dt><dd>{{ $user->created_at?->format('d/m/Y H:i') }}</dd></div><div><dt>Tổng số đơn</dt><dd>{{ $user->orders_count }}</dd></div>
            @if ($user->is_blocked)<div style="grid-column:1/-1"><dt>Lý do khóa</dt><dd>{{ $user->blocked_reason ?: 'Không ghi lý do' }}</dd></div>@endif
        </dl></div></section>
        <section class="admin-panel"><div class="admin-panel-header"><div><h2>Trạng thái tài khoản</h2><p>Khóa tài khoản sẽ chặn đăng nhập.</p></div></div><div class="admin-panel-body">
            <form method="post" action="{{ route('admin.users.status', $user) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="is_blocked" value="{{ $user->is_blocked ? 0 : 1 }}">
                @if (! $user->is_blocked)<div class="admin-field" style="margin-bottom:14px"><label for="blockedReason">Lý do khóa</label><textarea id="blockedReason" name="blocked_reason" maxlength="500" placeholder="Nhập lý do để lưu lại trên hồ sơ"></textarea></div>@endif
                <button class="{{ $user->is_blocked ? 'admin-button' : 'admin-button-danger' }}" type="submit">{{ $user->is_blocked ? 'Mở khóa tài khoản' : 'Khóa tài khoản' }}</button>
            </form>
        </div></section>
    </div>
    <section class="admin-panel"><div class="admin-panel-header"><div><h2>Đơn hàng gần đây</h2><p>Tối đa 10 đơn mới nhất.</p></div></div>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Mã đơn</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Trạng thái</th><th></th></tr></thead><tbody>
            @forelse ($user->orders as $order)
                <tr><td><strong>{{ $order->order_number }}</strong></td><td>{{ $order->created_at?->format('d/m/Y') }}</td><td>{{ number_format((float) $order->total, 0, ',', '.') }}₫</td><td>{{ ['pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'shipping' => 'Đang giao', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'][$order->status] ?? $order->status }}</td><td><a class="admin-button-link" href="{{ route('admin.orders.show', $order) }}">Chi tiết</a></td></tr>
            @empty
                <tr><td class="admin-empty" colspan="5">Khách hàng chưa có đơn hàng.</td></tr>
            @endforelse
        </tbody></table></div>
    </section>
@endsection
