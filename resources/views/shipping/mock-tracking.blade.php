@extends('layouts.store')
@section('title', 'Tra cứu vận đơn mô phỏng · '.$order->shipment_number)
@section('content')
@php
    $shipmentLabels = [
        'not_created' => 'Chưa tạo vận đơn', 'awaiting_pickup' => 'Chờ Viettel Post nhận hàng',
        'handed_over' => 'Bàn giao đơn vị vận chuyển', 'in_transit' => 'Đang trung chuyển',
        'out_for_delivery' => 'Đang giao đến người nhận', 'delivery_failed' => 'Giao không thành công',
        'returning' => 'Đang hoàn hàng', 'returned' => 'Đã hoàn hàng', 'delivered' => 'Đã giao thành công',
        'cancelled' => 'Đã hủy vận đơn',
    ];
@endphp
<main class="mx-auto max-w-3xl px-4 py-10 sm:py-14">
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-900/5">
        <div class="bg-indigo-950 px-6 py-7 text-white sm:px-9">
            <p class="text-xs font-bold uppercase tracking-[.2em] text-indigo-200">Viettel Post · Mô phỏng đồ án</p>
            <h1 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">Tra cứu hành trình vận đơn</h1>
            <p class="mt-2 text-sm text-indigo-100">Đây là trạng thái minh họa do quản lý cập nhật, chưa được đồng bộ với Viettel Post thật.</p>
        </div>

        <div class="p-6 sm:p-9">
            <div class="grid gap-4 rounded-2xl bg-slate-50 p-5 sm:grid-cols-2">
                <div><p class="text-xs text-slate-500">Mã vận đơn demo</p><p class="mt-1 font-mono text-sm font-bold text-slate-950">{{ $order->shipment_number }}</p></div>
                <div><p class="text-xs text-slate-500">Mã đơn hàng</p><p class="mt-1 font-mono text-sm font-bold text-slate-950">{{ $order->order_number }}</p></div>
                <div><p class="text-xs text-slate-500">Trạng thái hiện tại</p><p class="mt-1 font-bold text-indigo-950">{{ $shipmentLabels[$order->shipment_status] ?? $order->shipment_status }}</p></div>
                <div><p class="text-xs text-slate-500">Ngày giao dự kiến</p><p class="mt-1 font-bold text-slate-950">{{ $order->estimated_delivery_at?->format('d/m/Y') ?? 'Đang cập nhật' }}</p></div>
            </div>

            <div class="mt-7">
                <h2 class="font-bold text-slate-950">Lịch sử quét vận đơn</h2>
                <ol class="mt-4 grid gap-4">
                    @forelse ($order->shipmentEvents as $event)
                        <li class="flex gap-3 rounded-xl border border-slate-100 p-4">
                            <span class="mt-1 size-2.5 shrink-0 rounded-full bg-indigo-600"></span>
                            <span><strong class="block text-sm text-slate-950">{{ $shipmentLabels[$event->status] ?? $event->status }}</strong><span class="mt-1 block text-xs leading-5 text-slate-600">{{ $event->description ?: 'Cập nhật hành trình mô phỏng' }}</span><time class="mt-2 block text-[11px] text-slate-500">{{ ($event->occurred_at ?? $event->created_at)->format('d/m/Y H:i') }}</time></span>
                        </li>
                    @empty
                        <li class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">Chưa có lần quét nào được ghi nhận.</li>
                    @endforelse
                </ol>
            </div>
            <p class="mt-6 text-xs leading-5 text-slate-500">Trang tra cứu chỉ hiển thị mã đơn và dữ liệu vận chuyển. Thông tin khách hàng không được công khai tại đây.</p>
        </div>
    </section>
</main>
@endsection
