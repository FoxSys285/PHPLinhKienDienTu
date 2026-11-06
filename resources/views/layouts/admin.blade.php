<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#eaf0fb">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <title>@yield('title', 'Quản trị cửa hàng') · Linh Kiện</title>
    @stack('meta')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden font-sans text-[#172554] antialiased" style="background: radial-gradient(ellipse at 78% 5%, rgba(154, 157, 242, .38), transparent 29%), radial-gradient(ellipse at 23% 85%, rgba(146, 188, 255, .45), transparent 33%), linear-gradient(135deg, #e9edf6 0%, #eef2fb 52%, #dce6f2 100%);">
    <aside class="fixed inset-y-5 left-5 z-30 hidden w-[260px] md:block">
        <div class="flex h-full flex-col rounded-[28px] border border-white/80 bg-white/55 p-4 shadow-[0_22px_60px_-28px_rgba(31,41,85,.42)] backdrop-blur-2xl">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-2 py-2.5">
                <span class="grid size-11 shrink-0 place-items-center rounded-2xl border border-white bg-white/60 text-sm font-black tracking-tight text-[#172554] shadow-sm">LK</span>
                <span class="min-w-0 leading-tight">
                    <span class="block truncate text-sm font-extrabold tracking-tight text-[#172554]">Linh Kiện</span>
                    <span class="mt-1 block text-[11px] font-medium text-slate-500">Trung tâm quản trị</span>
                </span>
            </a>

            <div class="mt-4 min-h-0 flex-1 overflow-y-auto"><x-admin-nav /></div>

            <div class="mt-4 rounded-2xl border border-white/80 bg-white/40 p-3">
                <div class="mb-3 flex items-center gap-2.5 px-1">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-white/70 text-xs font-black text-[#172554]">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="min-w-0 leading-tight">
                        <span class="block truncate text-xs font-bold text-[#172554]">{{ auth()->user()->name }}</span>
                        <span class="mt-1 block text-[10px] font-medium text-slate-500">{{ ['admin' => 'Quản trị viên', 'sales' => 'Kinh doanh', 'warehouse' => 'Kho vận', 'accountant' => 'Kế toán'][auth()->user()->role] ?? auth()->user()->role }}</span>
                    </span>
                </div>
                <a href="{{ route('home') }}" class="flex min-h-10 items-center justify-center rounded-xl border border-white/80 bg-white/50 px-3 text-xs font-bold text-[#172554] transition hover:bg-white/85">Xem cửa hàng</a>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="flex min-h-10 w-full items-center justify-center rounded-xl border border-white/80 bg-white/50 px-3 text-xs font-bold text-rose-600 transition hover:bg-rose-50/80">Đăng xuất</button>
                </form>
            </div>
        </div>
    </aside>

    <header data-site-header class="admin-mobile-header sticky top-0 z-30 border-b border-white/65 bg-white/55 px-4 py-3 backdrop-blur-xl md:hidden">
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-2.5">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl border border-white bg-white/70 text-xs font-black text-[#172554]">LK</span>
                <span class="min-w-0 leading-tight"><span class="block truncate text-sm font-extrabold">Linh Kiện</span><span class="block text-[10px] text-slate-500">{{ auth()->user()->name }}</span></span>
            </a>
            <div class="flex shrink-0 items-center gap-2">
                <a href="{{ route('home') }}" class="rounded-xl border border-white/80 bg-white/55 px-3 py-2 text-[11px] font-bold">Cửa hàng</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-xl border border-rose-200 bg-white/55 px-3 py-2 text-[11px] font-bold text-rose-600">Đăng xuất</button>
                </form>
            </div>
        </div>
    </header>

    <div class="sticky top-[66px] z-20 border-b border-white/50 bg-white/25 px-4 py-2 backdrop-blur-xl md:hidden"><x-admin-nav /></div>

    <div class="md:pl-[300px]">
        @if (session('success') || session('error') || $errors->any())
            <div class="mx-auto mt-4 grid max-w-screen-2xl gap-2 px-4 sm:px-6 lg:px-8" data-flash>
                @if (session('success'))
                    <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/85 px-4 py-3 text-sm font-medium text-emerald-950 shadow-sm">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="rounded-2xl border border-rose-200/80 bg-rose-50/85 px-4 py-3 text-sm font-medium text-rose-900 shadow-sm">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-200/80 bg-rose-50/85 px-4 py-3 text-sm text-rose-900 shadow-sm">
                        <p class="font-bold">Vui lòng kiểm tra lại thông tin:</p>
                        <ul class="mt-1 list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        <main class="admin-main min-h-screen px-4 py-6 sm:px-6 md:py-8 lg:px-9" data-reveal-scope="admin">@yield('content')</main>
        @if(request()->routeIs('admin.dashboard'))
            <div data-guide-root data-guide-variant="admin"></div>
        @endif
        @if(auth()->user()->role === 'warehouse')
            <a href="{{ route('admin.inventory.index') }}" class="admin-quick-action" aria-label="Mở kiểm kê kho">
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 7h18v14H3zM7 7V4h10v3M7 11h10m-10 4h6"/></svg>
                <span>Kiểm kê kho</span>
            </a>
        @elseif(in_array(auth()->user()->role, ['admin', 'sales'], true))
            <a href="{{ route('admin.products.create') }}" class="admin-quick-action" aria-label="Thêm sản phẩm">
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14m-7-7h14"/></svg>
                <span>Thêm sản phẩm</span>
            </a>
        @endif
    </div>
</body>
</html>
