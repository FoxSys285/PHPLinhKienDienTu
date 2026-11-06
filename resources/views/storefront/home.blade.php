@extends('layouts.store')

@section('title', 'Linh Kiện — Tra cứu và đặt mua linh kiện điện tử')

@push('meta')
<meta property="og:title" content="Linh Kiện — Linh kiện điện tử">
<meta property="og:description" content="Tra cứu linh kiện theo SKU, MPN và thông số kỹ thuật.">
@endpush

@section('content')
<div class="home-page mx-auto max-w-7xl px-4 pb-10 pt-4 sm:pt-6">
    <x-store-hero :hero-products="$heroProducts" />

    <section id="why-us" class="home-benefits mt-4 grid gap-2.5 sm:grid-cols-2 xl:grid-cols-4" aria-label="Cam kết dịch vụ">
        <div class="home-glass-card flex items-center gap-3 rounded-2xl px-4 py-3">
            <span class="home-benefit-icon text-blue-700"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m12 3 7 3v5c0 4-2.8 7.2-7 10-4.2-2.8-7-6-7-10V6z"/><path d="m9 12 2 2 4-4"/></svg></span>
            <span><strong class="block text-xs font-bold text-[#142747]">Cam kết chính hãng</strong><span class="text-[11px] text-slate-500">Nguồn hàng rõ ràng</span></span>
        </div>
        <div class="home-glass-card flex items-center gap-3 rounded-2xl px-4 py-3">
            <span class="home-benefit-icon text-blue-700"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 7h11v10H3zM14 10h4l3 3v4h-7z"/><circle cx="7" cy="19" r="1.5"/><circle cx="18" cy="19" r="1.5"/></svg></span>
            <span><strong class="block text-xs font-bold text-[#142747]">Giao hàng toàn quốc</strong><span class="text-[11px] text-slate-500">Đóng gói nhanh chóng</span></span>
        </div>
        <div class="home-glass-card flex items-center gap-3 rounded-2xl px-4 py-3">
            <span class="home-benefit-icon text-blue-700"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 7v5h-5M4 17v-5h5"/><path d="M5.6 9A7 7 0 0 1 18 6l2 2M4 16l2 2a7 7 0 0 0 12.4-3"/></svg></span>
            <span><strong class="block text-xs font-bold text-[#142747]">Đổi trả thuận tiện</strong><span class="text-[11px] text-slate-500">Theo chính sách cửa hàng</span></span>
        </div>
        <div class="home-glass-card flex items-center gap-3 rounded-2xl px-4 py-3">
            <span class="home-benefit-icon text-blue-700"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 13v-2a8 8 0 0 1 16 0v2M4 12H3v6h4v-6H4zm16 0h1v6h-4v-6h3zM17 18a5 5 0 0 1-5 3h-2"/></svg></span>
            <span><strong class="block text-xs font-bold text-[#142747]">Hỗ trợ kỹ thuật</strong><span class="text-[11px] text-slate-500">Tư vấn tận tâm</span></span>
        </div>
    </section>

    <div class="mt-4 grid items-start gap-4 lg:grid-cols-[220px_minmax(0,1fr)] xl:grid-cols-[238px_minmax(0,1fr)]">
        <aside class="home-glass-card rounded-3xl p-4 sm:p-5" aria-label="Danh mục sản phẩm">
            <div class="flex items-center justify-between gap-3">
                <h2 class="flex items-center gap-2 text-base font-extrabold tracking-tight text-[#142747]"><span class="home-section-mark"></span>Danh mục</h2>
                <a href="{{ route('products.index') }}" class="text-xs font-semibold text-blue-700 hover:text-blue-900">Tất cả <span aria-hidden="true">→</span></a>
            </div>
            <a href="{{ route('products.index') }}" class="mt-4 flex items-center justify-between rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 px-3 py-2.5 text-xs font-bold text-white shadow-[0_8px_20px_rgba(37,99,235,.24)] transition hover:brightness-105">
                Tất cả danh mục <span aria-hidden="true">→</span>
            </a>
            <nav class="mt-3 grid gap-1" aria-label="Danh mục linh kiện">
                @foreach($categories as $category)
                    <a href="{{ route('categories.show', $category) }}" class="home-category-link flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-xs font-medium text-[#263b62] transition">
                        <span class="grid size-7 shrink-0 place-items-center rounded-lg bg-blue-100/70 text-blue-700">
                            <x-category-icon :slug="$category->slug" />
                        </span>
                        <span class="min-w-0 flex-1 truncate">{{ $category->name }}</span>
                        <span class="text-slate-400" aria-hidden="true">›</span>
                    </a>
                @endforeach
            </nav>

            <a href="{{ route('products.index') }}" class="home-side-promo mt-5 flex min-h-36 items-end overflow-hidden rounded-2xl p-4 text-white">
                <span class="relative z-10 max-w-[150px]">
                    <span class="block text-xs font-semibold text-white/85">Ưu đãi dành cho bạn</span>
                    <strong class="mt-1 block text-xl leading-tight">Sắm linh kiện<br>cho dự án mới</strong>
                    <span class="mt-3 inline-flex items-center gap-1 text-xs font-bold">Khám phá <span aria-hidden="true">→</span></span>
                </span>
            </a>
        </aside>

        <div class="min-w-0 space-y-4">
            <section data-guide-target="featured-categories" class="home-glass-card rounded-3xl p-4 sm:p-5 lg:p-6" aria-labelledby="category-heading">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[.18em] text-blue-700">Khám phá</p>
                        <h2 id="category-heading" class="mt-1 flex items-center gap-2 text-lg font-extrabold tracking-tight text-[#142747] sm:text-xl"><span class="home-section-mark"></span>Danh mục nổi bật</h2>
                    </div>
                    <a href="{{ route('products.index') }}" class="text-xs font-bold text-blue-700 transition hover:text-blue-900">Xem tất cả <span aria-hidden="true">→</span></a>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2.5 sm:grid-cols-3 xl:grid-cols-5">
                    @foreach($categories as $category)
                        <a href="{{ route('categories.show', $category) }}" class="home-category-card group flex min-h-28 flex-col items-center justify-center rounded-2xl px-3 py-4 text-center transition duration-200 hover:-translate-y-1">
                            <span class="home-category-art grid size-14 place-items-center rounded-2xl text-blue-700 transition duration-200 group-hover:scale-105">
                                <x-category-icon :slug="$category->slug" size="size-9" />
                            </span>
                            <h3 class="mt-2 text-xs font-bold leading-4 text-[#142747]">{{ $category->name }}</h3>
                        </a>
                    @endforeach
                </div>
            </section>

            <section id="featured-products" class="home-glass-card rounded-3xl p-4 sm:p-5 lg:p-6" aria-labelledby="featured-heading">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[.18em] text-blue-700">Lựa chọn dành cho bạn</p>
                        <h2 id="featured-heading" class="mt-1 flex items-center gap-2 text-lg font-extrabold tracking-tight text-[#142747] sm:text-xl"><span class="home-section-mark"></span>Sản phẩm nổi bật</h2>
                    </div>
                    <a href="{{ route('products.index') }}" class="text-xs font-bold text-blue-700 transition hover:text-blue-900">Xem cửa hàng <span aria-hidden="true">→</span></a>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2.5 sm:grid-cols-3 xl:grid-cols-5">
                    @foreach($featuredProducts as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            </section>
        </div>
    </div>

    <section id="daily-offers" class="mt-4 grid gap-3 md:grid-cols-2" aria-label="Ưu đãi và dự án">
        <a href="{{ route('products.index') }}" class="home-feature-banner home-feature-banner--offer group flex min-h-40 items-center overflow-hidden rounded-3xl px-6 py-6 text-white sm:min-h-44 sm:px-8">
            <span class="max-w-sm">
                <span class="text-[10px] font-bold uppercase tracking-[.18em] text-blue-100">Ưu đãi đặc biệt</span>
                <strong class="mt-1 block text-xl font-extrabold leading-tight sm:text-2xl">Giá tốt cho dự án<br>tiếp theo của bạn</strong>
                <span class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-xs font-bold text-blue-950 transition group-hover:bg-white">Khám phá ưu đãi <span aria-hidden="true">→</span></span>
            </span>
        </a>
        <a href="{{ route('pages.contact') }}" class="home-feature-banner home-feature-banner--project group flex min-h-40 items-center overflow-hidden rounded-3xl px-6 py-6 text-white sm:min-h-44 sm:px-8">
            <span class="max-w-sm">
                <span class="text-[10px] font-bold uppercase tracking-[.18em] text-blue-100">Dự án của bạn</span>
                <strong class="mt-1 block text-xl font-extrabold leading-tight sm:text-2xl">Bắt đầu với những<br>linh kiện phù hợp</strong>
                <span class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-xs font-bold text-blue-950 transition group-hover:bg-white">Liên hệ hỗ trợ dự án <span aria-hidden="true">→</span></span>
            </span>
        </a>
    </section>
</div>
@endsection
