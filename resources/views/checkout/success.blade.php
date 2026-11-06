@extends('layouts.store')
@section('title', 'Đặt hàng thành công · '.$order->order_number)
@section('content')
@php
    $statusLabels = [
        'pending_confirmation' => 'Đang xử lý đơn hàng', 'pending_payment' => 'Chờ thanh toán',
        'preparing' => 'Người bán chuẩn bị hàng', 'confirmed' => 'Người bán chuẩn bị hàng',
        'awaiting_pickup' => 'Chờ Viettel Post nhận hàng', 'handed_over' => 'Bàn giao đơn vị vận chuyển',
        'in_transit' => 'Đang trung chuyển', 'out_for_delivery' => 'Đang giao đến người nhận',
        'completed' => 'Đã hoàn tất giao hàng', 'cancelled' => 'Đã hủy đơn hàng',
    ];
@endphp
@php($isMockPayment = str_starts_with((string) $order->paymentIntent?->payment_link_id, 'MOCK-'))
<div class="mx-auto max-w-3xl px-4 py-14"><div class="rounded-2xl border border-emerald-200 bg-white p-6 text-center shadow-sm sm:p-10"><span class="mx-auto grid size-16 place-items-center rounded-full bg-emerald-100 text-emerald-900"><svg viewBox="0 0 24 24" class="size-8" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg></span><p class="mt-5 text-xs font-bold uppercase tracking-[.2em] text-emerald-700">{{ $order->payment_status === 'paid' ? ($isMockPayment ? 'Thanh toán mô phỏng thành công' : 'Thanh toán thành công') : 'Đã nhận đơn hàng' }}</p><h1 class="mt-2 text-3xl font-bold tracking-tight text-emerald-950">Cảm ơn bạn đã đặt hàng</h1><p class="mt-2 text-sm text-slate-500">{{ $order->payment_status === 'paid' ? ($isMockPayment ? 'Giao dịch mô phỏng đã thành công; không có khoản tiền thật nào được thu. Cửa hàng sẽ kiểm tra và xác nhận đơn trước khi chuẩn bị.' : 'PayOS đã xác nhận giao dịch. Cửa hàng sẽ kiểm tra và xác nhận đơn trước khi chuẩn bị.') : 'Nhân viên sẽ xác nhận thông tin trước khi chuẩn bị linh kiện.' }}</p><div class="mx-auto mt-7 max-w-md rounded-xl bg-[#f5f7f2] p-5 text-left"><div class="flex justify-between gap-3"><span class="text-sm text-slate-500">Mã đơn</span><strong class="font-mono text-sm">{{ $order->order_number }}</strong></div><div class="mt-3 flex justify-between gap-3"><span class="text-sm text-slate-500">Trạng thái</span><strong class="text-right text-sm">{{ $statusLabels[$order->status] ?? $order->status }}</strong></div><div class="mt-3 flex justify-between gap-3"><span class="text-sm text-slate-500">Thanh toán</span><strong class="text-sm">{{ $isMockPayment ? 'Đã thanh toán · mô phỏng' : ($order->payment_status === 'paid' ? 'Đã thanh toán' : 'Thanh toán khi nhận hàng') }}</strong></div><div class="mt-3 flex justify-between gap-3"><span class="text-sm text-slate-500">Tổng thanh toán</span><strong class="text-sm text-emerald-950">{{ number_format((float) $order->total, 0, ',', '.') }}₫</strong></div></div><div class="mt-7 flex flex-wrap justify-center gap-3">
    @if ($order->user_id)
        <a href="{{ route('account.orders.show', $order) }}" class="rounded-lg bg-emerald-950 px-5 py-3 text-sm font-bold text-white">Theo dõi đơn hàng</a>
    @elseif ($order->guest_access_token)
        <a href="{{ route('checkout.guest-order.show', ['order' => $order, 'token' => $order->guest_access_token]) }}" class="rounded-lg bg-emerald-950 px-5 py-3 text-sm font-bold text-white">Theo dõi hoặc hủy đơn hàng</a>
    @else
        <a href="{{ route('products.index') }}" class="rounded-lg bg-emerald-950 px-5 py-3 text-sm font-bold text-white">Tiếp tục mua sắm</a>
    @endif
    <a href="{{ route('home') }}" class="rounded-lg border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700">Về trang chủ</a></div></div></div>
@endsection
