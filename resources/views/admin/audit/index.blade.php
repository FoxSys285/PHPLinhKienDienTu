@extends('layouts.admin')
@section('title', 'Nhật ký thao tác')
@section('content')
@php
    $actionLabels = [
        'product.created' => 'Tạo sản phẩm',
        'product.updated' => 'Cập nhật sản phẩm',
        'product.hidden' => 'Ẩn sản phẩm',
        'inventory.updated' => 'Cập nhật tồn kho',
        'order.created' => 'Tạo đơn hàng',
        'order.confirmed' => 'Xác nhận đơn hàng',
        'order.status_changed' => 'Đổi trạng thái đơn hàng',
        'order.cancelled' => 'Hủy đơn hàng',
        'user.permissions_updated' => 'Cập nhật quyền tài khoản',
        'order.payment_expired' => 'Thanh toán hết hạn',
    ];
    $actionOptions = [
        'product.created' => 'Tạo sản phẩm',
        'product.updated' => 'Sửa sản phẩm',
        'product.hidden' => 'Ẩn sản phẩm',
        'inventory.updated' => 'Cập nhật kho',
        'order.created' => 'Tạo đơn',
        'order.confirmed' => 'Xác nhận đơn',
        'order.status_changed' => 'Đổi trạng thái đơn',
        'order.cancelled' => 'Hủy đơn',
        'user.permissions_updated' => 'Cập nhật quyền',
        'order.payment_expired' => 'Hết hạn thanh toán',
    ];
    $summaryCards = [
        ['label' => 'Tổng sự kiện', 'value' => $stats['total'], 'note' => 'Được ghi nhận trong hệ thống', 'tone' => 'text-blue-700', 'icon' => 'LOG'],
        ['label' => 'Phát sinh hôm nay', 'value' => $stats['today'], 'note' => now()->format('d/m/Y'), 'tone' => 'text-violet-600', 'icon' => '24h'],
        ['label' => 'Đang hiển thị', 'value' => $logs->count(), 'note' => 'Trên trang kết quả hiện tại', 'tone' => 'text-emerald-600', 'icon' => '↕'],
    ];
@endphp

