@extends('layouts.store')
@section('title', 'Đơn hàng '.$order->order_number)
@section('content')
@php
    $labels = [
        'pending_confirmation' => 'Đang xử lý đơn hàng', 'pending_payment' => 'Chờ thanh toán',
        'confirmed' => 'Người bán chuẩn bị hàng', 'preparing' => 'Người bán chuẩn bị hàng',
        'awaiting_pickup' => 'Chờ Viettel Post nhận hàng', 'handed_over' => 'Bàn giao đơn vị vận chuyển',
        'in_transit' => 'Đang trung chuyển', 'out_for_delivery' => 'Đang giao đến người nhận',
        'completed' => 'Đã hoàn tất giao hàng', 'delivery_failed' => 'Giao không thành công',
        'returning' => 'Đang hoàn hàng', 'returned' => 'Đã hoàn hàng', 'cancelled' => 'Đã hủy đơn hàng',
    ];
    $orderStatusStyles = [
        'pending_confirmation' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'pending_payment' => 'bg-orange-50 text-orange-800 ring-orange-200',
        'confirmed' => 'bg-indigo-50 text-indigo-800 ring-indigo-200',
        'preparing' => 'bg-indigo-50 text-indigo-800 ring-indigo-200',
        'awaiting_pickup' => 'bg-blue-50 text-blue-800 ring-blue-200',
        'handed_over' => 'bg-blue-50 text-blue-800 ring-blue-200',
        'in_transit' => 'bg-violet-50 text-violet-800 ring-violet-200',
        'out_for_delivery' => 'bg-violet-50 text-violet-800 ring-violet-200',
        'completed' => 'bg-green-50 text-green-800 ring-green-200',
        'delivery_failed' => 'bg-rose-50 text-rose-800 ring-rose-200',
        'returning' => 'bg-orange-50 text-orange-800 ring-orange-200',
        'returned' => 'bg-cyan-50 text-cyan-800 ring-cyan-200',
        'cancelled' => 'bg-rose-50 text-rose-800 ring-rose-200',
    ];
    $shipmentLabels = [
        'not_created' => 'Chưa tạo vận đơn', 'awaiting_pickup' => 'Chờ Viettel Post nhận hàng',
        'handed_over' => 'Bàn giao đơn vị vận chuyển', 'in_transit' => 'Đang trung chuyển',
        'out_for_delivery' => 'Đang giao đến người nhận', 'delivery_failed' => 'Giao không thành công',
        'returning' => 'Đang hoàn hàng', 'returned' => 'Đã hoàn hàng', 'delivered' => 'Đã giao thành công',
        'cancelled' => 'Đã hủy vận đơn',
    ];
    $returnStatusLabels = ['pending' => 'Đang chờ xử lý', 'accepted' => 'Đã chấp nhận', 'received' => 'Cửa hàng đã nhận hàng trả về', 'rejected' => 'Bị từ chối'];
    $returnResolutionLabels = ['exchange' => 'Đổi hàng', 'refund' => 'Hoàn tiền toàn bộ đơn', 'warranty' => 'Bảo hành'];
    $latestReturnRequest = $order->returnRequests->sortByDesc('id')->first();
    $hasOpenReturnRequest = $order->returnRequests->contains(fn ($returnRequest): bool => in_array($returnRequest->status, ['pending', 'accepted'], true));
    $canRequestReturn = ! isset($guestAccessToken)
        && $order->status === 'completed'
        && $order->created_at->gte(now()->subDays(7))
        && ! $hasOpenReturnRequest
        && ! in_array($order->refund_status, ['pending', 'refunded'], true);
@endphp

