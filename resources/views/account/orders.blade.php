@extends('layouts.store')

@section('title', 'Lịch sử đặt hàng')

@section('content')
    @php
        $statusLabels = [
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
            'cancelled' => 'Đã hủy đơn hàng',
            'returned' => 'Đã hoàn hàng',
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
            'completed' => 'bg-green-50 text-green-800 ring-green-200',
            'delivery_failed' => 'bg-rose-50 text-rose-800 ring-rose-200',
            'failed' => 'bg-rose-50 text-rose-800 ring-rose-200',
            'returning' => 'bg-orange-50 text-orange-800 ring-orange-200',
            'cancelled' => 'bg-rose-50 text-rose-800 ring-rose-200',
            'returned' => 'bg-cyan-50 text-cyan-800 ring-cyan-200',
        ];
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8 sm:py-10">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
            <div>
                <a href="{{ route('account.profile.edit') }}" class="text-xs font-bold text-[#164d8b] hover:text-blue-700">← Quay lại hồ sơ</a>
                <p class="mt-5 text-xs font-bold uppercase tracking-[.16em] text-blue-700">Tài khoản của bạn</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-[#10283e]">Lịch sử đặt hàng</h1>
                <p class="mt-1 text-sm text-slate-500">Xem lại các đơn hàng và trạng thái xử lý.</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-[#10283e] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#183f63]">
                Tiếp tục mua sắm
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2.2 11.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20 9H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
            </a>
        </div>

        <section class="overflow-hidden rounded-2xl border border-[#dce6f2] bg-white shadow-[0_8px_24px_rgba(27,59,100,.045)]">
            <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                <span class="grid size-10 place-items-center rounded-xl bg-blue-50 text-blue-700">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 4h14v16H5zM8 8h8m-8 4h8m-8 4h4"/></svg>
                </span>
                <div>
                    <h2 class="font-bold text-[#10283e]">Đơn hàng của bạn</h2>
                    <p class="mt-0.5 text-xs text-slate-500">{{ $orders->total() }} đơn hàng</p>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($orders as $order)
                    <a href="{{ route('account.orders.show', $order) }}" class="group flex flex-wrap items-center justify-between gap-4 px-5 py-4 transition hover:bg-blue-50/50 sm:px-6">
                        <span class="flex min-w-0 items-center gap-3">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-slate-50 text-slate-500 transition group-hover:bg-white group-hover:text-blue-700">
                                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 4h14v16H5zM8 8h8m-8 4h8m-8 4h4"/></svg>
                            </span>
                            <span class="min-w-0">
                                <strong class="block truncate font-mono text-sm text-slate-900">{{ $order->order_number }}</strong>
                                <span class="mt-1 block text-xs text-slate-500">Đặt ngày {{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->items_count }} sản phẩm</span>
                            </span>
                        </span>
                        <span class="flex w-full items-center justify-between gap-4 sm:w-auto sm:justify-end">
                            <span @class([
                                'rounded-full px-2.5 py-1 text-[10px] font-bold ring-1 ring-inset',
                                $statusStyles[$order->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200',
                            ])>{{ $statusLabels[$order->status] ?? $order->status }}</span>
                            <strong class="min-w-28 text-right text-sm text-[#10283e]">{{ number_format((float) $order->total, 0, ',', '.') }}₫</strong>
                        </span>
                    </a>
                @empty
                    <div class="px-5 py-14 text-center sm:px-6">
                        <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-blue-50 text-blue-700">
                            <svg viewBox="0 0 24 24" class="size-7" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 4h14v16H5zM8 8h8m-8 4h8m-8 4h4"/></svg>
                        </span>
                        <h3 class="mt-4 text-base font-bold text-slate-800">Bạn chưa có đơn hàng nào</h3>
                        <p class="mt-1 text-sm text-slate-500">Các đơn hàng sẽ xuất hiện tại đây sau khi bạn đặt mua.</p>
                        <a href="{{ route('products.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-blue-800">Khám phá linh kiện</a>
                    </div>
                @endforelse
            </div>

            @if ($orders->hasPages())
                <div class="border-t border-slate-100 p-4 sm:p-5">{{ $orders->links() }}</div>
            @endif
        </section>
    </div>
@endsection
