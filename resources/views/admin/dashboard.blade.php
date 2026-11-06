@extends('layouts.admin')
@section('title', 'Tổng quan quản trị')
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
    $statusTone = [
        'pending_confirmation' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'pending_payment' => 'bg-orange-50 text-orange-800 ring-orange-200',
        'confirmed' => 'bg-indigo-50 text-indigo-800 ring-indigo-200',
        'preparing' => 'bg-indigo-50 text-indigo-800 ring-indigo-200',
        'awaiting_pickup' => 'bg-blue-50 text-blue-800 ring-blue-200',
        'handed_over' => 'bg-blue-50 text-blue-800 ring-blue-200',
        'in_transit' => 'bg-violet-50 text-violet-800 ring-violet-200',
        'out_for_delivery' => 'bg-violet-50 text-violet-800 ring-violet-200',
        'shipped' => 'bg-violet-50 text-violet-800 ring-violet-200',
        'completed' => 'bg-green-50 text-green-800 ring-green-200',
        'delivery_failed' => 'bg-rose-50 text-rose-800 ring-rose-200',
        'failed' => 'bg-rose-50 text-rose-800 ring-rose-200',
        'returning' => 'bg-orange-50 text-orange-800 ring-orange-200',
        'returned' => 'bg-cyan-50 text-cyan-800 ring-cyan-200',
        'cancelled' => 'bg-rose-50 text-rose-800 ring-rose-200',
    ];
@endphp

