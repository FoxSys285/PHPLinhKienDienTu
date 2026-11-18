@extends('layouts.admin')

@section('title', 'Yêu cầu liên hệ')

@section('content')
<div class="mx-auto max-w-[1500px]">
    <div class="mb-6 px-1"><p class="text-xs font-black uppercase tracking-[.18em] text-blue-700">Hộp thư cửa hàng</p><h1 class="mt-1 text-3xl font-black tracking-tight text-[#172554] sm:text-4xl">Yêu cầu liên hệ</h1><p class="mt-2 text-sm text-slate-500">Tin nhắn từ biểu mẫu Liên hệ trên website.</p></div>
    <div class="grid gap-4">
        @forelse ($messages as $message)
            <article class="rounded-[26px] border border-white/80 bg-white/65 p-5 shadow-[0_18px_45px_-32px_rgba(24,38,76,.45)] backdrop-blur-xl sm:p-6">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2"><h2 class="text-lg font-extrabold text-[#172554]">{{ $message->subject }}</h2><span @class(['rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wide', 'bg-amber-100 text-amber-800' => $message->status === 'new', 'bg-blue-100 text-blue-800' => $message->status === 'in_progress', 'bg-emerald-100 text-emerald-800' => $message->status === 'resolved'])>{{ ['new' => 'Mới', 'in_progress' => 'Đang xử lý', 'resolved' => 'Đã xử lý'][$message->status] }}</span></div>
                        <p class="mt-2 text-xs font-medium text-slate-500">{{ $message->name }} · <a class="font-bold text-blue-800 hover:underline" href="mailto:{{ $message->email }}">{{ $message->email }}</a>@if ($message->phone) · <a class="font-bold text-blue-800 hover:underline" href="tel:{{ $message->phone }}">{{ $message->phone }}</a>@endif</p>
                        @if ($message->company)<p class="mt-1 text-xs text-slate-500">{{ $message->company }}</p>@endif
                        <p class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $message->message }}</p>
                        <p class="mt-4 text-[11px] text-slate-400">Gửi lúc {{ $message->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.contact-messages.update', $message) }}" class="flex shrink-0 items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <label class="sr-only" for="status-{{ $message->id }}">Trạng thái</label>
                        <select id="status-{{ $message->id }}" name="status" class="rounded-xl border-slate-200 bg-white/80 text-xs font-semibold">
                            <option value="new" @selected($message->status === 'new')>Mới</option>
                            <option value="in_progress" @selected($message->status === 'in_progress')>Đang xử lý</option>
                            <option value="resolved" @selected($message->status === 'resolved')>Đã xử lý</option>
                        </select>
                        <button class="rounded-xl bg-[#172554] px-3.5 py-2.5 text-xs font-bold text-white transition hover:bg-blue-900">Lưu</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="rounded-[26px] border border-dashed border-slate-300 bg-white/55 px-5 py-16 text-center"><p class="text-lg font-extrabold text-[#172554]">Chưa có yêu cầu liên hệ</p><p class="mt-2 text-sm text-slate-500">Tin nhắn gửi từ website sẽ xuất hiện tại đây.</p></div>
        @endforelse
    </div>
    @if ($messages->hasPages())<div class="mt-5 rounded-2xl border border-white/80 bg-white/50 p-4">{{ $messages->links() }}</div>@endif
</div>
@endsection
