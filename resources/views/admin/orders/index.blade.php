@extends('layouts.admin')
@section('title', 'Quản lý đơn hàng')
@section('content')
@php
    $labels = [
        'pending_confirmation' => 'Đang xử lý đơn hàng',
        'pending_payment' => 'Chờ thanh toán',
        'confirmed' => 'Người bán chuẩn bị hàng',
        'preparing' => 'Người bán chuẩn bị hàng',
        'awaiting_pickup' => 'Chờ Viettel Post nhận hàng',
        'handed_over' => 'Bàn giao đơn vị vận chuyển',
        'in_transit' => 'Đang trung chuyển',
        'out_for_delivery' => 'Đang giao đến người nhận',
        'shipped' => 'Đang trung chuyển',
        'completed' => 'Đã hoàn tất giao hàng',
        'delivery_failed' => 'Giao không thành công',
        'failed' => 'Giao không thành công',
        'returning' => 'Đang hoàn hàng',
        'returned' => 'Đã hoàn hàng',
        'cancelled' => 'Đã hủy đơn hàng',
    ];
    $statusStyles = [
        'pending_confirmation' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'pending_payment' => 'bg-orange-50 text-orange-800 ring-orange-200',
        'confirmed' => 'bg-indigo-50 text-indigo-800 ring-indigo-200',
        'preparing' => 'bg-indigo-50 text-indigo-800 ring-indigo-200',
        'awaiting_pickup' => 'bg-blue-50 text-blue-800 ring-blue-200',
        'handed_over' => 'bg-blue-50 text-blue-800 ring-blue-200',
        'in_transit' => 'bg-violet-50 text-violet-800 ring-violet-200',
        'out_for_delivery' => 'bg-violet-50 text-violet-800 ring-violet-200',
        'shipped' => 'bg-violet-50 text-violet-800 ring-violet-200',
        'completed' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
        'delivery_failed' => 'bg-rose-50 text-rose-800 ring-rose-200',
        'failed' => 'bg-rose-50 text-rose-800 ring-rose-200',
        'returning' => 'bg-orange-50 text-orange-800 ring-orange-200',
        'returned' => 'bg-cyan-50 text-cyan-800 ring-cyan-200',
        'cancelled' => 'bg-rose-50 text-rose-800 ring-rose-200',
    ];
    $summaryCards = [
        ['label' => 'Tổng đơn hàng', 'value' => $stats['total'], 'note' => 'Tất cả đơn trong hệ thống', 'tone' => 'text-blue-700', 'icon' => 'ĐH'],
        ['label' => 'Cần xử lý', 'value' => $stats['needs_attention'], 'note' => 'Chờ xác nhận hoặc thanh toán', 'tone' => 'text-amber-600', 'icon' => '!'],
        ['label' => 'Đang giao hàng', 'value' => $stats['in_fulfillment'], 'note' => 'Đang chuẩn bị hoặc vận chuyển', 'tone' => 'text-violet-600', 'icon' => '↗'],
        ['label' => 'Đã hoàn tất', 'value' => $stats['completed'], 'note' => 'Giao hàng thành công', 'tone' => 'text-emerald-600', 'icon' => '✓'],
    ];
@endphp