<div class="mx-auto max-w-screen-2xl">
    <div class="mb-7">
        <p class="text-xs font-bold uppercase tracking-[.18em] text-blue-600">Tuân thủ & vận hành</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-[#172554] sm:text-4xl">Nhật ký thao tác</h1>
        <p class="mt-2 text-sm text-slate-500">Tra cứu thay đổi giá, tồn kho, đơn hàng và quyền tài khoản theo thời gian.</p>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach ($summaryCards as $card)
            <div class="group relative overflow-hidden rounded-2xl border border-white/80 bg-white/75 p-5 shadow-[0_14px_36px_-28px_rgba(23,37,84,.4)] backdrop-blur transition hover:-translate-y-0.5 hover:shadow-lg">
                <span class="absolute -right-5 -top-7 size-24 rounded-full bg-blue-50/80 transition group-hover:scale-125"></span>
                <div class="relative flex items-start justify-between gap-3"><div><p class="text-xs font-semibold text-slate-500">{{ $card['label'] }}</p><p class="mt-3 text-3xl font-extrabold tracking-tight {{ $card['tone'] }}">{{ number_format($card['value']) }}</p><p class="mt-1 text-[11px] text-slate-400">{{ $card['note'] }}</p></div><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-[10px] font-black text-blue-700">{{ $card['icon'] }}</span></div>
            </div>
        @endforeach
    </div>

    <section class="overflow-hidden rounded-2xl border border-white/80 bg-white/80 shadow-sm">
        <form method="GET" class="flex flex-wrap items-center gap-3 border-b border-slate-100 p-4">
            <label><span class="sr-only">Lọc loại hoạt động</span><select name="action" class="rounded-xl border-slate-200 px-3 py-2.5 text-sm"><option value="">Tất cả hoạt động</option>@foreach ($actionOptions as $value => $label)<option value="{{ $value }}" @selected(request('action') === $value)>{{ $label }}</option>@endforeach</select></label>
            <button class="rounded-xl bg-[#172554] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-900">Lọc nhật ký</button>
            @if (request()->filled('action'))<a href="{{ route('admin.audit.index') }}" class="px-2 text-xs font-semibold text-slate-500 hover:text-blue-700">Xem tất cả</a>@endif
            <span class="ml-auto text-xs text-slate-400">{{ number_format($logs->total()) }} sự kiện phù hợp</span>
        </form>

        <div class="divide-y divide-slate-100">
            @forelse ($logs as $log)
                <article class="group relative grid gap-4 px-5 py-5 transition hover:bg-blue-50/25 sm:grid-cols-[minmax(150px,.55fr)_minmax(220px,.8fr)_minmax(260px,1.4fr)]">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 grid size-10 shrink-0 place-items-center rounded-xl border border-blue-100 bg-blue-50 text-xs font-black text-blue-700">{{ mb_strtoupper(mb_substr($log->user?->name ?? 'HT', 0, 1)) }}</span>
                        <div class="min-w-0"><strong class="block truncate text-xs font-bold text-[#172554]">{{ $log->user?->name ?? 'Hệ thống / khách' }}</strong><time class="mt-1 block text-[11px] leading-5 text-slate-500" datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('d/m/Y') }}<span class="block font-semibold text-slate-400">{{ $log->created_at->format('H:i:s') }}</span></time></div>
                    </div>
                    <div>
                        <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] font-bold text-indigo-700">{{ $actionLabels[$log->action] ?? str($log->action)->replace('.', ' ')->ucfirst() }}</span>
                        <p class="mt-2 break-all text-xs font-semibold text-[#172554]">{{ class_basename($log->subject_type) ?: 'Sự kiện hệ thống' }}{{ $log->subject_id ? ' #'.$log->subject_id : '' }}</p>
                        <p class="mt-1 text-[10px] text-slate-400">IP {{ $log->ip_address ?? 'không xác định' }}</p>
                    </div>
                    <div class="min-w-0">
                        @if ($log->old_values || $log->new_values)
                            <details class="rounded-xl border border-slate-100 bg-slate-50/80 p-3">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-xs font-bold text-slate-600"><span>Chi tiết thay đổi</span><span class="text-blue-600 transition group-open:rotate-180" aria-hidden="true">⌄</span></summary>
                                <div class="mt-3 grid gap-2 md:grid-cols-2">
                                    <div class="min-w-0 rounded-lg bg-white p-3"><p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Trước khi đổi</p><pre class="mt-2 max-h-40 overflow-auto whitespace-pre-wrap break-words font-mono text-[10px] leading-5 text-slate-600">{{ $log->old_values ? json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '—' }}</pre></div>
                                    <div class="min-w-0 rounded-lg bg-white p-3"><p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Sau khi đổi</p><pre class="mt-2 max-h-40 overflow-auto whitespace-pre-wrap break-words font-mono text-[10px] leading-5 text-slate-600">{{ $log->new_values ? json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '—' }}</pre></div>
                                </div>
                            </details>
                        @else
                            <p class="rounded-xl bg-slate-50 px-3 py-2.5 text-xs text-slate-400">Sự kiện không có dữ liệu so sánh.</p>
                        @endif
                    </div>
                </article>
            @empty
                <div class="px-6 py-16 text-center"><span class="mx-auto grid size-12 place-items-center rounded-2xl bg-blue-50 text-xl text-blue-600">⌕</span><strong class="mt-4 block text-sm text-[#172554]">Chưa có sự kiện phù hợp</strong><p class="mt-1 text-xs text-slate-500">Nhật ký vận hành sẽ xuất hiện tại đây khi có hoạt động được ghi nhận.</p>@if (request()->filled('action'))<a href="{{ route('admin.audit.index') }}" class="mt-3 inline-flex text-xs font-bold text-blue-700 hover:underline">Xóa bộ lọc</a>@endif</div>
            @endforelse
        </div>
        <div class="border-t border-slate-100 p-4">{{ $logs->links() }}</div>
    </section>
</div>
@endsection