<div class="mx-auto max-w-5xl px-4 py-9">
    <a href="{{ isset($guestAccessToken) ? route('home') : route('account.index') }}" class="text-xs font-bold text-emerald-900">{{ isset($guestAccessToken) ? '← Về cửa hàng' : '← Tài khoản của tôi' }}</a>
    <div class="mt-5 flex flex-wrap items-end justify-between gap-4"><div><p class="text-xs font-bold tracking-wide text-emerald-700">Chi tiết đơn hàng</p><h1 class="mt-2 font-mono text-2xl font-bold text-emerald-950">{{ $order->order_number }}</h1><p class="mt-1 text-sm text-slate-500">Đặt ngày {{ $order->created_at->format('d/m/Y H:i') }}</p></div><span @class(['rounded-full px-4 py-2 text-sm font-bold ring-1 ring-inset', $orderStatusStyles[$order->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200'])>{{ $labels[$order->status] ?? $order->status }}</span></div>

    <div class="mt-7 grid items-start gap-6 lg:grid-cols-[1fr_340px]">
        <div class="grid gap-5">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white"><h2 class="border-b border-slate-100 px-5 py-4 font-bold text-emerald-950">Sản phẩm trong đơn</h2><div class="divide-y divide-slate-100">@foreach ($order->items as $item)<div class="flex items-center justify-between gap-3 px-5 py-4"><span><strong class="block text-sm">{{ $item->product_name }}</strong><span class="mt-1 block font-mono text-xs text-slate-500">{{ $item->sku }} · {{ $item->quantity }} cái</span></span><span class="text-right"><strong class="block text-sm">{{ number_format((float) $item->line_total, 0, ',', '.') }}₫</strong><span class="text-xs text-slate-500">{{ number_format((float) $item->unit_price, 0, ',', '.') }}₫/cái</span></span></div>@endforeach</div></section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5"><div class="flex flex-wrap items-start justify-between gap-3"><div><h2 class="font-bold text-emerald-950">Vận chuyển Viettel Post</h2><p class="mt-1 text-xs text-slate-500">{{ $shipmentLabels[$order->shipment_status] ?? $order->shipment_status }}</p></div>@if ($order->shipment_number)<a href="{{ $order->shipment_tracking_url ?: 'https://viettelpost.com.vn/tra-cuu-hanh-trinh-don/' }}" target="_blank" rel="noopener" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-emerald-900">{{ str_contains((string) $order->shipment_service, 'Mô phỏng') ? 'Tra cứu demo ↗' : 'Tra cứu Viettel Post ↗' }}</a>@endif</div>
                @if ($order->shipment_number)<div class="mt-4 grid gap-3 rounded-xl bg-slate-50 p-4 text-xs sm:grid-cols-3"><p><span class="text-slate-500">Mã vận đơn</span><strong class="mt-1 block font-mono text-slate-900">{{ $order->shipment_number }}</strong></p><p><span class="text-slate-500">Dịch vụ</span><strong class="mt-1 block text-slate-900">{{ $order->shipment_service ?: 'Viettel Post' }}</strong></p><p><span class="text-slate-500">Ngày giao dự kiến</span><strong class="mt-1 block text-slate-900">{{ $order->estimated_delivery_at?->format('d/m/Y') ?: 'Đang cập nhật' }}</strong></p></div><p class="mt-2 text-[11px] text-slate-500">Ngày giao là ước tính và có thể thay đổi theo hành trình thực tế.</p>@else<p class="mt-3 rounded-xl bg-slate-50 p-4 text-xs leading-5 text-slate-600">Mã vận đơn sẽ hiển thị sau khi cửa hàng tạo vận đơn.</p>@endif
                <ol class="mt-4 grid gap-3 border-t border-slate-100 pt-4">@forelse ($order->shipmentEvents as $event)<li class="flex gap-3 text-xs"><span class="mt-1.5 size-2 shrink-0 rounded-full bg-emerald-600"></span><span><strong class="text-slate-900">{{ $shipmentLabels[$event->status] ?? $event->status }} · {{ $event->description ?: 'Cập nhật hành trình' }}</strong><span class="mt-0.5 block text-[10px] text-slate-500">{{ ($event->occurred_at ?? $event->created_at)->format('d/m/Y H:i') }}</span></span></li>@empty<li class="text-xs text-slate-500">Chưa có cập nhật vận chuyển.</li>@endforelse</ol>
            </section>
        </div>

        <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5"><h2 class="font-bold text-emerald-950">Tổng thanh toán</h2><div class="mt-4 grid gap-3 text-sm"><div class="flex justify-between"><span class="text-slate-500">Tạm tính</span><span>{{ number_format((float) $order->subtotal, 0, ',', '.') }}₫</span></div><div class="flex justify-between"><span class="text-slate-500">Giảm giá</span><span>-{{ number_format((float) $order->discount_total, 0, ',', '.') }}₫</span></div><div class="flex justify-between"><span class="text-slate-500">Giao hàng</span><span>{{ number_format((float) $order->shipping_total, 0, ',', '.') }}₫</span></div><div class="flex justify-between"><span class="text-slate-500">VAT</span><span>{{ number_format((float) $order->vat_total, 0, ',', '.') }}₫</span></div><div class="flex justify-between border-t border-slate-200 pt-3 text-base font-bold text-emerald-950"><span>Tổng</span><span>{{ number_format((float) $order->total, 0, ',', '.') }}₫</span></div></div><div class="mt-5 border-t border-slate-100 pt-4 text-xs leading-5 text-slate-600"><p class="font-bold text-slate-800">Địa chỉ giao hàng</p><p>{{ $order->customer_name }} · {{ $order->customer_phone }}</p><p>{{ $order->shipping_address }}@if ($order->shipping_city), {{ $order->shipping_city }}@endif</p><p class="mt-3 font-bold text-slate-800">Thanh toán</p><p>{{ $order->payment_method === 'cod' ? 'Thanh toán khi nhận hàng (COD)' : ($order->paymentIntent && str_starts_with((string) $order->paymentIntent->payment_link_id, 'MOCK-') ? 'Thanh toán online · Mô phỏng' : 'Chuyển khoản ngân hàng') }} · {{ $order->payment_status === 'paid' ? 'Đã thanh toán' : ($order->payment_status === 'refunded' ? 'Đã hoàn tiền' : 'Chờ thanh toán') }}</p>
            @if ($order->payment_method === 'cod')<p class="mt-1">Đối soát thu hộ: {{ ['pending_collection' => 'Chờ thu', 'collected' => 'Đã thu', 'pending_reconciliation' => 'Chờ Viettel Post chuyển tiền', 'reconciled' => 'Đã đối soát'][$order->cod_status] ?? $order->cod_status }}</p>@endif
        </div>
        @if ($order->status === 'cancelled')
            <div class="mt-4 rounded-xl bg-rose-50 p-3 text-xs leading-5 text-rose-900">
                <strong>Đơn đã hủy</strong>
                <p class="mt-1">{{ $order->cancellation_reason }}</p>
            </div>
        @endif
        @if ($order->refund_status === 'pending')
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-950">
                <strong>Khoản hoàn tiền đang chờ xử lý</strong>
                <p class="mt-1">Cửa hàng đang xử lý hoàn {{ number_format((float) $order->total, 0, ',', '.') }}₫.</p>
            </div>
        @elseif ($order->refund_status === 'refunded')
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs leading-5 text-emerald-950">
                <strong>{{ str_starts_with((string) $order->refund_reference, 'SIM-REFUND-') ? 'Đã mô phỏng hoàn tiền' : 'Đã hoàn tiền' }}</strong>
                <p class="mt-1">Số tiền: {{ number_format((float) $order->total, 0, ',', '.') }}₫</p>
                <p class="mt-1">Mã giao dịch: <span class="font-mono">{{ $order->refund_reference }}</span></p>
                @if (str_starts_with((string) $order->refund_reference, 'SIM-REFUND-'))
                    <p class="mt-1">Đây là giao dịch mô phỏng, không có tiền thật được chuyển.</p>
                @endif
            </div>
        @endif
        @if (in_array($order->status, ['pending_confirmation', 'pending_payment'], true))<form method="POST" action="{{ isset($guestAccessToken) ? route('checkout.guest-order.cancel', ['order' => $order, 'token' => $guestAccessToken]) : route('account.orders.cancel', $order) }}" class="mt-5 grid gap-2 border-t border-slate-100 pt-4">@csrf<label for="cancel-reason" class="text-xs font-bold text-slate-700">Lý do hủy đơn</label><textarea id="cancel-reason" required minlength="5" maxlength="1000" name="reason" rows="3" placeholder="Nhập lý do cụ thể" class="rounded-lg border-slate-200 text-xs"></textarea><button class="w-full rounded-lg border border-rose-200 px-4 py-2.5 text-xs font-bold text-rose-700 hover:bg-rose-50">Hủy đơn hàng</button></form>@endif
        @if (! isset($guestAccessToken) && $latestReturnRequest)
            <div class="mt-5 border-t border-slate-100 pt-4 text-xs leading-5">
                <strong>Yêu cầu đổi trả: {{ $returnStatusLabels[$latestReturnRequest->status] ?? $latestReturnRequest->status }}</strong>
                <p class="mt-1 text-slate-600">{{ $latestReturnRequest->reason }}</p>
                @if ($latestReturnRequest->resolution)
                    <p class="mt-1 text-slate-600">Hướng xử lý: {{ $returnResolutionLabels[$latestReturnRequest->resolution] ?? $latestReturnRequest->resolution }}</p>
                @endif
                @if ($latestReturnRequest->status === 'accepted' && $latestReturnRequest->resolution === 'refund')
                    <p class="mt-1 text-slate-600">Vui lòng gửi hàng theo hướng dẫn của cửa hàng. Kế toán xử lý hoàn tiền sau khi cửa hàng xác nhận đã nhận hàng.</p>
                @endif
                @if ($latestReturnRequest->staff_note)
                    <p class="mt-1 text-slate-600">Ghi chú từ cửa hàng: {{ $latestReturnRequest->staff_note }}</p>
                @endif
            </div>
        @endif
        @if ($canRequestReturn)
            <form method="POST" enctype="multipart/form-data" action="{{ route('account.orders.return', $order) }}" class="mt-5 grid gap-3 border-t border-slate-100 pt-4">
                @csrf
                <h3 class="text-sm font-bold">Yêu cầu đổi trả / bảo hành</h3>
                <textarea required name="reason" rows="2" placeholder="Lý do đổi trả..." class="rounded-lg border-slate-200 text-xs"></textarea>
                <label class="grid gap-1 text-xs text-slate-500">Ảnh/video minh chứng<input type="file" name="evidence" accept="image/jpeg,image/png,image/webp,video/mp4" class="text-xs"></label>
                <button class="rounded-lg bg-emerald-950 px-4 py-2.5 text-xs font-bold text-white">Gửi yêu cầu</button>
            </form>
        @endif
        </aside>
    </div>
</div>
@endsection
