@extends('layouts.admin')

@section('title', 'Yêu cầu liên hệ')

@section('content')
@php
    $summaryCards = [
        ['label' => 'Tổng tin nhắn', 'value' => $stats['total'], 'note' => 'Từ biểu mẫu liên hệ', 'tone' => 'text-blue-700', 'icon' => 'IN'],
        ['label' => 'Tin nhắn mới', 'value' => $stats['new'], 'note' => 'Chưa được tiếp nhận', 'tone' => 'text-amber-600', 'icon' => 'NEW'],
        ['label' => 'Đang xử lý', 'value' => $stats['in_progress'], 'note' => 'Đang chờ phản hồi', 'tone' => 'text-blue-600', 'icon' => '…'],
        ['label' => 'Đã hoàn tất', 'value' => $stats['resolved'], 'note' => 'Đã xử lý xong', 'tone' => 'text-emerald-600', 'icon' => '✓'],
    ];
    $statusLabels = ['new' => 'Mới', 'in_progress' => 'Đang xử lý', 'resolved' => 'Đã xử lý'];
@endphp

<div class="mx-auto max-w-screen-2xl">
    <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-xs font-bold uppercase tracking-[.18em] text-blue-600">Hộp thư cửa hàng</p><h1 class="mt-2 text-3xl font-extrabold tracking-tight text-[#172554] sm:text-4xl">Yêu cầu liên hệ</h1><p class="mt-2 text-sm text-slate-500">Tiếp nhận câu hỏi và theo dõi tiến độ hỗ trợ khách hàng.</p></div>
        <div class="rounded-2xl border border-white/80 bg-white/70 px-4 py-3 text-right shadow-sm"><p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Đang hiển thị</p><p class="mt-1 text-sm font-extrabold text-[#172554]">{{ number_format($messages->total()) }} <span class="font-medium text-slate-500">tin nhắn</span></p></div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach ($summaryCards as $card)
            <div class="group relative overflow-hidden rounded-2xl border border-white/80 bg-white/75 p-5 shadow-[0_14px_36px_-28px_rgba(23,37,84,.4)] backdrop-blur transition hover:-translate-y-0.5 hover:shadow-lg">
                <span class="absolute -right-5 -top-7 size-24 rounded-full bg-blue-50/80 transition group-hover:scale-125"></span>
                <div class="relative flex items-start justify-between gap-3"><div><p class="text-xs font-semibold text-slate-500">{{ $card['label'] }}</p><p class="mt-3 text-3xl font-extrabold tracking-tight {{ $card['tone'] }}">{{ number_format($card['value']) }}</p><p class="mt-1 text-[11px] text-slate-400">{{ $card['note'] }}</p></div><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-[10px] font-black text-blue-700">{{ $card['icon'] }}</span></div>
            </div>
        @endforeach
    </div>

    <section class="overflow-hidden rounded-2xl border border-white/80 bg-white/80 shadow-sm">
        <form method="GET" class="flex flex-wrap items-center gap-3 border-b border-slate-100 p-4">
            <label class="min-w-48 flex-1"><span class="sr-only">Tìm nội dung liên hệ</span><input name="q" value="{{ request('q') }}" placeholder="Tìm theo người gửi, email, tiêu đề hoặc nội dung..." class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm"></label>
            <label><span class="sr-only">Lọc trạng thái xử lý</span><select name="status" class="rounded-xl border-slate-200 px-3 py-2.5 text-sm"><option value="">Tất cả trạng thái</option>@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></label>
            <button class="rounded-xl bg-[#172554] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-900">Tìm tin nhắn</button>
            @if (request()->filled('q') || request()->filled('status'))<a href="{{ route('admin.contact-messages.index') }}" class="px-2 text-xs font-semibold text-slate-500 hover:text-blue-700">Đặt lại</a>@endif
        </form>

        <div class="grid gap-4 p-4 sm:p-5">
            @forelse ($messages as $message)
                <article class="group relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-5 shadow-[0_12px_32px_-28px_rgba(23,37,84,.55)] transition hover:-translate-y-0.5 hover:border-blue-100 hover:shadow-lg sm:p-6">
                    <span @class(['absolute inset-y-0 left-0 w-1', 'bg-amber-400' => $message->status === 'new', 'bg-blue-500' => $message->status === 'in_progress', 'bg-emerald-400' => $message->status === 'resolved'])></span>
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="flex min-w-0 flex-1 gap-4">
                            <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-100 text-sm font-black text-[#172554]">{{ mb_strtoupper(mb_substr($message->name, 0, 1)) }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2"><h2 class="text-base font-extrabold text-[#172554]">{{ $message->subject }}</h2><span @class(['rounded-full px-2.5 py-1 text-[10px] font-bold', 'bg-amber-50 text-amber-700' => $message->status === 'new', 'bg-blue-50 text-blue-700' => $message->status === 'in_progress', 'bg-emerald-50 text-emerald-700' => $message->status === 'resolved'])>{{ $statusLabels[$message->status] ?? $message->status }}</span></div>
                                <p class="mt-1.5 text-xs font-semibold text-slate-600">{{ $message->name }}</p>
                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs"><a class="font-semibold text-blue-700 hover:underline" href="mailto:{{ $message->email }}">{{ $message->email }}</a>@if ($message->phone)<a class="font-semibold text-blue-700 hover:underline" href="tel:{{ $message->phone }}">{{ $message->phone }}</a>@endif</div>
                                <div class="mt-4 rounded-xl bg-slate-50/90 p-4"><p class="whitespace-pre-line break-words text-sm leading-6 text-slate-700">{{ $message->message }}</p></div>
                                <p class="mt-3 text-[11px] text-slate-400">Nhận lúc {{ $message->created_at->format('d/m/Y · H:i') }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.contact-messages.update', $message) }}" class="flex shrink-0 flex-wrap items-end gap-2 rounded-xl bg-slate-50 p-3 lg:min-w-[255px]">
                            @csrf
                            @method('PATCH')
                            <label class="grid min-w-36 flex-1 gap-1.5 text-[10px] font-bold text-slate-500"><span>Cập nhật trạng thái</span><select name="status" class="rounded-lg border-slate-200 bg-white text-xs font-semibold text-[#172554]">@foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected($message->status === $value)>{{ $label }}</option>@endforeach</select></label>
                            <button class="rounded-lg bg-[#172554] px-3.5 py-2.5 text-xs font-bold text-white transition hover:bg-blue-900">Lưu</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/65 px-5 py-16 text-center"><span class="mx-auto grid size-12 place-items-center rounded-2xl bg-white text-xl text-blue-600 shadow-sm">✉</span><p class="mt-4 text-base font-extrabold text-[#172554]">{{ request()->filled('q') || request()->filled('status') ? 'Không tìm thấy tin nhắn phù hợp' : 'Hộp thư đang trống' }}</p><p class="mt-1 text-sm text-slate-500">{{ request()->filled('q') || request()->filled('status') ? 'Thử đổi từ khóa hoặc trạng thái để tìm yêu cầu.' : 'Tin nhắn gửi từ biểu mẫu liên hệ sẽ xuất hiện tại đây.' }}</p>@if (request()->filled('q') || request()->filled('status'))<a href="{{ route('admin.contact-messages.index') }}" class="mt-3 inline-flex text-xs font-bold text-blue-700 hover:underline">Xóa bộ lọc</a>@endif</div>
            @endforelse
        </div>
        @if ($messages->hasPages())<div class="border-t border-slate-100 p-4">{{ $messages->links() }}</div>@endif
    </section>
</div>
@endsection
