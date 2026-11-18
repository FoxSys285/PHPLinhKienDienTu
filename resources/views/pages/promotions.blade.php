@extends('layouts.store')
@section('title', 'Ưu đãi linh kiện điện tử')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12">
    <section id="danh-sach-uu-dai" aria-labelledby="promotion-list-title">
        <div class="flex flex-wrap items-end justify-between gap-5">
            <div class="max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full bg-blue-100/80 px-4 py-2 text-xs font-extrabold uppercase tracking-wider text-blue-700">ϟ Chương trình khuyến mãi</p>
                <h1 id="promotion-list-title" class="mt-4 text-3xl font-black tracking-tight text-[#102448] sm:text-4xl lg:text-5xl">Ưu đãi đang <span class="text-blue-600">áp dụng</span></h1>
                <p class="mt-3 text-sm leading-6 text-slate-600 sm:text-base">Thêm linh kiện, thêm ý tưởng. Khám phá ưu đãi phù hợp và để cửa hàng tự áp dụng khi bạn thanh toán.</p>
                <div class="mt-5 h-1 w-24 rounded-full bg-blue-200"><div class="h-1 w-10 rounded-full bg-blue-600"></div></div>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-blue-700 hover:text-blue-900">Xem toàn bộ sản phẩm <span class="grid size-8 place-items-center rounded-full bg-blue-100" aria-hidden="true">→</span></a>
        </div>
        <div class="mt-7 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @forelse ($promotions as $promotion)
                @include('partials.promotion-card', ['promotion' => $promotion])
            @empty
                <div class="rounded-3xl border border-dashed border-blue-200 bg-white/80 p-10 text-center sm:col-span-2 xl:col-span-4"><h2 class="font-bold text-slate-800">Các ưu đãi mới đang được chuẩn bị</h2><p class="mt-2 text-sm text-slate-500">Bạn có thể tiếp tục khám phá các sản phẩm tại cửa hàng.</p></div>
            @endforelse
        </div>
        @if ($promotions->isNotEmpty())
            <p class="mt-6 text-center text-xs leading-5 text-slate-500">{{ $promotions->count() }} chương trình đang áp dụng · Hệ thống chọn một ưu đãi cho tổng thanh toán thấp nhất · Không cộng dồn</p>
        @endif
    </section>
</div>
@endsection
