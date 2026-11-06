<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Linh Kiện — Linh kiện điện tử')</title>
    <meta name="description" content="Tra cứu linh kiện theo SKU, MPN và thông số kỹ thuật. Xem giá theo số lượng, tình trạng tồn và datasheet.">
    <meta name="theme-color" content="#10283e">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    @if(request()->routeIs('home', 'products.index', 'categories.show', 'pages.promotions', 'pages.support'))
        <link rel="preload" as="image" href="{{ asset('images/home-hero-cpu.webp') }}" fetchpriority="high">
    @endif
    @stack('meta')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body id="page-top" @class([
    'min-h-screen bg-[#f7f9fc] text-slate-900 antialiased',
    'storefront-home' => request()->routeIs('home'),
    'storefront-catalog' => request()->routeIs('products.index', 'categories.show'),
    'storefront-product' => request()->routeIs('products.show'),
    'storefront-cart' => request()->routeIs('cart.index'),
    'storefront-auth' => request()->routeIs('login', 'register', 'password.request', 'password.reset'),
    'storefront-content' => request()->routeIs('pages.promotions', 'pages.support', 'pages.returns', 'pages.shipping', 'pages.privacy', 'pages.contact', 'account.*', 'checkout.*'),
])>
    <header data-site-header class="site-header sticky top-0 z-40 border-b border-slate-200/80 bg-white shadow-[0_2px_7px_rgba(15,35,65,.04)]">
        <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-2.5 sm:gap-5 lg:gap-7">
            <a href="{{ route('home') }}" class="flex w-fit shrink-0 items-center gap-2.5" aria-label="Linh Kiện — Trang chủ">
                <x-brand-mark size="size-11" class="site-brand-mark" />
                <span class="leading-tight"><span class="block text-lg font-extrabold tracking-tight text-[#10283e]">Linh Kiện</span><span class="hidden text-[10px] font-medium text-slate-500 sm:block">Kết nối đam mê công nghệ</span></span>
            </a>

            <form action="{{ route('products.index') }}" method="GET" class="relative hidden min-w-0 flex-1 md:block" data-search-form data-search-endpoint="{{ route('search.suggest') }}" data-guide-target="search">
                <label class="sr-only" for="site-search">Tìm kiếm linh kiện</label>
                <div class="site-search-control flex rounded-full border border-[#d7e1ef] bg-white p-0.5 shadow-[0_1px_5px_rgba(15,35,65,.05)] focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-100">
                    <input id="site-search" data-search-input type="search" name="q" value="{{ request('q') }}" placeholder="Tìm kiếm linh kiện, sản phẩm..." class="w-full rounded-full border-0 bg-transparent px-4 py-2 text-sm outline-none focus:ring-0" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="desktop-search-results" autocomplete="off">
                    <button type="submit" aria-label="Tìm kiếm" class="m-0.5 grid size-9 shrink-0 place-items-center rounded-full text-[#10283e] hover:bg-slate-100"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg></button>
                </div>
                <div id="desktop-search-results" data-search-results role="listbox" aria-label="Gợi ý tìm kiếm" aria-busy="false" class="absolute left-0 right-0 top-[calc(100%+7px)] hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl"></div>
            </form>

            <div class="ml-auto flex shrink-0 items-center gap-2 sm:gap-3">
                <a href="{{ route('cart.index') }}" class="site-cart-link relative flex h-10 items-center gap-2 rounded-xl bg-[#10213d] px-3 text-white shadow-sm hover:bg-[#183c60] sm:px-3.5" aria-label="Giỏ hàng, {{ $navCartCount ?? 0 }} sản phẩm">
                    <svg viewBox="0 0 24 24" class="size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2.2 11.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20 9H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                    <span class="hidden text-xs font-semibold sm:inline">Giỏ hàng ({{ $navCartCount ?? 0 }})</span>
                    @if(($navCartCount ?? 0) > 0)<span class="absolute -right-1 -top-1 grid min-w-5 place-items-center rounded-full bg-amber-400 px-1 text-[10px] font-bold text-slate-950 sm:hidden">{{ $navCartCount }}</span>@endif
                </a>
                @auth
                    <a href="{{ route('account.index') }}" class="hidden h-10 max-w-36 items-center gap-2 rounded-lg border border-slate-300 px-3 text-sm font-semibold text-slate-700 hover:border-blue-400 hover:text-[#10283e] sm:flex"><svg viewBox="0 0 24 24" class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/></svg><span class="truncate">{{ auth()->user()->name }}</span></a>
                @else
                    <a href="{{ route('login') }}" class="hidden h-10 items-center gap-2 rounded-lg border border-slate-300 px-3 text-sm font-semibold text-slate-700 hover:border-blue-400 hover:text-[#10283e] sm:flex"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/></svg>Đăng nhập</a>
                @endauth
                <button type="button" data-menu-button aria-expanded="false" aria-controls="mobile-site-menu" class="grid size-10 place-items-center rounded-lg border border-slate-300 text-[#10283e] md:hidden" aria-label="Mở menu"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
            </div>
        </div>

        <div class="hidden border-t border-slate-100 md:block">
            <nav class="site-navigation mx-auto flex max-w-7xl items-center gap-4 px-4 py-1.5 text-xs font-semibold text-[#142747] lg:gap-6">
                <details class="group relative shrink-0" data-guide-target="store-categories">
                    <summary class="site-navigation-category inline-flex cursor-pointer list-none items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-[#142747] shadow-sm transition hover:bg-amber-100">
                        <svg viewBox="0 0 24 24" class="size-4 text-blue-700" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3.5" y="3.5" width="6" height="6" rx="1"/><rect x="14.5" y="3.5" width="6" height="6" rx="1"/><rect x="3.5" y="14.5" width="6" height="6" rx="1"/><rect x="14.5" y="14.5" width="6" height="6" rx="1"/></svg>
                        Danh mục
                        <svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </summary>
                    <div class="absolute left-0 top-[calc(100%+10px)] z-50 grid w-64 gap-1 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                        <a href="{{ route('products.index') }}" class="rounded-xl bg-amber-50 px-3 py-2.5 font-bold text-[#142747] hover:bg-amber-100">Xem tất cả sản phẩm <span aria-hidden="true">→</span></a>
                        @foreach(($navCategories ?? collect()) as $navCategory)
                            <a href="{{ route('categories.show', $navCategory) }}" class="rounded-xl px-3 py-2 text-slate-700 hover:bg-blue-50 hover:text-[#183652]">{{ $navCategory->name }}</a>
                        @endforeach
                    </div>
                </details>
                <a href="{{ route('pages.promotions') }}" class="site-navigation-item site-navigation-item--promo inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap"><svg viewBox="0 0 24 24" class="size-[18px]" fill="currentColor" aria-hidden="true"><path d="M12.2 2.1c.6 3.1-.7 4.6-2.2 6.1-1.4 1.4-2.7 2.8-2.7 5a4.7 4.7 0 0 0 9.4 0c0-1.6-.7-3-2-4.2-.1 2-1.2 2.7-1.7 3.2.1-4.1-1.2-7.7-3.4-10.1-.5 2.2-1.4 3.4-2.6 4.8C5 9 3.8 11 3.8 13.5a8.2 8.2 0 0 0 16.4 0c0-4.5-3.1-8.6-8-11.4Z"/></svg>Khuyến mãi</a>
                @auth
                    @if (auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-900 transition hover:bg-blue-100">
                            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3 4 6v5c0 5 3.4 8.4 8 10 4.6-1.6 8-5 8-10V6z"/><path d="M8 12h8m-4-4v8"/></svg>
                            Quản trị
                        </a>
                    @endif
                @endauth
            </nav>
        </div>

        <div id="mobile-site-menu" data-mobile-menu class="hidden border-t border-slate-200 bg-white px-4 py-3 md:hidden">
            <form action="{{ route('products.index') }}" method="GET" class="relative mb-3 flex rounded-xl border border-slate-300/80 bg-white/85 p-1.5 shadow-sm" data-search-form data-search-endpoint="{{ route('search.suggest') }}" data-guide-target="search">
                <label class="sr-only" for="mobile-site-search">Tìm kiếm linh kiện</label>
                <input id="mobile-site-search" data-search-input type="search" name="q" value="{{ request('q') }}" placeholder="Tìm mã hoặc tên linh kiện" class="min-w-0 flex-1 rounded-lg border-0 px-3 py-2 text-sm focus:ring-0" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="mobile-search-results" autocomplete="off">
                <button class="rounded-lg bg-[#10283e] px-3 text-sm font-semibold text-white">Tìm</button>
                <div id="mobile-search-results" data-search-results role="listbox" aria-label="Gợi ý tìm kiếm" aria-busy="false" class="absolute left-0 right-0 top-[calc(100%+6px)] z-50 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl"></div>
            </form>
            <div class="grid gap-1">
                <a class="rounded-xl bg-amber-50 px-3 py-2 text-sm font-semibold text-slate-900" href="{{ route('products.index') }}">Danh mục</a>
                <a class="rounded-xl px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('pages.promotions') }}">Khuyến mãi</a>
                @foreach(($navCategories ?? collect()) as $navCategory)<a class="rounded-xl px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('categories.show', $navCategory) }}">{{ $navCategory->name }}</a>@endforeach
                @guest<a class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('login') }}">Đăng nhập</a>@endguest
                @auth
                    <a class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50" href="{{ route('account.index') }}">Tài khoản</a>
                    @if (auth()->user()->role === 'admin')
                        <a class="rounded-md bg-blue-50 px-3 py-2 text-sm font-bold text-blue-900 hover:bg-blue-100" href="{{ route('admin.dashboard') }}">Quản trị</a>
                    @endif
                @endauth
            </div>
        </div>
    </header>

    @if(session('success') || session('error') || $errors->any())
        <div class="mx-auto mt-4 max-w-7xl px-4" data-flash>
            @if(session('success'))<div class="mb-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-950">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="mb-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="mb-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900"><p class="font-bold">Vui lòng kiểm tra lại thông tin:</p><ul class="mt-1 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        </div>
    @endif

    <main class="min-h-[60vh]" data-reveal-scope="store">@yield('content')</main>
    @if(request()->routeIs('home', 'products.index', 'categories.show'))
        <div data-guide-root data-guide-variant="store"></div>
    @endif
    @if(!request()->routeIs('pages.support'))
        <a href="{{ route('pages.support') }}" class="store-support-fab" aria-label="Mở trang hỗ trợ">
            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 13v-2a8 8 0 0 1 16 0v2M4 12H3v6h4v-6H4zm16 0h1v6h-4v-6h3zM17 18a5 5 0 0 1-5 3h-2"/></svg>
            <span>Hỗ trợ</span>
        </a>
    @endif

    <footer class="site-footer text-white">
        <div class="site-footer-shell mx-auto max-w-[1500px] px-4 pb-0 pt-8 sm:px-6 sm:pt-10">
            <div class="site-footer-main grid gap-8 rounded-[30px] border border-white/15 bg-white/[.045] p-5 shadow-[0_24px_80px_rgba(0,10,42,.34)] backdrop-blur-2xl sm:p-7 lg:grid-cols-[1.35fr_1fr_1.05fr_1.25fr_230px] lg:gap-6 lg:p-8 xl:gap-8">
                <section class="site-footer-brand min-w-0">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3" aria-label="Linh Kiện — Trang chủ">
                        <x-brand-mark size="size-12" />
                        <span class="leading-tight"><span class="block text-2xl font-extrabold tracking-tight">Linh Kiện</span><span class="mt-1 block text-xs text-blue-100/80">Kết nối đam mê công nghệ</span></span>
                    </a>
                    <p class="mt-4 max-w-sm text-sm leading-6 text-blue-100/80">Tra cứu linh kiện theo mã, thông số và danh mục. Đặt hàng trực tuyến hoặc liên hệ để được hỗ trợ.</p>

                    <a href="{{ route('pages.promotions') }}" class="site-footer-newsletter mt-5 flex items-center gap-3 rounded-2xl border border-white/15 bg-gradient-to-r from-blue-500/30 to-indigo-500/20 p-3.5 transition hover:border-blue-300/50 hover:bg-blue-500/25">
                        <span class="grid size-11 shrink-0 place-items-center rounded-full bg-gradient-to-br from-cyan-400 to-blue-600 text-white shadow-[0_8px_20px_rgba(0,124,255,.35)]"><svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m3 11 18-8-7 18-3-7-8-3Z"/><path d="m11 14 4-5"/></svg></span>
                        <span class="min-w-0 flex-1"><strong class="block text-sm font-bold">Ưu đãi mới nhất</strong><span class="mt-0.5 block text-xs text-blue-100/75">Khám phá khuyến mãi từ Linh Kiện</span></span>
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-blue-600 text-xl text-white" aria-hidden="true">→</span>
                    </a>

                    <div class="site-footer-social-row mt-6 flex flex-wrap items-center gap-2.5">
                        <span class="site-social-icon site-social-icon--facebook" role="img" aria-label="Facebook">f</span>
                        <span class="site-social-icon site-social-icon--youtube" role="img" aria-label="YouTube"><svg viewBox="0 0 24 24" class="size-5" fill="currentColor" aria-hidden="true"><path d="M23 7.2a3 3 0 0 0-2.1-2.1C19 4.6 12 4.6 12 4.6s-7 0-8.9.5A3 3 0 0 0 1 7.2 31 31 0 0 0 .5 12a31 31 0 0 0 .5 4.8 3 3 0 0 0 2.1 2.1c1.9.5 8.9.5 8.9.5s7 0 8.9-.5a3 3 0 0 0 2.1-2.1 31 31 0 0 0 .5-4.8 31 31 0 0 0-.5-4.8ZM9.6 15.5v-7l6 3.5-6 3.5Z"/></svg></span>
                        <span class="site-social-icon site-social-icon--discord" role="img" aria-label="Discord"><svg viewBox="0 0 24 24" class="size-5" fill="currentColor" aria-hidden="true"><path d="M19.7 5.1A18 18 0 0 0 15.3 3.7l-.5 1a16.5 16.5 0 0 0-5.6 0l-.5-1a18 18 0 0 0-4.4 1.4C1.5 9.2.7 13.2 1.1 17.2a18 18 0 0 0 5.4 2.7l1.2-2a11.4 11.4 0 0 1-1.9-.9l.5-.4a12.8 12.8 0 0 0 11.4 0l.5.4a11.4 11.4 0 0 1-1.9.9l1.2 2a18 18 0 0 0 5.4-2.7c.5-4.6-.8-8.6-3.2-12.1ZM8.7 14.7c-1 0-1.8-.9-1.8-2s.8-2 1.8-2 1.8.9 1.8 2-.8 2-1.8 2Zm6.6 0c-1 0-1.8-.9-1.8-2s.8-2 1.8-2 1.8.9 1.8 2-.8 2-1.8 2Z"/></svg></span>
                        <span class="site-social-icon site-social-icon--github" role="img" aria-label="GitHub"><svg viewBox="0 0 24 24" class="size-5" fill="currentColor" aria-hidden="true"><path d="M12 .9a11.1 11.1 0 0 0-3.5 21.6c.6.1.8-.2.8-.6v-2.1c-3.1.7-3.8-1.3-3.8-1.3-.5-1.2-1.2-1.5-1.2-1.5-1-.7.1-.7.1-.7 1.1.1 1.7 1.1 1.7 1.1 1 1.7 2.6 1.2 3.2.9.1-.7.4-1.2.7-1.5-2.5-.3-5.1-1.2-5.1-5.5 0-1.2.4-2.1 1.1-2.9-.1-.3-.5-1.4.1-2.9 0 0 .9-.3 3 1.1a10.4 10.4 0 0 1 5.4 0c2-1.4 3-1.1 3-1.1.6 1.5.2 2.6.1 2.9.7.8 1.1 1.7 1.1 2.9 0 4.3-2.6 5.2-5.1 5.5.4.3.8 1 .8 2v3c0 .4.2.7.8.6A11.1 11.1 0 0 0 12 .9Z"/></svg></span>
                        <span class="ml-2 hidden h-8 items-center border-l border-white/20 pl-4 text-xs text-blue-100/80 sm:inline-flex"><span class="mr-2 text-lg text-rose-400">♡</span>Theo dõi chúng tôi</span>
                    </div>
                </section>

                <section class="site-footer-column">
                    <h2 class="site-footer-heading"><span class="site-footer-heading-icon"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 13v-2a8 8 0 0 1 16 0v2M4 12H3v6h4v-6H4zm16 0h1v6h-4v-6h3zM17 18a5 5 0 0 1-5 3h-2"/></svg></span>Hỗ trợ khách hàng</h2>
                    <nav class="site-footer-links" aria-label="Hỗ trợ khách hàng"><a href="{{ route('pages.shipping') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7h11v10H3zM14 10h4l3 3v4h-7z"/><circle cx="7" cy="19" r="1.5"/><circle cx="18" cy="19" r="1.5"/></svg>Giao hàng & thanh toán</a><a href="{{ route('pages.returns') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h13l-3-3m3 3-3 3M20 17H7l3 3m-3-3 3-3"/></svg>Đổi trả & bảo hành</a><a href="{{ route('pages.privacy') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 19 6v5c0 4.5-3 7.5-7 10-4-2.5-7-5.5-7-10V6z"/><path d="m9 12 2 2 4-4"/></svg>Chính sách dữ liệu</a><a href="{{ route('pages.support') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 4.5 1.5c-1.2 1-2 1.5-2 3m0 3h.01"/></svg>Liên hệ hỗ trợ</a></nav>
                </section>

                <section class="site-footer-column">
                    <h2 class="site-footer-heading"><span class="site-footer-heading-icon"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 3h12v18H6zM9 7h6M9 11h6M9 15h3"/></svg></span>Danh mục sản phẩm</h2>
                    <nav class="site-footer-links" aria-label="Danh mục sản phẩm">
                        @foreach(($navCategories ?? collect())->take(6) as $navCategory)
                            <a href="{{ route('categories.show', $navCategory) }}"><x-category-icon :slug="$navCategory->slug" />{{ $navCategory->name }}</a>
                        @endforeach
                    </nav>
                </section>

                <section class="site-footer-column">
                    <h2 class="site-footer-heading"><span class="site-footer-heading-icon"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3 20 6v5c0 5-3 8-8 10-5-2-8-5-8-10V6z"/><path d="m9 12 2 2 4-4"/></svg></span>Dành cho doanh nghiệp</h2>
                    <nav class="site-footer-links" aria-label="Dành cho doanh nghiệp"><a href="{{ route('pages.contact') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m4 0h1m-6 4h1m4 0h1m-5 4h2"/></svg>Hỗ trợ đơn hàng số lượng lớn</a><a href="{{ route('register') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0m2-8h5m-2.5-2.5v5"/></svg>Đăng ký tài khoản doanh nghiệp</a><a href="{{ route('products.index') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg>Tra cứu sản phẩm</a></nav>
                </section>

                <a href="{{ route('pages.promotions') }}" class="site-footer-promo-widget">
                    <span class="site-footer-promo-art"><img src="{{ asset('images/footer-chip-widget-optimized.png') }}" alt="" loading="lazy" decoding="async"></span>
                    <span class="site-footer-promo-label">Ưu đãi đặc biệt</span>
                    <strong>Giảm đến 50%</strong>
                    <span class="site-footer-promo-copy">Cho đơn hàng đầu tiên</span>
                    <span class="site-footer-promo-button">Mua ngay <span aria-hidden="true">→</span></span>
                </a>
            </div>

            <div class="site-footer-legal flex flex-col gap-4 py-5 text-xs text-blue-100/75 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2"><span>© {{ now()->year }} Linh Kiện.</span><span class="text-blue-100/55">Bảo lưu mọi quyền.</span><span class="hidden h-6 border-l border-white/20 lg:block"></span><a href="{{ route('pages.returns') }}">Đổi trả & bảo hành</a><span class="hidden h-4 border-l border-white/20 sm:block"></span><a href="{{ route('pages.privacy') }}">Chính sách bảo mật</a><span class="hidden h-4 border-l border-white/20 sm:block"></span><a href="{{ route('pages.shipping') }}">Chính sách vận chuyển</a></div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="mr-1 text-blue-100/75">Thanh toán an toàn:</span>
                    <span class="site-payment-logo site-payment-logo--visa">VISA</span>
                    <span class="site-payment-logo site-payment-logo--mastercard" aria-label="Mastercard"><i></i><i></i></span>
                    <span class="site-payment-logo site-payment-logo--momo">momo</span>
                    <span class="site-payment-logo site-payment-logo--vnpay"><b>◆</b> VNPAY</span>
                </div>
                <a href="#page-top" class="site-footer-top grid size-12 shrink-0 place-items-center rounded-full text-3xl font-light text-white" aria-label="Lên đầu trang">↑</a>
            </div>
        </div>
    </footer>
</body>
</html>
