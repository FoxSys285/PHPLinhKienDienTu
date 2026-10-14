<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#101d35">
    <title>@yield('title', 'Tài khoản') - Mạch Việt</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @stack('styles')
</head>
<body class="auth-page">
    <header class="auth-header">
        <a class="auth-brand" href="{{ route('home') }}" aria-label="Mạch Việt — Trang chủ">
            <span class="auth-brand-mark" aria-hidden="true"><svg viewBox="0 0 44 44"><path d="M22 3.5 39 13v18L22 40.5 5 31V13L22 3.5Z"/><path class="brand-bolt" d="M25 9 14 23h8l-3 12 12-16h-8l2-10Z"/></svg></span>
            <span><strong>MẠCH VIỆT</strong><small>LINH KIỆN · SÁNG TẠO</small></span>
        </a>
        <a class="auth-home-link" href="{{ route('home') }}"><span aria-hidden="true">←</span> Về trang chủ</a>
    </header>
    <main class="auth-shell {{ ($isRegister ?? false) ? 'auth-shell-register' : '' }}">
        <aside class="auth-story" aria-label="Giới thiệu Mạch Việt">
            <span class="auth-eyebrow"><span class="status-dot"></span> KẾT NỐI ĐAM MÊ CÔNG NGHỆ</span>
            <h1>Từ linh kiện nhỏ.<br>Đến ý tưởng <em>lớn.</em></h1>
            <p>Hệ thống Quản lý Linh kiện Điện tử</p>
            <div class="circuit-illustration" aria-hidden="true">
                <svg viewBox="0 0 460 310" fill="none">
                    <defs><linearGradient id="chipGradient" x1="158" y1="90" x2="306" y2="229" gradientUnits="userSpaceOnUse"><stop stop-color="#2d5da5"/><stop offset="1" stop-color="#162f58"/></linearGradient></defs>
                    <g class="circuit-traces" stroke="#49709c" stroke-width="2">
                        <path d="M173 121H110L75 86H30M173 146H88L52 182H12M173 172H102V242H42M173 199H135V285H90M201 96V49L165 13M228 96V22M256 96V52H334V15M284 121H349L383 87H445M284 148H401L438 185M284 172H337V236H421M284 199H311V282H377M202 222V273H174M228 222V302M255 222V262H277"/>
                        <g fill="#70dbd1" stroke="none"><circle cx="30" cy="86" r="5"/><circle cx="12" cy="182" r="5"/><circle cx="42" cy="242" r="5"/><circle cx="90" cy="285" r="5"/><circle cx="165" cy="13" r="5"/><circle cx="228" cy="22" r="5"/><circle cx="334" cy="15" r="5"/><circle cx="445" cy="87" r="5"/><circle cx="438" cy="185" r="5"/><circle cx="421" cy="236" r="5"/><circle cx="377" cy="282" r="5"/><circle cx="174" cy="273" r="5"/><circle cx="228" cy="302" r="5"/><circle cx="277" cy="262" r="5"/></g>
                    </g>
                    <rect x="165" y="87" width="128" height="144" rx="18" fill="#0e203c" stroke="#6186b7"/>
                    <rect x="178" y="100" width="102" height="118" rx="12" fill="url(#chipGradient)" stroke="#6087bc"/>
                    <path d="m236 124-30 40h24l-10 32 35-46h-26l7-26Z" fill="#78e4d7"/>
                    <path d="M187 209h17M251 109h17" stroke="#9db8df" stroke-width="2" stroke-linecap="round"/>
                    <g fill="#46678e"><rect x="34" y="105" width="43" height="23" rx="5"/><rect x="363" y="256" width="39" height="19" rx="4"/><circle cx="378" cy="54" r="11"/></g>
                </svg>
                <span class="circuit-label">BUILD. CONNECT. CREATE.</span>
            </div>
            <div class="auth-story-footer"><span class="story-icon" aria-hidden="true">✦</span><span>Không gian dành cho những người<br><strong>yêu công nghệ và sáng tạo.</strong></span></div>
        </aside>
        <section class="auth-panel" aria-label="@yield('title', 'Tài khoản')">
            @yield('content')
        </section>
    </main>
    <footer class="auth-footer"><span>© {{ now()->year }} Mạch Việt</span><span>Linh kiện đáng tin cậy cho mọi ý tưởng.</span></footer>
    <script src="{{ asset('js/jquery-3.7.0.min.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.unobtrusive.min.js') }}"></script>
    <script src="{{ asset('js/auth.js') }}"></script>
    @stack('scripts')
</body>
</html>
