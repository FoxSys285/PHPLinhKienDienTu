@if(request()->routeIs('home'))
<section data-hero-carousel data-hero-slides="{{ asset('images/home-hero-cpu.webp') }}|{{ asset('images/home-promotion-tools.webp') }}|{{ asset('images/electronics-banner.png') }}" class="store-hero relative isolate flex min-h-[250px] overflow-hidden rounded-[28px] bg-[#071b47] text-white shadow-[0_18px_46px_rgba(14,49,128,.28)] sm:min-h-[270px] lg:min-h-[300px]" aria-labelledby="store-hero-title" aria-roledescription="carousel" aria-label="Banner giới thiệu">
    <div data-parallax data-hero-image class="absolute inset-0 z-0 bg-cover transition-opacity duration-300 motion-reduce:transition-none" style="--hero-slide-image: url('{{ asset('images/home-hero-cpu.webp') }}'); background-image: linear-gradient(90deg, rgba(5, 21, 59, .97) 0%, rgba(5, 21, 59, .91) 38%, rgba(5, 21, 59, .3) 68%, rgba(5, 21, 59, .04) 100%), var(--hero-slide-image); background-position: center, center 48%; background-size: cover;"></div>
    <div class="pointer-events-none absolute inset-0 z-0 bg-[radial-gradient(ellipse_at_80%_45%,rgba(20,112,214,.22),transparent_52%)]"></div>
    <div class="relative z-10 flex w-full items-center px-5 py-8 sm:px-9 lg:px-10">
        <div class="max-w-[580px]">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3 py-1.5 text-[10px] font-semibold text-white/95 shadow-sm backdrop-blur-md sm:text-xs"><span aria-hidden="true">🏆</span> Linh kiện điện tử — kết nối đam mê công nghệ</span>
            <h1 id="store-hero-title" class="mt-3 text-3xl font-extrabold leading-[1.08] tracking-tight sm:text-4xl lg:text-[42px]">Linh kiện điện tử<span class="store-hero-highlight mt-1 block">Chất lượng tạo nên giá trị</span></h1>
            <p class="mt-3 text-xs font-medium text-blue-50 sm:text-sm">Đa dạng sản phẩm <span class="px-1.5 text-cyan-300">•</span> Giá tốt <span class="px-1.5 text-cyan-300">•</span> Giao hàng nhanh</p>
            <div class="mt-5 flex flex-wrap items-center gap-4">
                <a href="{{ route('products.index') }}" class="store-hero-cta inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-xs font-bold text-white transition hover:brightness-110 sm:text-sm">Khám phá ngay <span aria-hidden="true">→</span></a>
                <a href="#featured-products" class="inline-flex items-center gap-2 text-xs font-semibold text-white/95 transition hover:text-cyan-200"><span class="grid size-8 place-items-center rounded-full border border-white/50 bg-white/15 text-sm backdrop-blur-md" aria-hidden="true">▶</span>Xem sản phẩm</a>
            </div>
        </div>
    </div>

    <aside class="store-hero-service absolute right-4 top-1/2 z-10 hidden -translate-y-1/2 rounded-2xl border border-white/35 bg-white/15 p-3.5 text-white shadow-[0_12px_36px_rgba(0,20,70,.2)] backdrop-blur-xl xl:grid xl:w-40 xl:gap-3" aria-label="Dịch vụ nổi bật">
        <div class="flex items-center gap-2.5"><span class="grid size-8 shrink-0 place-items-center rounded-full bg-white/15 text-blue-50"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m12 3 7 3v5c0 4-2.8 7.2-7 10-4.2-2.8-7-6-7-10V6z"/><path d="m9 12 2 2 4-4"/></svg></span><span class="text-[10px] font-semibold leading-tight">Bảo hành<br>chính hãng</span></div>
        <div class="flex items-center gap-2.5"><span class="grid size-8 shrink-0 place-items-center rounded-full bg-white/15 text-blue-50"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 7h11v10H3zM14 10h4l3 3v4h-7z"/><circle cx="7" cy="19" r="1.5"/><circle cx="18" cy="19" r="1.5"/></svg></span><span class="text-[10px] font-semibold leading-tight">Giao hàng<br>nhanh chóng</span></div>
        <div class="flex items-center gap-2.5"><span class="grid size-8 shrink-0 place-items-center rounded-full bg-white/15 text-blue-50"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 13v-2a8 8 0 0 1 16 0v2M4 12H3v6h4v-6H4zm16 0h1v6h-4v-6h3zM17 18a5 5 0 0 1-5 3h-2"/></svg></span><span class="text-[10px] font-semibold leading-tight">Hỗ trợ<br>tận tâm</span></div>
    </aside>

    <div class="absolute bottom-3 left-1/2 z-10 flex -translate-x-1/2 items-center gap-2" role="group" aria-label="Chọn ảnh banner">
        <button type="button" data-hero-indicator="0" class="h-2 w-5 rounded-full bg-white transition-all" aria-label="Hiển thị ảnh 1" aria-pressed="true"></button>
        <button type="button" data-hero-indicator="1" class="size-2 rounded-full bg-white/45 transition-all" aria-label="Hiển thị ảnh 2" aria-pressed="false"></button>
        <button type="button" data-hero-indicator="2" class="size-2 rounded-full bg-white/45 transition-all" aria-label="Hiển thị ảnh 3" aria-pressed="false"></button>
    </div>
