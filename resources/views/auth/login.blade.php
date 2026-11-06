@extends('layouts.store')

@section('title', 'Đăng nhập')

@section('content')
<div class="auth-page mx-auto max-w-5xl px-4 py-8 sm:py-12">
    <div class="auth-shell grid overflow-hidden rounded-[30px] border border-white/80 bg-white/35 shadow-[0_20px_60px_rgba(55,91,169,.14)] backdrop-blur-2xl lg:grid-cols-[.85fr_1.15fr]">
        <aside class="auth-promo-panel relative hidden flex-col justify-between overflow-hidden p-8 text-white lg:flex xl:p-10">
            <a href="{{ route('home') }}" class="relative z-10 inline-flex w-fit items-center gap-3" aria-label="Linh Kiện — Trang chủ">
                <x-brand-mark size="size-12" />
                <span><strong class="block text-lg font-extrabold">Linh Kiện</strong><span class="text-[10px] text-blue-100">Kết nối đam mê công nghệ</span></span>
            </a>
            <div class="relative z-10 max-w-sm py-10">
                <p class="text-xs font-bold uppercase tracking-[.2em] text-cyan-200">Chào mừng trở lại</p>
                <h2 class="mt-3 text-3xl font-extrabold leading-tight xl:text-4xl">Mọi linh kiện cho ý tưởng tiếp theo.</h2>
                <p class="mt-4 text-sm leading-6 text-blue-100">Đăng nhập để quản lý đơn hàng, xem giá doanh nghiệp và tiếp tục dự án của bạn.</p>
                <div class="mt-6 flex flex-wrap gap-2"><span class="rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[11px]">Theo dõi đơn hàng</span><span class="rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[11px]">Giá doanh nghiệp</span></div>
            </div>
            <span class="relative z-10 text-xs text-blue-100/80">Đồng hành cùng cộng đồng maker và kỹ sư.</span>
        </aside>

        <section class="auth-card p-6 sm:p-9 lg:p-10" aria-labelledby="login-heading">
            <span class="auth-icon-badge grid size-12 place-items-center rounded-2xl text-blue-700"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5m5 5H3"/></svg></span>
            <p class="mt-5 text-xs font-bold uppercase tracking-[.18em] text-blue-700">Tài khoản Linh Kiện</p>
            <h1 id="login-heading" class="mt-2 text-3xl font-extrabold tracking-tight text-[#102653]">Đăng nhập</h1>
            <p class="mt-1 text-sm text-slate-500">Truy cập đơn hàng, giá doanh nghiệp và cập nhật tài khoản.</p>

            <form method="POST" action="{{ route('login') }}" class="mt-7 grid gap-4">
                @csrf
                <label class="grid gap-1.5 text-xs font-bold text-slate-600">Email hoặc số điện thoại<input required autofocus name="login" value="{{ old('login') }}" autocomplete="username" placeholder="ban@email.com" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"></label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-600">Mật khẩu<input required type="password" name="password" autocomplete="current-password" placeholder="Nhập mật khẩu" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"></label>
                <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                    <label class="flex items-center gap-2 text-slate-500"><input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-blue-700"> Ghi nhớ đăng nhập</label>
                    <a href="{{ route('password.request') }}" class="font-bold text-blue-800 hover:underline">Quên mật khẩu?</a>
                </div>
                <button class="auth-primary-action mt-1 rounded-xl px-4 py-3.5 text-sm font-bold text-white">Đăng nhập</button>
            </form>
            <p class="mt-6 text-center text-sm text-slate-500">Chưa có tài khoản? <a href="{{ route('register') }}" class="font-bold text-blue-800 hover:underline">Đăng ký ngay</a></p>
        </section>
    </div>
</div>
@endsection
