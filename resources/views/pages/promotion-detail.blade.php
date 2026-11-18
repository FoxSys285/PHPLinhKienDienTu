@extends('layouts.store')
@section('title', $promotion->title)

@section('content')
@php
    $categoryNames = $promotionCategories->pluck('name')->join(', ');
    $minimumPurchase = number_format($promotion->minimum_purchase, 0, ',', '.').'₫';
    $promotionPeriod = match (true) {
        $promotion->starts_at !== null && $promotion->expires_at !== null => $promotion->starts_at->format('d/m/Y').' – '.$promotion->expires_at->format('d/m/Y'),
        $promotion->expires_at !== null => 'Đến hết '.$promotion->expires_at->format('d/m/Y'),
        $promotion->starts_at !== null => 'Từ '.$promotion->starts_at->format('d/m/Y'),
        default => 'Đến khi có thông báo mới',
    };
    $heroImage = $promotion->image_path === 'images/promotions/maker.png' && in_array('board-phat-trien', $promotion->category_slugs ?? [], true) ? asset('images/promotions/boards.png') : $promotion->image_url;
    $conditions = [
        'Giảm '.$promotion->discount_label.' khi mua từ '.$minimumPurchase.' sản phẩm áp dụng.',
        $categoryNames !== '' ? 'Áp dụng cho các sản phẩm thuộc danh mục: '.$categoryNames.'.' : 'Áp dụng cho tất cả sản phẩm trong cửa hàng.',
        'Mỗi đơn hàng áp dụng một mã ưu đãi. Không cộng dồn với các chương trình khác.',
    ];
    if ($promotion->maximum_discount !== null) {
        $conditions[] = 'Mức giảm tối đa: '.number_format($promotion->maximum_discount, 0, ',', '.').'₫ mỗi đơn hàng.';
    }
    if ($promotion->usage_limit !== null) {
        $conditions[] = 'Còn '.max(0, $promotion->usage_limit - $promotion->used_count).' lượt sử dụng.';
    }
    $conditions[] = 'Thời gian áp dụng: '.$promotionPeriod.'.';