</section>
@elseif(request()->routeIs('products.index', 'categories.show'))
<section data-hero-carousel data-hero-slides="{{ asset('images/home-hero-cpu.webp') }}|{{ asset('images/home-promotion-tools.webp') }}|{{ asset('images/electronics-banner.png') }}" class="store-hero relative isolate flex min-h-[220px] overflow-hidden rounded-[26px] bg-[#071b47] text-white shadow-[0_16px_38px_rgba(14,49,128,.2)] sm:min-h-[240px]" aria-labelledby="store-hero-title" aria-roledescription="carousel" aria-label="Banner giới thiệu">
    <div data-parallax data-hero-image class="absolute inset-0 z-0 bg-cover transition-opacity duration-300 motion-reduce:transition-none" style="--hero-slide-image: url('{{ asset('images/home-hero-cpu.webp') }}'); background-image: linear-gradient(90deg, rgba(5, 21, 59, .96) 0%, rgba(5, 21, 59, .88) 42%, rgba(5, 21, 59, .2) 72%, rgba(5, 21, 59, .06) 100%), var(--hero-slide-image); background-position: center, center 48%; background-size: cover;"></div>
    <div class="pointer-events-none absolute inset-0 z-0 bg-[radial-gradient(ellipse_at_82%_45%,rgba(20,112,214,.2),transparent_52%)]"></div>
    <div class="relative z-10 flex w-full items-center px-5 py-7 sm:px-9 lg:px-10">
        <div class="max-w-[560px]">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3 py-1.5 text-[10px] font-semibold text-white/95 backdrop-blur-md sm:text-xs"><span aria-hidden="true">✦</span> Linh kiện cho ý tưởng của bạn</span>
            <h1 id="store-hero-title" class="mt-3 text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl">Khám phá linh kiện<span class="store-hero-highlight mt-1 block">Sẵn sàng cho dự án mới</span></h1>
            <p class="mt-2.5 text-xs font-medium text-blue-50 sm:text-sm">Tìm kiếm theo tên, SKU, MPN hoặc thông số kỹ thuật.</p>
        </div>
    </div>
    <div class="absolute bottom-3 left-1/2 z-10 flex -translate-x-1/2 items-center gap-2" role="group" aria-label="Chọn ảnh banner">
        <button type="button" data-hero-indicator="0" class="h-2 w-5 rounded-full bg-white transition-all" aria-label="Hiển thị ảnh 1" aria-pressed="true"></button>
        <button type="button" data-hero-indicator="1" class="size-2 rounded-full bg-white/45 transition-all" aria-label="Hiển thị ảnh 2" aria-pressed="false"></button>
        <button type="button" data-hero-indicator="2" class="size-2 rounded-full bg-white/45 transition-all" aria-label="Hiển thị ảnh 3" aria-pressed="false"></button>
    </div>
</section>
@else
<section class="relative isolate flex min-h-[220px] overflow-hidden rounded-2xl bg-[#0c2140] text-white shadow-sm sm:min-h-[230px]" aria-labelledby="store-hero-title" style="background-image: linear-gradient(90deg, rgba(9, 25, 49, .97) 0%, rgba(9, 25, 49, .91) 38%, rgba(9, 25, 49, .22) 68%, rgba(9, 25, 49, .04) 100%), url('{{ asset('images/home-hero-cpu.webp') }}'); background-position: center, center 48%; background-size: cover;">
    <div class="pointer-events-none absolute inset-0 z-0 bg-[radial-gradient(ellipse_at_90%_45%,rgba(20,112,214,.18),transparent_50%)]"></div>
    <div class="relative z-10 flex w-full items-center px-6 py-6 sm:px-10 lg:px-14">
        <div class="max-w-xl">
            <h1 id="store-hero-title" class="text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl lg:text-[42px]">Linh kiện điện tử<span class="mt-1 block text-amber-400">Chất lượng tạo nên giá trị</span></h1>
            <p class="mt-2.5 text-sm font-medium text-slate-100 sm:text-base">Đa dạng sản phẩm <span class="px-1 text-amber-400">·</span> Giá tốt <span class="px-1 text-amber-400">·</span> Giao hàng nhanh</p>
            <a href="{{ route('products.index') }}" class="mt-4 inline-flex items-center gap-2 rounded-full bg-amber-400 px-5 py-2.5 text-sm font-bold text-[#10283e] shadow-sm transition hover:bg-amber-300">Khám phá ngay <span aria-hidden="true">→</span></a>
        </div>
    </div>
    <div class="absolute bottom-3 left-1/2 z-10 flex -translate-x-1/2 items-center gap-2" aria-hidden="true">
        <span class="size-2.5 rounded-full bg-amber-400"></span><span class="size-2 rounded-full bg-white/45"></span><span class="size-2 rounded-full bg-white/45"></span>
    </div>
</section>
@endif
