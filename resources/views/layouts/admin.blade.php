<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#101d35">
    <title>@yield('title', 'Quản trị') - Mạch Việt</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    <link rel="stylesheet" href="{{ asset('css/role-menus.css') }}">
    @stack('styles')
</head>
<body class="admin-page">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <a class="brand admin-brand" href="{{ url('/admin') }}" aria-label="Mạch Việt — Khu vực Admin">
                <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 44 44"><path d="M22 3.5 39 13v18L22 40.5 5 31V13L22 3.5Z"/><path d="M25 9 14 23h8l-3 12 12-16h-8l2-10Z"/></svg></span>
                <span class="brand-copy"><strong>MẠCH VIỆT</strong><small>KHU VỰC ADMIN</small></span>
            </a>
            @include('layouts.partials.role-menu')
            <div class="admin-sidebar-footer"><span class="admin-status-dot"></span> Đã đăng nhập với quyền Admin</div>
        </aside>
        <div class="admin-workspace">
            <header class="admin-header">
                <div><span class="admin-header-eyebrow">HỆ THỐNG QUẢN LÝ LINH KIỆN ĐIỆN TỬ</span><strong>@yield('title', 'Quản trị')</strong></div>
                <div class="admin-header-account">
                    <span class="admin-user-avatar" aria-hidden="true">{{ mb_substr(auth()->user()?->name ?? 'A', 0, 1) }}</span>
                    <span><strong>{{ auth()->user()?->name ?? 'Quản trị viên' }}</strong><small>Quản trị viên</small></span>
                    @auth
                        <form action="{{ url('/logout') }}" method="post" class="account-logout-form">
                            @csrf
                            <button type="submit" class="account-logout">Đăng xuất</button>
                        </form>
                    @endauth
                </div>
            </header>
            <main class="admin-main">
                @yield('content')
            </main>
            <footer class="admin-footer">© {{ now()->year }} Mạch Việt · Hệ thống Quản lý Linh kiện Điện tử</footer>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
