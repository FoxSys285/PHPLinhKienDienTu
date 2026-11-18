@extends('layouts.store')

@section('title', 'Liên hệ cửa hàng')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-12">
    <section class="overflow-hidden rounded-[2rem] border border-blue-100 bg-white shadow-[0_20px_60px_rgba(26,50,94,.1)]">
        <div class="grid lg:grid-cols-[.78fr_1.22fr]">
            <div class="relative isolate overflow-hidden bg-gradient-to-br from-[#102448] via-[#173b72] to-[#3154a2] p-6 text-white sm:p-10 lg:p-12">
                <div class="absolute -bottom-16 -right-16 size-64 rounded-full border-[36px] border-white/5"></div>
                <div class="relative">
                    <span class="inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-bold text-cyan-100">Hỗ trợ từ cửa hàng</span>
                    <h1 class="mt-5 text-3xl font-black tracking-tight sm:text-4xl">Chúng tôi có thể giúp gì cho bạn?</h1>
                    <p class="mt-4 text-sm leading-7 text-blue-100">Gửi câu hỏi về sản phẩm, đơn hàng, báo giá dự án hoặc linh kiện thay thế. Yêu cầu sẽ được lưu để đội ngũ cửa hàng tiếp nhận.</p>
                    <div class="mt-8 grid gap-3">
                        <a href="{{ route('products.index') }}" class="flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 p-4 transition hover:bg-white/15">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/15 text-lg">⌕</span>
                            <span><strong class="block text-sm">Tra cứu linh kiện</strong><span class="mt-1 block text-xs text-blue-100/75">Tìm theo mã SKU, MPN và thông số</span></span>
                        </a>
                        <a href="{{ route('pages.promotions') }}" class="flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 p-4 transition hover:bg-white/15">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/15 text-lg">✦</span>
                            <span><strong class="block text-sm">Ưu đãi đang áp dụng</strong><span class="mt-1 block text-xs text-blue-100/75">Xem mã và điều kiện của từng chương trình</span></span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="p-5 sm:p-8 lg:p-10">
                <div class="mb-6"><p class="text-xs font-black uppercase tracking-[.18em] text-blue-700">Biểu mẫu hỗ trợ</p><h2 class="mt-2 text-2xl font-black text-[#142747]">Gửi yêu cầu liên hệ</h2><p class="mt-2 text-sm leading-6 text-slate-600">Các trường có dấu * là bắt buộc. Thông tin của bạn chỉ dùng để phản hồi yêu cầu này.</p></div>

                @if (session('success'))
                    <div role="status" class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" class="grid gap-4 sm:grid-cols-2">
                    @csrf
                    <label class="grid gap-1.5 text-xs font-bold text-slate-700">Họ và tên *<input name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="120" class="rounded-xl border-slate-200 px-3.5 py-3 text-sm font-normal focus:border-blue-500 focus:ring-blue-500" placeholder="Nguyễn Văn An">@error('name')<span class="text-xs font-medium text-rose-600">{{ $message }}</span>@enderror</label>
                    <label class="grid gap-1.5 text-xs font-bold text-slate-700">Email *<input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required maxlength="255" class="rounded-xl border-slate-200 px-3.5 py-3 text-sm font-normal focus:border-blue-500 focus:ring-blue-500" placeholder="ban@example.com">@error('email')<span class="text-xs font-medium text-rose-600">{{ $message }}</span>@enderror</label>
                    <label class="grid gap-1.5 text-xs font-bold text-slate-700">Số điện thoại<input type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" maxlength="30" class="rounded-xl border-slate-200 px-3.5 py-3 text-sm font-normal focus:border-blue-500 focus:ring-blue-500" placeholder="09xx xxx xxx">@error('phone')<span class="text-xs font-medium text-rose-600">{{ $message }}</span>@enderror</label>
                    <label class="grid gap-1.5 text-xs font-bold text-slate-700">Công ty / trường học<input name="company" value="{{ old('company') }}" autocomplete="organization" maxlength="255" class="rounded-xl border-slate-200 px-3.5 py-3 text-sm font-normal focus:border-blue-500 focus:ring-blue-500" placeholder="Không bắt buộc">@error('company')<span class="text-xs font-medium text-rose-600">{{ $message }}</span>@enderror</label>
                    <label class="grid gap-1.5 text-xs font-bold text-slate-700 sm:col-span-2">Chủ đề *<input name="subject" value="{{ old('subject', $subject) }}" required maxlength="120" class="rounded-xl border-slate-200 px-3.5 py-3 text-sm font-normal focus:border-blue-500 focus:ring-blue-500" placeholder="Ví dụ: tư vấn đơn hàng dự án">@error('subject')<span class="text-xs font-medium text-rose-600">{{ $message }}</span>@enderror</label>
                    <label class="grid gap-1.5 text-xs font-bold text-slate-700 sm:col-span-2">Nội dung yêu cầu *<textarea name="message" rows="5" required minlength="10" maxlength="5000" class="resize-y rounded-xl border-slate-200 px-3.5 py-3 text-sm font-normal focus:border-blue-500 focus:ring-blue-500" placeholder="Mã linh kiện, số lượng hoặc nội dung bạn cần hỗ trợ...">{{ old('message') }}</textarea>@error('message')<span class="text-xs font-medium text-rose-600">{{ $message }}</span>@enderror</label>
                    <div class="flex flex-wrap items-center justify-between gap-3 sm:col-span-2"><p class="text-xs text-slate-500">Yêu cầu được lưu trong khu vực quản trị để cửa hàng xử lý.</p><button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#173b72] px-5 text-sm font-extrabold text-white shadow-lg shadow-blue-950/15 transition hover:-translate-y-0.5 hover:bg-blue-900">Gửi yêu cầu <span aria-hidden="true">→</span></button></div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
