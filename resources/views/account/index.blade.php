@extends('layouts.store')

@section('title', 'Tài khoản của tôi')

@section('content')
    @php
        $user = auth()->user();
        $orderLabels = [
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
            'cancelled' => 'bg-rose-50 text-rose-800 ring-rose-200',
            'returned' => 'bg-cyan-50 text-cyan-800 ring-cyan-200',
        ];
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-7 sm:py-10">
        <section class="relative isolate overflow-hidden rounded-[28px] bg-[#0d294b] px-6 py-7 text-white shadow-[0_20px_55px_rgba(20,67,125,.18)] sm:px-9 sm:py-9">
            <div class="pointer-events-none absolute -right-16 -top-28 -z-10 size-80 rounded-full bg-blue-500/25 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-36 right-1/3 -z-10 size-72 rounded-full bg-cyan-400/15 blur-3xl"></div>

            <div class="relative flex flex-wrap items-end justify-between gap-7">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[.16em] text-blue-100">
                        <span class="size-1.5 rounded-full bg-cyan-300"></span>
                        Trung tâm tài khoản
                    </div>
                    <h1 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">Xin chào, {{ $user->name }}</h1>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-blue-100/80 sm:text-base">Theo dõi đơn hàng và cập nhật thông tin của bạn tại một nơi.</p>
                </div>

                <div class="flex w-full flex-wrap gap-2.5 sm:w-auto">
                    <a href="{{ route('account.profile.edit') }}" class="inline-flex min-h-11 flex-1 items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/15 focus:outline-2 focus:outline-offset-2 focus:outline-white sm:flex-none">
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/></svg>
                        Hồ sơ tài khoản
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="flex flex-1 sm:flex-none">
                        @csrf
                        <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-rose-200/30 bg-rose-500/15 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-500/25 focus:outline-2 focus:outline-offset-2 focus:outline-white">
                            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 17l5-5-5-5m5 5H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg>
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </section>

        @if ($user->account_type === 'business')
            <section @class([
                'mt-5 flex flex-col gap-4 rounded-2xl border p-5 sm:flex-row sm:items-center sm:justify-between sm:px-6',
                'border-emerald-200 bg-gradient-to-r from-emerald-50 to-white' => $user->business_approved,
                'border-amber-200 bg-gradient-to-r from-amber-50 to-white' => ! $user->business_approved,
            ]) aria-live="polite">
                <div class="flex items-start gap-3.5">
                    <span @class([
                        'grid size-11 shrink-0 place-items-center rounded-xl bg-white shadow-sm',
                        'text-emerald-800' => $user->business_approved,
                        'text-amber-800' => ! $user->business_approved,
                    ])>
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 21h18M5 21V7l8-4v18m6 0V11l-6-4"/><path d="M9 9v.01M9 12v.01M9 15v.01m7-1v.01m0 3v.01"/></svg>
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-sm font-bold text-slate-900">{{ $user->business_approved ? 'Tài khoản doanh nghiệp đã được duyệt' : 'Hồ sơ doanh nghiệp đang chờ duyệt' }}</h2>
                            <span @class([
                                'rounded-full px-2.5 py-1 text-[10px] font-bold ring-1 ring-inset',
                                'bg-emerald-100 text-emerald-800 ring-emerald-200' => $user->business_approved,
                                'bg-amber-100 text-amber-800 ring-amber-200' => ! $user->business_approved,
                            ])>{{ $user->business_approved ? 'Đã xác minh' : 'Đang xét duyệt' }}</span>
                        </div>
                        <p class="mt-1 text-xs leading-5 text-slate-600">
                            @if ($user->business_approved)
                                {{ $user->company_name ?: 'Hồ sơ doanh nghiệp' }} · Đang áp dụng nhóm giá <strong class="font-bold text-slate-800">{{ $user->price_group }}</strong> cho đơn hàng.
                            @else
                                {{ $user->company_name ?: 'Hồ sơ doanh nghiệp' }} · Đội ngũ kinh doanh sẽ kiểm tra thông tin và cập nhật quyền lợi B2B sau khi duyệt.
                            @endif
                        </p>
                    </div>
                </div>
                <a href="{{ route('account.profile.edit') }}" class="inline-flex shrink-0 items-center gap-2 text-xs font-bold text-[#164d8b] hover:text-blue-700 focus:outline-2 focus:outline-offset-4 focus:outline-blue-600">
                    Cập nhật hồ sơ
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                </a>
            </section>
        @endif

        <div class="mt-7 grid gap-5 lg:grid-cols-1 lg:gap-6">
            <section class="overflow-hidden rounded-2xl border border-[#dce6f2] bg-white shadow-[0_8px_24px_rgba(27,59,100,.045)]">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <span class="grid size-10 place-items-center rounded-xl bg-blue-50 text-blue-700">
                            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2.2 11.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20 9H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                        </span>
                        <div>
                            <h2 class="font-bold text-[#10283e]">Đơn hàng gần đây</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Cập nhật tình trạng các đơn hàng</p>
                        </div>
                    </div>
                    <a href="{{ route('account.orders.index') }}" class="inline-flex shrink-0 items-center gap-1 text-xs font-bold text-[#164d8b] hover:text-blue-700">
                        Lịch sử đơn hàng
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($orders as $order)
                        <a href="{{ route('account.orders.show', $order) }}" class="group flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-blue-50/50 sm:px-6">
                            <span class="flex min-w-0 items-center gap-3">
                                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-slate-50 text-slate-500 transition group-hover:bg-white group-hover:text-blue-700">
                                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 4h14v16H5zM8 8h8m-8 4h8m-8 4h4"/></svg>
                                </span>
                                <span class="min-w-0">
                                    <strong class="block truncate font-mono text-xs text-slate-900">{{ $order->order_number }}</strong>
                                    <span class="mt-1 block text-xs text-slate-500">{{ $order->created_at->format('d/m/Y') }} · {{ number_format((float) $order->total, 0, ',', '.') }}₫</span>
                                </span>
                            </span>
                            <span @class([
                                'shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold ring-1 ring-inset',
                                $orderStatusStyles[$order->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200',
                            ])>{{ $orderLabels[$order->status] ?? $order->status }}</span>
                        </a>
                    @empty
                        <div class="px-5 py-8 text-center sm:px-6 sm:py-9">
                            <span class="mx-auto grid size-12 place-items-center rounded-2xl bg-blue-50 text-blue-700">
                                <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3 4h2l2.2 11.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20 9H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                            </span>
                            <h3 class="mt-3 text-sm font-bold text-slate-800">Bạn chưa có đơn hàng nào</h3>
                            <p class="mt-1 text-xs text-slate-500">Khám phá danh mục linh kiện để bắt đầu mua sắm.</p>
                            <a href="{{ route('products.index') }}" class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-[#164d8b] hover:text-blue-700">Khám phá sản phẩm <span aria-hidden="true">→</span></a>
                        </div>
                    @endforelse
                </div>
            </section>

        </div>
    </div>
@endsection
