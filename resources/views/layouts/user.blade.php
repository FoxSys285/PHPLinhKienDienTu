<div>
    <!-- Knowing is not enough; we must apply. Being willing is not enough; we must do. - Leonardo da Vinci -->
</div>
@php
    $storeCategories = $categories ?? [];
    $cartCount = $cartCount ?? 0;
    $currentUser = auth()->user();
    $currentUserAttributes = $currentUser?->getAttributes() ?? [];
    $isAdmin = ($currentUserAttributes['role'] ?? null) === 'Admin';
@endphp
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#101d35">
    <title>@yield('title', 'Trang chủ') - Mạch Việt</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    <link rel="stylesheet" href="{{ asset('css/role-menus.css') }}">
    @stack('styles')
</head>
<body>
    <div class="announcement-bar">
        <div class="container site-container announcement-inner">
            <span>Giao hàng toàn quốc · Hỗ trợ kỹ thuật tận tâm</span>
            <span>Hotline <strong>1900 6868</strong> <span class="announcement-divider">|</span> Miễn phí giao hàng từ 500K</span>
        </div>
    </div>
    <header class="site-header">
        <div class="container site-container header-main">
            <a class="brand" href="{{ route('home') }}" aria-label="Mạch Việt - Trang chủ">
                <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 44 44" role="img"><path d="M22 3.5 39 13v18L22 40.5 5 31V13L22 3.5Z"/><path d="M25 9 14 23h8l-3 12 12-16h-8l2-10Z"/></svg></span>
                <span class="brand-copy"><strong>MẠCH VIỆT</strong><small>LINH KIỆN · SÁNG TẠO</small></span>
            </a>
            <form class="search-form" action="{{ route('products.index') }}" method="get" role="search">
                <span class="search-icon" aria-hidden="true">⌕</span>
                <input type="search" name="q" value="{{ request('q', $query ?? '') }}" placeholder="Tìm linh kiện, module, dụng cụ..." aria-label="Tìm kiếm sản phẩm">
                <button type="submit">Tìm kiếm</button>
            </form>
            <div class="header-actions">
                @auth
                    <div class="account-link">
                        <span class="action-icon" aria-hidden="true">♙</span>
                        <span class="account-copy"><small>{{ $isAdmin ? 'Admin' : 'Khách hàng' }}</small><strong>{{ $currentUser->name }}</strong></span>
                    </div>
                    @if ($isAdmin)
                        <a class="admin-access-link" href="{{ url('/admin') }}">Khu vực quản trị</a>
                    @endif
                    <form action="{{ route('logout') }}" method="post" class="account-logout-form">
                        @csrf
                        <button type="submit" class="account-logout">Đăng xuất</button>
                    </form>
                @else
                    <div class="account-link">
                        <span class="action-icon" aria-hidden="true">♙</span>
                        <span class="account-copy"><small>Xin chào!</small><strong><a href="{{ url('/dang-nhap') }}">Đăng nhập</a><i>/</i><a href="{{ url('/dang-ky') }}">Đăng ký</a></strong></span>
                    </div>
                @endauth
                @if (! $isAdmin)
                    <a class="cart-link" href="{{ url('/tai-khoan') }}" aria-label="Tài khoản"><span class="action-icon" aria-hidden="true">👤</span></a>
                    <a class="cart-link" href="{{ route('cart.index') }}" aria-label="Giỏ hàng"><span class="action-icon" aria-hidden="true">🛒</span><span class="cart-count">{{ $cartCount }}</span></a>
                @endif
            </div>
        </div>
        <nav class="top-nav" aria-label="Điều hướng chính">
            <div class="container site-container top-nav-inner">
                <a class="nav-category" href="{{ route('products.index') }}"><span aria-hidden="true">☰</span> Danh mục sản phẩm</a>
                <a href="{{ route('home') }}">Trang chủ</a>
                <a href="{{ route('products.index') }}">Sản phẩm</a>
                <a href="{{ route('products.index', ['sort' => 'newest']) }}">Linh kiện mới</a>
                <a href="{{ route('products.index', ['category' => 'DIY']) }}">Góc DIY</a>
                <span class="nav-promo">ƯU ĐÃI TUẦN NÀY <span aria-hidden="true">↗</span></span>
            </div>
        </nav>
    </header>
    <div class="container site-container page-shell">
        <aside class="category-sidebar" aria-label="Danh mục sản phẩm">
            <div class="sidebar-heading"><span class="sidebar-heading-icon">▦</span><span>Danh mục sản phẩm</span></div>
            <div class="category-list">
                @forelse ($storeCategories as $category)
                    <a class="category-item" href="{{ route('products.index', ['category' => data_get($category, 'filter', data_get($category, 'slug'))]) }}">
                        <span class="category-symbol" aria-hidden="true">{{ data_get($category, 'icon', '▦') }}</span>
                        <span class="category-name">{{ data_get($category, 'name', 'Danh mục') }}</span>
                        <span class="category-count">{{ data_get($category, 'product_count', 0) }}</span>
                    </a>
                @empty
                    <p class="category-name">Danh mục sản phẩm sẽ hiển thị tại đây.</p>
                @endforelse
            </div>
            <a class="sidebar-all-link" href="{{ route('products.index') }}">Xem tất cả danh mục <span>→</span></a>
        </aside>
        <main class="page-content">
            @yield('content')
        </main>
    </div>
    <footer class="site-footer">
        <div class="container site-container footer-main">
            <div class="footer-brand-block">
                <a class="brand footer-brand" href="{{ route('home') }}">
                    <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 44 44"><path d="M22 3.5 39 13v18L22 40.5 5 31V13L22 3.5Z"/><path d="M25 9 14 23h8l-3 12 12-16h-8l2-10Z"/></svg></span>
                    <span class="brand-copy"><strong>MẠCH VIỆT</strong><small>LINH KIỆN · SÁNG TẠO</small></span>
                </a>
                <p>Khơi nguồn sáng tạo với linh kiện điện tử đáng tin cậy cho mọi ý tưởng.</p>
            </div>
            <div class="footer-column"><h3>Hỗ trợ khách hàng</h3><a href="#">Hướng dẫn mua hàng</a><a href="#">Chính sách giao hàng</a><a href="#">Đổi trả &amp; bảo hành</a></div>
            <div class="footer-column"><h3>Về Mạch Việt</h3><a href="#">Câu chuyện của chúng tôi</a><a href="#">Liên hệ</a><a href="#">Góc chia sẻ DIY</a></div>
            <div class="footer-newsletter"><h3>Nhận tin từ Mạch Việt</h3><p>Mẹo DIY, sản phẩm mới và ưu đãi dành riêng cho bạn.</p><form><input type="email" placeholder="Email của bạn" aria-label="Email của bạn"><button type="button" aria-label="Đăng ký nhận tin">→</button></form></div>
        </div>
        <div class="footer-bottom"><div class="container site-container"><span>© {{ now()->year }} Mạch Việt. Thiết kế dành cho những người yêu công nghệ.</span><span>Thanh toán an toàn · Giao hàng toàn quốc</span></div></div>
    </footer>
    @stack('scripts')
</body>
</html>