<div class="mx-auto max-w-[1500px]">
    <div class="mb-6 px-1">
        <h1 class="text-3xl font-black tracking-tight text-[#172554] sm:text-4xl">Tổng quan quản trị</h1>
        <p class="mt-1.5 text-sm text-slate-500">Xin chào {{ auth()->user()->name }} · {{ now()->format('d/m/Y') }}</p>
    </div>

    <section aria-label="Chỉ số cửa hàng" data-guide-target="overview-metrics" class="grid grid-cols-2 gap-3 xl:grid-cols-4 xl:gap-4">
        <article class="rounded-[24px] border border-white/80 bg-white/55 p-4 shadow-[0_18px_40px_-30px_rgba(24,38,76,.55)] backdrop-blur-2xl sm:p-5">
            <p class="text-xs font-medium text-slate-500">Đơn hôm nay</p>
            <p class="mt-2 text-3xl font-black tracking-tight text-[#172554]">{{ $ordersToday }}</p>
        </article>
        <article class="rounded-[24px] border border-white/80 bg-white/55 p-4 shadow-[0_18px_40px_-30px_rgba(24,38,76,.55)] backdrop-blur-2xl sm:p-5">
            <p class="text-xs font-medium text-slate-500">Chờ xử lý</p>
            <p class="mt-2 text-3xl font-black tracking-tight text-amber-700">{{ $pendingOrders }}</p>
        </article>
        <article class="rounded-[24px] border border-white/80 bg-white/55 p-4 shadow-[0_18px_40px_-30px_rgba(24,38,76,.55)] backdrop-blur-2xl sm:p-5">
            <p class="text-xs font-medium text-slate-500">Doanh thu tháng</p>
            <p class="mt-2 text-2xl font-black tracking-tight text-[#172554]">{{ number_format((float) $revenueThisMonth, 0, ',', '.') }}₫</p>
        </article>
        <article class="rounded-[24px] border border-white/80 bg-white/55 p-4 shadow-[0_18px_40px_-30px_rgba(24,38,76,.55)] backdrop-blur-2xl sm:p-5">
            <p class="text-xs font-medium text-slate-500">Sản phẩm tồn thấp</p>
            <p class="mt-2 text-3xl font-black tracking-tight text-rose-600">{{ $lowStock }}</p>
        </article>
    </section>

    <div class="mt-5 grid gap-4 xl:grid-cols-[1.3fr_.8fr]">
        <section class="overflow-hidden rounded-[26px] border border-white/80 bg-white/55 p-4 shadow-[0_22px_45px_-32px_rgba(24,38,76,.5)] backdrop-blur-2xl sm:p-5">
            <div class="flex items-center justify-between gap-3 px-1 pb-3">
                <div><h2 class="font-extrabold tracking-tight text-[#172554]">Đơn hàng gần đây</h2><p class="mt-0.5 text-xs text-slate-500">Ưu tiên xác nhận và chuẩn bị đơn mới</p></div>
                @if (in_array(auth()->user()->role, ['admin', 'sales', 'warehouse', 'accountant'], true))
                    <a href="{{ route('admin.orders.index') }}" class="shrink-0 rounded-xl border border-white bg-white/60 px-3 py-2 text-[11px] font-bold text-[#172554] transition hover:bg-white">Tất cả đơn →</a>
                @endif
            </div>
            <div class="divide-y divide-white/80">
                @forelse ($recentOrders as $order)
                    <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center justify-between gap-3 rounded-2xl px-2 py-3.5 transition hover:bg-white/45 sm:px-3">
                        <span class="min-w-0"><strong class="block truncate font-mono text-xs font-bold text-[#172554]">{{ $order->order_number }}</strong><span class="mt-1 block truncate text-xs text-slate-500">{{ $order->customer_name }} · {{ $order->created_at->format('d/m H:i') }}</span></span>
                        <span class="shrink-0 text-right"><strong class="block text-sm font-extrabold text-[#172554]">{{ number_format((float) $order->total, 0, ',', '.') }}₫</strong><span @class(['mt-1 inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold ring-1 ring-inset', $statusTone[$order->status] ?? 'bg-white/70 text-slate-600 ring-white'])>{{ $labels[$order->status] ?? $order->status }}</span></span>
                    </a>
                @empty
                    <div class="px-3 py-10 text-center text-sm text-slate-500">Chưa có đơn hàng.</div>
                @endforelse
            </div>
        </section>

        <section class="overflow-hidden rounded-[26px] border border-white/80 bg-white/55 p-4 shadow-[0_22px_45px_-32px_rgba(24,38,76,.5)] backdrop-blur-2xl sm:p-5">
            <div class="px-1 pb-3"><h2 class="font-extrabold tracking-tight text-[#172554]">Cảnh báo tồn kho</h2><p class="mt-0.5 text-xs text-slate-500">Cần nhập hàng hoặc điều chỉnh mức tối thiểu</p></div>
            <div class="divide-y divide-white/80">
                @forelse ($lowStockProducts as $product)
                    <a href="{{ route('admin.inventory.index', ['q' => $product->sku]) }}" class="flex items-center justify-between gap-3 rounded-2xl px-2 py-3.5 transition hover:bg-white/45 sm:px-3">
                        <span class="min-w-0"><strong class="block truncate text-sm font-bold text-[#172554]">{{ $product->name }}</strong><span class="mt-1 block font-mono text-[10px] text-slate-500">{{ $product->sku }}</span></span>
                        <span class="shrink-0 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-black text-rose-700 ring-1 ring-rose-200">{{ $product->stock }} còn</span>
                    </a>
                @empty
                    <div class="flex min-h-40 flex-col items-center justify-center px-3 py-8 text-center text-sm text-slate-500"><span class="mb-3 grid size-11 place-items-center rounded-full border border-emerald-200 bg-emerald-50/70 text-emerald-700"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg></span>Tất cả sản phẩm đều trên mức tối thiểu.</div>
                @endforelse
            </div>
        </section>
    </div>

    <section data-guide-target="overview-actions" class="mt-5 rounded-[26px] border border-white/85 bg-gradient-to-r from-blue-100/80 via-white/55 to-indigo-100/70 p-5 shadow-[0_22px_45px_-32px_rgba(24,38,76,.5)] backdrop-blur-2xl sm:flex sm:items-center sm:justify-between sm:px-7 sm:py-6">
        <div><h2 class="text-lg font-extrabold tracking-tight text-[#172554]">Theo dõi doanh thu, đơn hàng và tồn kho</h2><p class="mt-1 text-xs text-slate-500">Số liệu được tổng hợp trực tiếp từ đơn hàng và nhật ký kho.</p></div>
        <div class="mt-4 flex flex-wrap gap-2 sm:mt-0">
            @if (in_array(auth()->user()->role, ['admin', 'sales', 'warehouse', 'accountant'], true))
                <a href="{{ route('admin.orders.index') }}" class="rounded-xl border border-white bg-white/55 px-4 py-2.5 text-xs font-bold text-[#172554] transition hover:bg-white">Báo cáo đơn hàng</a>
            @endif
            @if (in_array(auth()->user()->role, ['admin', 'warehouse'], true))
                <a href="{{ route('admin.inventory.index') }}" class="rounded-xl border border-white bg-white/55 px-4 py-2.5 text-xs font-bold text-[#172554] transition hover:bg-white">Báo cáo tồn kho</a>
            @endif
        </div>
    </section>
</div>
@endsection