<div class="mx-auto max-w-screen-2xl">
    <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.18em] text-blue-600">Vận hành & giao hàng</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-[#172554] sm:text-4xl">Đơn hàng</h1>
            <p class="mt-2 text-sm text-slate-500">Theo dõi thanh toán, trạng thái xử lý và hành trình đơn hàng.</p>
        </div>
        <div class="rounded-2xl border border-white/80 bg-white/70 px-4 py-3 text-right shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kết quả đang xem</p>
            <p class="mt-1 text-sm font-extrabold text-[#172554]">{{ number_format($orders->total()) }} <span class="font-medium text-slate-500">đơn hàng</span></p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach ($summaryCards as $card)
            <div class="group relative overflow-hidden rounded-2xl border border-white/80 bg-white/75 p-5 shadow-[0_14px_36px_-28px_rgba(23,37,84,.4)] backdrop-blur transition hover:-translate-y-0.5 hover:shadow-lg">
                <span class="absolute -right-5 -top-7 size-24 rounded-full bg-blue-50/80 transition group-hover:scale-125"></span>
                <div class="relative flex items-start justify-between gap-3">
                    <div><p class="text-xs font-semibold text-slate-500">{{ $card['label'] }}</p><p class="mt-3 text-3xl font-extrabold tracking-tight {{ $card['tone'] }}">{{ number_format($card['value']) }}</p><p class="mt-1 text-[11px] text-slate-400">{{ $card['note'] }}</p></div>
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-sm font-black text-blue-700">{{ $card['icon'] }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <section class="overflow-hidden rounded-2xl border border-white/80 bg-white/80 shadow-sm">
        <form method="GET" class="flex flex-wrap items-center gap-3 border-b border-slate-100 p-4">
            <label class="min-w-48 flex-1"><span class="sr-only">Tìm đơn hàng</span><input name="q" value="{{ request('q') }}" placeholder="Mã đơn, tên khách hàng hoặc số điện thoại..." class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm"></label>
            <label><span class="sr-only">Lọc theo trạng thái</span><select name="status" class="rounded-xl border-slate-200 px-3 py-2.5 text-sm"><option value="">Tất cả trạng thái</option>@foreach ($labels as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select></label>
            <button class="rounded-xl bg-[#172554] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-900">Lọc danh sách</button>
            @if (request()->filled('q') || request()->filled('status'))<a href="{{ route('admin.orders.index') }}" class="px-2 text-xs font-semibold text-slate-500 hover:text-blue-700">Đặt lại</a>@endif
        </form>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] text-left text-sm">
                <thead class="bg-slate-50/80 text-xs font-semibold text-slate-500"><tr><th class="px-5 py-4">Đơn hàng</th><th class="px-4 py-4">Khách hàng</th><th class="px-4 py-4">Ngày đặt</th><th class="px-4 py-4">Thanh toán</th><th class="px-4 py-4 text-right">Tổng tiền</th><th class="px-5 py-4 text-right">Trạng thái</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        <tr class="transition hover:bg-blue-50/30">
                            <td class="px-5 py-4"><a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-xs font-extrabold text-[#172554] hover:text-blue-600">{{ $order->order_number }}</a><span class="mt-1 block text-[11px] text-slate-400">{{ $order->items_count }} sản phẩm</span></td>
                            <td class="px-4 py-4"><div class="flex items-center gap-3"><span class="grid size-9 shrink-0 place-items-center rounded-xl bg-blue-50 text-xs font-black text-blue-800">{{ mb_strtoupper(mb_substr($order->customer_name, 0, 1)) }}</span><span class="min-w-0"><strong class="block truncate text-sm text-[#172554]">{{ $order->customer_name }}</strong><span class="mt-0.5 block text-xs text-slate-500">{{ $order->customer_phone }}</span></span></div></td>
                            <td class="px-4 py-4 text-xs leading-5 text-slate-600">{{ $order->created_at->format('d/m/Y') }}<span class="block text-[11px] text-slate-400">{{ $order->created_at->format('H:i') }}</span></td>
                            <td class="px-4 py-4"><span class="text-xs font-semibold text-slate-700">{{ $order->payment_method === 'cod' ? 'Thanh toán COD' : 'Chuyển khoản' }}</span><span @class(['mt-1 inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold', 'bg-emerald-50 text-emerald-700' => $order->payment_status === 'paid', 'bg-amber-50 text-amber-700' => $order->payment_status !== 'paid'])>{{ $order->payment_status === 'paid' ? 'Đã thu' : 'Chưa thu' }}</span></td>
                            <td class="px-4 py-4 text-right font-extrabold text-[#172554]">{{ number_format((float) $order->total, 0, ',', '.') }}₫</td>
                            <td class="px-5 py-4 text-right"><span @class(['inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold ring-1 ring-inset', $statusStyles[$order->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200'])>{{ $labels[$order->status] ?? $order->status }}</span><a href="{{ route('admin.orders.show', $order) }}" class="mt-2 block text-xs font-bold text-blue-700 hover:underline">Xem chi tiết →</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-16 text-center"><span class="mx-auto grid size-12 place-items-center rounded-2xl bg-blue-50 text-xl text-blue-600">⌕</span><strong class="mt-4 block text-sm text-[#172554]">Không có đơn hàng khớp bộ lọc</strong><p class="mt-1 text-xs text-slate-500">Thử thay đổi từ khóa hoặc trạng thái để xem thêm đơn hàng.</p><a href="{{ route('admin.orders.index') }}" class="mt-3 inline-flex text-xs font-bold text-blue-700 hover:underline">Xóa bộ lọc</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 p-4">{{ $orders->links() }}</div>
    </section>
</div>
@endsection
