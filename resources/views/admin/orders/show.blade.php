@extends('layouts.admin')
@section('title', 'Đơn hàng '.$order->order_number)
@section('content')
@php
    $labels = [
        'pending_confirmation' => 'Đang xử lý đơn hàng', 'pending_payment' => 'Chờ thanh toán',
        'confirmed' => 'Người bán chuẩn bị hàng', 'preparing' => 'Người bán chuẩn bị hàng',
        'awaiting_pickup' => 'Chờ Viettel Post nhận hàng', 'handed_over' => 'Bàn giao đơn vị vận chuyển',
        'in_transit' => 'Đang trung chuyển', 'out_for_delivery' => 'Đang giao đến người nhận',
        'shipped' => 'Đang trung chuyển',
        'completed' => 'Đã hoàn tất giao hàng', 'delivery_failed' => 'Giao không thành công',
        'failed' => 'Giao không thành công',
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
        'shipped' => 'bg-violet-50 text-violet-800 ring-violet-200',
        'completed' => 'bg-green-50 text-green-800 ring-green-200',
        'delivery_failed' => 'bg-rose-50 text-rose-800 ring-rose-200',
        'failed' => 'bg-rose-50 text-rose-800 ring-rose-200',
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
    $shipmentTransitions = [
        'awaiting_pickup' => ['handed_over' => 'Viettel Post đã nhận kiện', 'in_transit' => 'Đang trung chuyển', 'out_for_delivery' => 'Đang giao đến người nhận', 'delivery_failed' => 'Giao không thành công', 'returning' => 'Đang hoàn hàng', 'completed' => 'Đã giao thành công'],
        'handed_over' => ['in_transit' => 'Đang trung chuyển', 'out_for_delivery' => 'Đang giao đến người nhận', 'delivery_failed' => 'Giao không thành công', 'returning' => 'Đang hoàn hàng', 'completed' => 'Đã giao thành công'],
        'in_transit' => ['out_for_delivery' => 'Đang giao đến người nhận', 'delivery_failed' => 'Giao không thành công', 'returning' => 'Đang hoàn hàng', 'completed' => 'Đã giao thành công'],
        'out_for_delivery' => ['in_transit' => 'Tiếp tục trung chuyển', 'delivery_failed' => 'Giao không thành công', 'returning' => 'Đang hoàn hàng', 'completed' => 'Đã giao thành công'],
        'delivery_failed' => ['in_transit' => 'Tiếp tục trung chuyển', 'out_for_delivery' => 'Giao lại', 'returning' => 'Đang hoàn hàng', 'returned' => 'Đã hoàn hàng'],
        'returning' => ['returned' => 'Kho đã nhận lại hàng'],
    ];
    $nextStatuses = $shipmentTransitions[$order->status] ?? [];
    $isOrderManager = in_array(auth()->user()->role, ['admin', 'sales'], true);
    $canManageShipment = in_array(auth()->user()->role, ['admin', 'sales', 'warehouse'], true);
    $canManageFinance = in_array(auth()->user()->role, ['admin', 'accountant'], true);
    $isViettelPostMock = config('services.viettelpost.driver') === 'mock';
    $isMockPayment = str_starts_with((string) $order->paymentIntent?->payment_link_id, 'MOCK-');
    $returnStatusLabels = ['pending' => 'Chờ xử lý', 'accepted' => 'Đã duyệt', 'received' => 'Đã nhận hàng hoàn', 'rejected' => 'Từ chối'];
    $returnResolutionLabels = ['exchange' => 'Đổi hàng', 'refund' => 'Hoàn tiền', 'warranty' => 'Bảo hành'];
@endphp

<div class="mx-auto max-w-[1500px]">
    <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-indigo-800 hover:underline">← Tất cả đơn hàng</a>
    <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-xs font-bold uppercase tracking-[.16em] text-indigo-700">Vận hành đơn hàng</p><h1 class="mt-1 font-mono text-2xl font-black tracking-tight text-[#172554] sm:text-3xl">{{ $order->order_number }}</h1><p class="mt-1 text-sm text-slate-500">{{ $order->created_at->format('d/m/Y H:i') }} · {{ $labels[$order->status] ?? $order->status }}</p></div>
        <div class="flex flex-wrap gap-2 print:hidden"><button onclick="window.print()" class="rounded-xl border border-white bg-white/60 px-4 py-2.5 text-xs font-bold text-[#172554] shadow-sm">In phiếu đơn hàng</button>
            @if ($isOrderManager && in_array($order->status, ['pending_confirmation', 'pending_payment'], true))
                <form method="POST" action="{{ route('admin.orders.confirm', $order) }}">@csrf<button class="rounded-xl bg-[#172554] px-4 py-2.5 text-xs font-bold text-white shadow-sm">Xác nhận, bắt đầu chuẩn bị</button></form>
            @endif
        </div>
    </div>

    <div class="mt-5 grid items-start gap-4 xl:grid-cols-[minmax(0,1.3fr)_minmax(320px,.7fr)]">
        <div class="grid gap-4">
            <section class="overflow-hidden rounded-[24px] border border-white/85 bg-white/65 shadow-[0_18px_45px_-32px_rgba(24,38,76,.5)] backdrop-blur-xl">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/80 px-5 py-4"><div><h2 class="font-extrabold text-[#172554]">Sản phẩm trong đơn</h2><p class="mt-0.5 text-xs text-slate-500">{{ $order->items->count() }} dòng sản phẩm</p></div><span @class(['rounded-full px-3 py-1.5 text-[11px] font-bold ring-1 ring-inset', $orderStatusStyles[$order->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200'])>{{ $labels[$order->status] ?? $order->status }}</span></div>
                <div class="overflow-x-auto"><table class="w-full min-w-[600px] text-left text-sm"><thead class="bg-white/35 text-[10px] uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">Sản phẩm</th><th class="px-5 py-3 text-right">SL</th><th class="px-5 py-3 text-right">Đơn giá</th><th class="px-5 py-3 text-right">Thành tiền</th></tr></thead><tbody class="divide-y divide-white/80">@foreach ($order->items as $item)<tr><td class="px-5 py-3"><strong>{{ $item->product_name }}</strong><span class="mt-1 block font-mono text-xs text-slate-500">{{ $item->sku }}</span></td><td class="px-5 py-3 text-right">{{ $item->quantity }}</td><td class="px-5 py-3 text-right">{{ number_format((float) $item->unit_price, 0, ',', '.') }}₫</td><td class="px-5 py-3 text-right font-bold">{{ number_format((float) $item->line_total, 0, ',', '.') }}₫</td></tr>@endforeach</tbody></table></div>
                <div class="border-t border-white/80 p-5"><div class="ml-auto grid max-w-xs gap-2 text-sm"><div class="flex justify-between"><span class="text-slate-500">Tạm tính</span><span>{{ number_format((float) $order->subtotal, 0, ',', '.') }}₫</span></div><div class="flex justify-between"><span class="text-slate-500">Giảm giá</span><span>-{{ number_format((float) $order->discount_total, 0, ',', '.') }}₫</span></div><div class="flex justify-between"><span class="text-slate-500">Giao hàng + VAT</span><span>{{ number_format((float) ($order->shipping_total + $order->vat_total), 0, ',', '.') }}₫</span></div><div class="flex justify-between border-t border-white pt-3 text-base font-black"><span>Tổng</span><span>{{ number_format((float) $order->total, 0, ',', '.') }}₫</span></div></div></div>
            </section>

            <section class="rounded-[24px] border border-white/85 bg-white/65 p-5 shadow-[0_18px_45px_-32px_rgba(24,38,76,.5)] backdrop-blur-xl">
                <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 class="font-extrabold text-[#172554]">Vận chuyển Viettel Post</h2><p class="mt-1 text-xs text-slate-500">Trạng thái hiện tại: {{ $shipmentLabels[$order->shipment_status] ?? $order->shipment_status }}</p></div>
                    @if ($order->shipment_number)<a href="{{ $order->shipment_tracking_url ?: 'https://viettelpost.com.vn/tra-cuu-hanh-trinh-don/' }}" target="_blank" rel="noopener" class="rounded-xl border border-white bg-white/70 px-3 py-2 text-xs font-bold text-indigo-800">{{ $isViettelPostMock ? 'Tra cứu demo ↗' : 'Tra cứu Viettel Post ↗' }}</a>@endif
                </div>
                @if ($order->shipment_number)
                    <div class="mt-4 grid gap-2 rounded-2xl border border-white bg-white/45 p-4 text-xs sm:grid-cols-3"><p><span class="text-slate-500">Mã vận đơn</span><strong class="mt-1 block font-mono text-[#172554]">{{ $order->shipment_number }}</strong></p><p><span class="text-slate-500">Dịch vụ</span><strong class="mt-1 block text-[#172554]">{{ $order->shipment_service ?: 'Viettel Post' }}</strong></p><p><span class="text-slate-500">Ngày giao dự kiến</span><strong class="mt-1 block text-[#172554]">{{ $order->estimated_delivery_at?->format('d/m/Y') ?? 'Chưa có dữ liệu' }}</strong></p></div>
                @endif

                @if ($canManageShipment && in_array($order->status, ['preparing', 'confirmed'], true))
                    @if ($isViettelPostMock)
                        <div class="mt-4 rounded-2xl bg-indigo-50/70 p-4"><p class="text-xs font-bold text-indigo-950">Tạo vận đơn demo</p><p class="mt-1 text-xs leading-5 text-indigo-900/75">Hệ thống tạo mã thử và ngày giao ước tính sau 3 ngày. Mã này không tồn tại trên Viettel Post thật.</p><form method="POST" action="{{ route('admin.orders.shipment', $order) }}" class="mt-3">@csrf<button class="rounded-xl bg-[#172554] px-4 py-2.5 text-xs font-bold text-white">Tạo vận đơn mô phỏng</button></form></div>
                    @else
                        <div class="mt-4 rounded-2xl bg-indigo-50/70 p-4"><p class="text-xs font-bold text-indigo-950">Tạo vận đơn tại Viettel Post Partner trước</p><p class="mt-1 text-xs leading-5 text-indigo-900/75">Sau khi VTP cấp mã vận đơn, nhập thông tin dưới đây để khách theo dõi. Việc lưu mã ở đây không tự tạo vận đơn trên VTP.</p><form method="POST" action="{{ route('admin.orders.shipment', $order) }}" class="mt-3 grid gap-2 sm:grid-cols-2">@csrf<input name="shipment_number" required maxlength="100" placeholder="Mã vận đơn Viettel Post" class="rounded-xl border-white bg-white/80 text-sm"><input name="shipment_service" maxlength="100" placeholder="Dịch vụ (tùy chọn)" class="rounded-xl border-white bg-white/80 text-sm"><input name="shipment_tracking_url" type="url" placeholder="Link tra cứu (tùy chọn)" class="rounded-xl border-white bg-white/80 text-sm"><input name="estimated_delivery_at" type="date" min="{{ now()->toDateString() }}" class="rounded-xl border-white bg-white/80 text-sm"><button class="rounded-xl bg-[#172554] px-4 py-2.5 text-xs font-bold text-white sm:col-span-2">Lưu mã · Chờ VTP nhận kiện</button></form></div>
                    @endif
                @endif

                @if ($canManageShipment && $nextStatuses)
                    <form method="POST" action="{{ route('admin.orders.shipment-status', $order) }}" class="mt-4 grid gap-2 rounded-2xl border border-white bg-white/45 p-4 sm:grid-cols-[1fr_1fr_auto]">@csrf @method('PATCH')<label class="grid gap-1 text-[10px] font-bold text-slate-500">{{ $isViettelPostMock ? 'Mô phỏng lần quét của Viettel Post' : 'Xác nhận sự kiện đã biết' }}<select name="status" class="rounded-xl border-white bg-white/80 text-xs">@foreach ($nextStatuses as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label><label class="grid gap-1 text-[10px] font-bold text-slate-500">Ghi chú sự kiện<input name="description" maxlength="255" placeholder="{{ $isViettelPostMock ? 'Ví dụ: mô phỏng quét nhận hàng tại bưu cục' : 'Ví dụ: bưu cục đã quét nhận hàng' }}" class="rounded-xl border-white bg-white/80 text-xs"></label><button class="self-end rounded-xl border border-white bg-white/80 px-4 py-2.5 text-xs font-bold text-[#172554]">{{ $isViettelPostMock ? 'Mô phỏng quét' : 'Lưu sự kiện' }}</button></form>
                @endif

                @if ($order->estimated_delivery_at)
                    <p class="mt-3 text-[11px] text-slate-500">Ngày giao là ước tính, có thể thay đổi theo cập nhật hành trình từ Viettel Post.</p>
                @endif
                <div class="mt-4 border-t border-white/80 pt-4"><h3 class="text-xs font-bold text-[#172554]">Lịch sử vận chuyển</h3><ol class="mt-3 grid gap-3">@forelse ($order->shipmentEvents as $event)<li class="flex gap-3 text-xs"><span class="mt-1.5 size-2 shrink-0 rounded-full bg-indigo-500"></span><span class="min-w-0"><strong class="font-semibold text-[#172554]">{{ $shipmentLabels[$event->status] ?? $event->status }} · {{ $event->description ?: ($event->source === 'viettelpost' ? 'Cập nhật từ Viettel Post' : 'Nhân viên cập nhật') }}</strong><span class="mt-0.5 block text-[10px] text-slate-500">{{ ($event->occurred_at ?? $event->created_at)->format('d/m/Y H:i') }} · {{ $event->source === 'viettelpost' ? 'Viettel Post' : ($event->source === 'simulation' ? 'Mô phỏng Viettel Post' : 'Quản lý') }}</span></span></li>@empty<li class="text-xs text-slate-500">Chưa có sự kiện vận chuyển.</li>@endforelse</ol></div>
            </section>

            <section class="rounded-[24px] border border-white/85 bg-white/65 p-5 shadow-[0_18px_45px_-32px_rgba(24,38,76,.5)] backdrop-blur-xl">
                <h2 class="font-extrabold text-[#172554]">Đổi trả / bảo hành</h2>
                @forelse ($order->returnRequests as $return)
                    <div class="mt-3 rounded-2xl bg-white/55 p-3 text-xs">
                        <strong>{{ $returnStatusLabels[$return->status] ?? $return->status }}</strong>
                        <p class="mt-1 text-slate-600">{{ $return->reason }}</p>
                        @if ($return->resolution)
                            <p class="mt-1 text-slate-600">Hướng xử lý: {{ $returnResolutionLabels[$return->resolution] ?? $return->resolution }}</p>
                        @endif
                        @if ($return->staff_note)
                            <p class="mt-1 text-slate-600">Ghi chú: {{ $return->staff_note }}</p>
                        @endif
                        @if ($return->evidence_path)
                            <a href="{{ route('admin.returns.evidence', $return) }}" class="mt-2 inline-block font-bold text-indigo-800 underline">Tải minh chứng</a>
                        @endif
                    </div>
                    @if ($return->status === 'pending')
                        <form method="POST" action="{{ route('admin.returns.update', $return) }}" class="mt-3 grid gap-2">
                            @csrf @method('PATCH')
                            <select name="resolution" class="rounded-xl border-white bg-white/80 text-xs">
                                <option value="exchange">Đổi hàng</option>
                                <option value="refund">Hoàn tiền toàn bộ đơn</option>
                                <option value="warranty">Bảo hành</option>
                            </select>
                            <textarea name="staff_note" rows="2" placeholder="Ghi chú xử lý gửi khách" class="rounded-xl border-white bg-white/80 text-xs"></textarea>
                            @if ($order->payment_status !== 'paid')
                                <p class="text-[10px] text-amber-800">Đơn chưa được ghi nhận đã thanh toán nên chưa thể duyệt hoàn tiền.</p>
                            @endif
                            <div class="flex gap-2">
                                <button name="status" value="accepted" class="rounded-xl bg-[#172554] px-3 py-2 text-[10px] font-bold text-white">Chấp nhận</button>
                                <button name="status" value="rejected" class="rounded-xl border border-rose-200 bg-white/70 px-3 py-2 text-[10px] font-bold text-rose-700">Từ chối</button>
                            </div>
                        </form>
                    @endif
                    @if ($return->status === 'accepted' && $return->resolution === 'refund')
                        <form method="POST" action="{{ route('admin.returns.receive', $return) }}" class="mt-3">
                            @csrf @method('PATCH')
                            <button class="w-full rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-[10px] font-bold text-emerald-900">Xác nhận đã nhận hàng trả về</button>
                        </form>
                    @endif
                @empty
                    <p class="mt-2 text-xs text-slate-500">Chưa có yêu cầu đổi trả.</p>
                @endforelse
            </section>
        </div>

        <aside class="grid content-start gap-4">
            <section class="rounded-[24px] border border-white/85 bg-white/65 p-5 shadow-[0_18px_45px_-32px_rgba(24,38,76,.5)] backdrop-blur-xl"><h2 class="font-extrabold text-[#172554]">Khách hàng & giao nhận</h2><div class="mt-4 grid gap-3 text-sm"><p><strong>{{ $order->customer_name }}</strong><span class="mt-0.5 block text-xs text-slate-500">{{ $order->customer_phone }}</span><span class="block text-xs text-slate-500">{{ $order->customer_email }}</span></p><p class="border-t border-white pt-3 text-xs leading-5 text-slate-600">{{ $order->shipping_address }}@if ($order->shipping_city), {{ $order->shipping_city }}@endif</p>@if ($order->company_name)<p class="border-t border-white pt-3 text-xs text-slate-600">{{ $order->company_name }} · MST {{ $order->tax_number }}</p>@endif<p class="rounded-xl bg-white/50 p-3 text-xs">{{ $order->customer_note ?: 'Không có ghi chú.' }}</p></div></section>

            <section class="rounded-[24px] border border-white/85 bg-white/65 p-5 shadow-[0_18px_45px_-32px_rgba(24,38,76,.5)] backdrop-blur-xl"><h2 class="font-extrabold text-[#172554]">Thanh toán</h2><div class="mt-3 grid gap-2 text-xs"><p><span class="text-slate-500">Phương thức</span><strong class="mt-0.5 block text-[#172554]">{{ $order->payment_method === 'cod' ? 'Thu hộ COD' : ($isMockPayment ? 'Thanh toán online · Mô phỏng' : 'Chuyển khoản / online') }}</strong></p><p><span class="text-slate-500">Thanh toán</span><strong class="mt-0.5 block text-[#172554]">{{ ['pending' => 'Chưa thu', 'paid' => 'Đã thanh toán', 'refunded' => 'Đã hoàn tiền'][$order->payment_status] ?? $order->payment_status }}</strong></p>
                @if ($isMockPayment)<p><span class="text-slate-500">Tham chiếu demo</span><strong class="mt-0.5 block font-mono text-[#172554]">{{ $order->paymentIntent?->reference ?: 'Chưa có' }}</strong><span class="mt-1 block text-[10px] text-amber-800">Giao dịch mô phỏng, không thu tiền thật.</span></p>@endif
                @if ($order->payment_method === 'cod')<p class="border-t border-white pt-3"><span class="text-slate-500">Đối soát COD</span><strong class="mt-0.5 block text-[#172554]">{{ ['pending_collection' => 'Chờ thu từ người nhận', 'collected' => 'Đã thu hộ', 'pending_reconciliation' => 'Chờ Viettel Post đối soát', 'reconciled' => 'Đã đối soát'][$order->cod_status] ?? $order->cod_status }}</strong></p>@endif
                @if ($order->refund_status !== 'not_required')<p class="border-t border-white pt-3"><span class="text-slate-500">Hoàn tiền</span><strong class="mt-0.5 block {{ $order->refund_status === 'pending' ? 'text-amber-700' : 'text-emerald-700' }}">{{ $order->refund_status === 'pending' ? 'Đang chờ xử lý' : 'Đã hoàn · '.$order->refund_reference }}</strong></p>@endif
            </div>
                @if ($canManageFinance && $order->payment_method === 'cod' && $order->cod_status !== 'not_applicable')
                    @php($codNext = ['pending_collection' => 'collected', 'collected' => 'pending_reconciliation', 'pending_reconciliation' => 'reconciled'][$order->cod_status] ?? null)
                    @if ($codNext)<form method="POST" action="{{ route('admin.orders.cod', $order) }}" class="mt-4">@csrf @method('PATCH')<input type="hidden" name="cod_status" value="{{ $codNext }}"><button class="w-full rounded-xl border border-white bg-white/70 px-3 py-2.5 text-xs font-bold text-[#172554]">{{ ['collected' => 'Xác nhận đã thu COD', 'pending_reconciliation' => 'Chuyển sang chờ đối soát', 'reconciled' => 'Xác nhận đã đối soát'][$codNext] }}</button></form>@endif
                @endif
            </section>

            @if ($isOrderManager && in_array($order->status, ['pending_confirmation', 'pending_payment'], true))
                <section class="rounded-[24px] border border-rose-200/70 bg-rose-50/65 p-5 shadow-sm"><h2 class="font-extrabold text-rose-900">Hủy đơn hàng</h2><p class="mt-1 text-xs leading-5 text-rose-800">Chỉ hủy được trước khi xác nhận chuẩn bị hàng. Bắt buộc ghi lý do; nếu đã thanh toán online, đơn sẽ chuyển sang chờ hoàn tiền.</p><form method="POST" action="{{ route('admin.orders.cancel', $order) }}" class="mt-3 grid gap-2">@csrf<textarea name="reason" required minlength="5" maxlength="1000" rows="3" placeholder="Nhập lý do hủy cụ thể" class="rounded-xl border-rose-200 bg-white/80 text-xs"></textarea><button class="rounded-xl border border-rose-200 bg-white/75 px-4 py-2.5 text-xs font-bold text-rose-700">Hủy đơn</button></form></section>
            @endif

            @if ($canManageFinance && $order->refund_status === 'pending')
                <section class="rounded-[24px] border border-amber-200/80 bg-amber-50/65 p-5">
                    <h2 class="font-extrabold text-amber-950">Hoàn tiền cần xử lý</h2>
                    <p class="mt-1 text-xs text-amber-900">Số tiền hoàn toàn bộ đơn: <strong>{{ number_format((float) $order->total, 0, ',', '.') }}₫</strong></p>
                    <form method="POST" action="{{ route('admin.orders.refund', $order) }}" class="mt-3 grid gap-2">
                        @csrf @method('PATCH')
                        @if (config('services.payment.driver') === 'mock')
                            <p class="text-xs leading-5 text-amber-900">Chế độ mô phỏng: thao tác này chỉ tạo mã giao dịch giả, không chuyển tiền thật.</p>
                            <button class="rounded-xl bg-amber-800 px-4 py-2.5 text-xs font-bold text-white">Mô phỏng chuyển khoản hoàn tiền</button>
                        @else
                            <p class="text-xs leading-5 text-amber-900">Hãy chuyển tiền qua ngân hàng/cổng thanh toán trước, sau đó ghi lại mã giao dịch tại đây.</p>
                            <input required name="refund_reference" maxlength="120" placeholder="Mã giao dịch hoàn tiền" class="rounded-xl border-amber-200 bg-white/80 text-xs">
                            <button class="rounded-xl bg-amber-800 px-4 py-2.5 text-xs font-bold text-white">Ghi nhận đã chuyển tiền</button>
                        @endif
                    </form>
                </section>
            @endif

            @if ($order->status === 'cancelled')<section class="rounded-[24px] border border-rose-200/70 bg-rose-50/65 p-5"><h2 class="font-extrabold text-rose-900">Đã hủy đơn hàng</h2><p class="mt-2 text-xs leading-5 text-rose-800">{{ $order->cancellation_reason ?: 'Không có lý do được lưu.' }}</p><p class="mt-2 text-[10px] text-rose-700">{{ $order->canceller?->name ?: 'Hệ thống' }} · {{ $order->cancelled_at?->format('d/m/Y H:i') }}</p></section>@endif
        </aside>
    </div>
</div>
@endsection