@endphp
<div class="mx-auto max-w-7xl px-4 pb-10 pt-5 sm:px-6 sm:pb-12 sm:pt-6">
    <nav aria-label="Đường dẫn" class="mb-5 flex flex-wrap items-center gap-2 text-xs font-semibold text-blue-700 sm:gap-3">
        <a href="{{ route('home') }}" aria-label="Trang chủ" class="rounded-md p-1 text-blue-600 hover:bg-blue-100"><svg viewBox="0 0 24 24" class="size-5" fill="currentColor" aria-hidden="true"><path d="m12 3 10 9h-3v9h-5v-6h-4v6H5v-9H2z"/></svg></a>
        <span aria-hidden="true" class="text-blue-400">›</span>
        <a href="{{ route('pages.promotions') }}" class="hover:underline">Khuyến mãi</a>
        <span aria-hidden="true" class="text-blue-400">›</span>
        <span aria-current="page" class="text-[#10285a]">Chi tiết khuyến mãi</span>
    </nav>

    <section aria-labelledby="promotion-title" class="relative isolate overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-[#008cff] via-[#064ee6] to-[#092ca6] text-white shadow-[0_18px_50px_rgba(26,84,211,.18)]">
        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <svg viewBox="0 0 1100 320" preserveAspectRatio="xMidYMid slice" class="absolute inset-0 size-full fill-none stroke-cyan-300/15" stroke-width="1.5"><path d="M350 0h90l30 30h110l30 30h160l20-20h160M490 320v-40l40-40h110l60-60h120l70-70h210M390 180h120l50-50h100l40-40h100M570 0v40l40 40h80M720 320l45-45h130l40-40h165M890 0l-30 30v55l-35 35M1010 320v-50l-40-40v-65"/><g fill="currentColor" class="text-cyan-300/30"><circle cx="390" cy="180" r="4"/><circle cx="570" cy="0" r="4"/><circle cx="490" cy="320" r="4"/><circle cx="800" cy="90" r="4"/></g></svg>
            <div class="absolute -bottom-16 right-[-6%] h-40 w-80 -rotate-12 rounded-full border-[24px] border-cyan-300/25"></div>
        </div>
        <div class="relative grid items-center md:grid-cols-[1.3fr_1fr]">
            <div class="relative z-10 p-6 sm:p-9 lg:p-10">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/15 px-4 py-2 text-xs font-bold shadow-lg shadow-blue-950/10 backdrop-blur-md">
                    <svg viewBox="0 0 24 24" class="size-5 text-amber-300" fill="currentColor" aria-hidden="true"><path d="m3 6 5 4 4-7 4 7 5-4-2 13H5z"/></svg>
                    Chương trình khuyến mãi
                </span>
                <h1 id="promotion-title" class="mt-4 text-3xl font-black leading-[1.12] tracking-tight sm:text-4xl lg:text-[2.65rem]">{{ $promotion->title }}</h1>
                <p class="mt-4 max-w-2xl text-sm font-medium leading-7 text-blue-50 sm:text-base">{{ $promotion->description }}</p>
                <p class="mt-5 flex items-start gap-2.5 text-xs leading-6 text-blue-50 sm:text-sm">
                    <svg viewBox="0 0 24 24" class="mt-0.5 size-5 shrink-0 text-cyan-300" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 6v6l4 2"/></svg>
                    <span>Thời gian áp dụng: <strong class="font-bold">{{ $promotionPeriod }}</strong></span>
                </p>
            </div>
            <div class="relative isolate h-52 overflow-hidden bg-[#064ee6] sm:h-64 md:h-full md:min-h-80">
                <img src="{{ $heroImage }}" alt="{{ $promotion->eyebrow }}" width="768" height="512" fetchpriority="high" class="absolute inset-0 size-full object-cover mix-blend-luminosity opacity-90">
                <div class="absolute inset-0 bg-gradient-to-t from-blue-800/20 via-transparent to-blue-600/10 md:bg-gradient-to-r md:from-[#0748d6] md:via-transparent md:to-transparent"></div>
                <div class="absolute bottom-5 left-8 right-8 h-12 rounded-[50%] border-b-[3px] border-cyan-200 shadow-[0_8px_18px_rgba(34,211,238,.65)] md:bottom-8"></div>
                <div class="absolute right-5 top-4 rotate-6 rounded-3xl border border-cyan-200/70 bg-blue-600/65 px-5 py-4 text-center shadow-[0_14px_35px_rgba(0,30,125,.3)] backdrop-blur-md sm:right-7 sm:top-6">
                    <span class="block text-sm font-bold sm:text-base">Giảm ngay</span>
                    <strong @class(['mt-1 block font-black leading-none tracking-tight', 'text-6xl sm:text-7xl' => $promotion->type === 'percentage', 'text-3xl sm:text-4xl' => $promotion->type !== 'percentage'])>{{ $promotion->discount_label }}</strong>
                </div>
            </div>
        </div>
    </section>

    <div class="mt-6 grid items-stretch gap-5 lg:grid-cols-[1.45fr_1fr]">
        <section aria-labelledby="promotion-information-title" class="min-w-0 rounded-3xl border border-white/90 bg-white/85 p-5 shadow-[0_10px_30px_rgba(54,91,180,.07)] sm:p-6">
            <h2 id="promotion-information-title" class="flex items-center gap-3 text-lg font-extrabold tracking-tight text-[#10285a] sm:text-xl">
                <svg viewBox="0 0 24 24" class="size-7 shrink-0 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 8h18v4H3zM5 12v9h14v-9M12 8v13"/><path d="M12 8H8a3 3 0 1 1 3-3l1 3Zm0 0h4a3 3 0 1 0-3-3l-1 3Z"/></svg>
                Thông tin chương trình
            </h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-[1.25fr_1fr_1fr] sm:items-center">
                <div class="relative min-w-0 overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 to-[#008fff] p-4 text-center text-white shadow-lg shadow-blue-600/15">
                    <span class="absolute -left-1.5 top-1/2 size-3 rounded-full bg-[#eef4ff]" aria-hidden="true"></span><span class="absolute -right-1.5 top-1/2 size-3 rounded-full bg-[#eef4ff]" aria-hidden="true"></span>
                    <p class="text-xs font-semibold text-blue-50">Mã khuyến mãi</p>
                    <strong data-promotion-code class="mt-2 block break-all font-mono text-2xl font-black tracking-tight sm:text-3xl">{{ $promotion->code }}</strong>
                    <button type="button" data-copy-promotion-code="{{ $promotion->code }}" class="mt-3 inline-flex min-h-9 max-w-full items-center justify-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-extrabold text-blue-700 transition hover:bg-cyan-50 disabled:opacity-80">
                        <svg viewBox="0 0 24 24" class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="8" y="8" width="12" height="13" rx="2"/><path d="M16 8V3H3v13h5"/></svg>
                        <span data-copy-label>Sao chép mã</span>
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-4 sm:col-span-2">
                    <div class="min-w-0 border-r border-blue-200 px-1 py-2 text-center sm:border-l sm:px-3 sm:py-5">
                        <p class="text-sm text-[#334987]">Giảm giá</p>
                        <strong @class(['mt-2 block break-words font-black leading-none tracking-tight text-blue-600', 'text-4xl sm:text-5xl' => $promotion->type === 'percentage', 'text-2xl sm:text-3xl' => $promotion->type !== 'percentage'])>{{ $promotion->discount_label }}</strong>
                    </div>
                    <div class="min-w-0 px-1 py-2 text-center sm:px-2 sm:py-5">
                        <p class="text-sm text-[#334987]">Mua tối thiểu</p>
                        <strong class="mt-2 block break-words text-xl font-black leading-tight tracking-tight text-[#10285a] sm:text-2xl">{{ $minimumPurchase }}</strong>
                    </div>
                </div>
            </div>
            <p data-copy-status role="status" aria-live="polite" class="mt-2 min-h-4 text-center text-[11px] text-blue-700"></p>
            <div class="mt-3 flex items-start gap-3 rounded-xl border-l-2 border-blue-500 bg-blue-50 px-4 py-3 text-xs leading-6 text-[#233d7b] sm:text-sm">
                <svg viewBox="0 0 24 24" class="mt-0.5 size-5 shrink-0 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M9 18h6m-6 3h6M8 14a7 7 0 1 1 8 0l-1 2H9z"/></svg>
                <span>{{ $promotion->is_automatic ? 'Ưu đãi được tự động xét khi thanh toán. Hệ thống chọn chương trình giúp tổng thanh toán thấp nhất.' : 'Sao chép và nhập mã tại bước thanh toán để kiểm tra điều kiện áp dụng.' }}</span>
            </div>
        </section>
        <section aria-labelledby="promotion-conditions-title" class="min-w-0 rounded-3xl border border-blue-200 bg-white/80 p-5 shadow-[0_10px_30px_rgba(54,91,180,.05)] sm:p-6">
            <h2 id="promotion-conditions-title" class="flex items-center gap-3 text-lg font-extrabold tracking-tight text-[#10285a] sm:text-xl">
                <svg viewBox="0 0 24 24" class="size-7 shrink-0 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 3h10l4 4v14H5zM14 3v5h5M8 12h8m-8 4h6"/></svg>
                Điều kiện áp dụng
            </h2>
            <ul class="mt-5 grid gap-3">
                @foreach ($conditions as $condition)
                    <li class="flex items-start gap-3 text-xs leading-6 text-[#233d7b] sm:text-sm"><svg viewBox="0 0 24 24" class="mt-0.5 size-5 shrink-0 text-blue-600" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m7.5 12 3 3 6-6" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>{{ $condition }}</span></li>
                @endforeach
            </ul>
            @if ($promotion->terms)
                <p class="mt-4 whitespace-pre-line border-t border-blue-100 pt-4 text-xs leading-6 text-slate-600">{{ $promotion->terms }}</p>
            @endif
        </section>
    </div>

    <section id="promotion-products" class="mt-8 sm:mt-10" aria-labelledby="promotion-products-title">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 id="promotion-products-title" class="flex items-center gap-3 text-lg font-extrabold tracking-tight text-[#10285a] sm:text-xl">
                <svg viewBox="0 0 24 24" class="size-7 shrink-0 text-blue-600" fill="currentColor" aria-hidden="true"><path d="m12 1.5 3.2 6.6 7.3 1.1-5.3 5.1 1.3 7.2L12 18.1l-6.5 3.4 1.3-7.2-5.3-5.1 7.3-1.1z"/></svg>
                Sản phẩm nổi bật trong chương trình
            </h2>
            <a href="{{ route('products.index', ['voucher' => $promotion->code]) }}" class="inline-flex items-center gap-2 text-xs font-bold text-blue-700 hover:underline sm:text-sm">Xem tất cả sản phẩm <span aria-hidden="true">→</span></a>
        </div>
        @if ($products->isNotEmpty())
            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 xl:grid-cols-5">
                @foreach ($products->take(5) as $product)
                    @include('partials.promotion-product-card', ['product' => $product, 'promotion' => $promotion])
                @endforeach
            </div>
            <p class="mt-4 text-xs leading-6 text-slate-500">Giá hiển thị là giá sản phẩm trước ưu đãi chương trình. Mức giảm được tính khi giỏ hàng đủ điều kiện và hiển thị tại bước thanh toán.</p>
        @else
            <div class="mt-5 rounded-3xl border border-dashed border-blue-200 bg-white/70 p-8 text-center"><p class="font-bold text-[#10285a]">Sản phẩm trong chương trình đang được cập nhật</p><a href="{{ route('products.index') }}" class="mt-3 inline-flex text-sm font-bold text-blue-600 hover:underline">Khám phá cửa hàng →</a></div>
        @endif
    </section>

    <aside class="relative mt-7 isolate overflow-hidden rounded-[2rem] border border-blue-100 bg-gradient-to-r from-[#e0e9ff] via-blue-100/80 to-[#a9ceff] px-5 py-6 sm:mt-8 sm:px-7">
        <svg viewBox="0 0 380 140" class="pointer-events-none absolute right-10 top-0 -z-10 h-full w-1/2 fill-none stroke-blue-400/20" stroke-width="3" aria-hidden="true"><path d="M0 20h90l30 30h70l50 50h140M20 70h70l30-30h100l30-30h130M40 140l45-45h80l60-60h155M120 140l30-30h90l50-50h90"/></svg>
        <div class="flex flex-col items-start gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-4">
                <span class="grid size-14 shrink-0 place-items-center rounded-2xl border border-white/70 bg-white/40 text-blue-600"><svg viewBox="0 0 32 32" class="size-9" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 22 10 12C15 5 21 3 29 3c0 8-2 14-9 19l-8-1ZM10 12 5 13l-3 8 9-2m9 3-1 5-8 3 2-9M8 24l-4 4"/><circle cx="22" cy="10" r="3" fill="#fff"/></svg></span>
                <div class="min-w-0"><h2 class="text-sm font-extrabold leading-6 text-[#10285a] sm:text-base">Đừng bỏ lỡ linh kiện chất lượng với giá tốt!</h2><p class="mt-1 text-xs leading-6 text-[#334987] sm:text-sm">Khám phá sản phẩm và tận dụng ưu đãi <strong class="break-all font-extrabold text-blue-700">{{ $promotion->code }}</strong> cho dự án của bạn.</p></div>
            </div>
            <a href="#promotion-products" class="inline-flex w-full shrink-0 items-center justify-center gap-3 rounded-full bg-gradient-to-r from-[#008aff] to-blue-700 px-7 py-3 text-sm font-extrabold text-white shadow-lg shadow-blue-600/20 transition hover:shadow-blue-600/35 sm:w-auto">Mua sắm ngay <span aria-hidden="true">→</span></a>
        </div>
    </aside>
</div>
@endsection
