@extends('layouts.store')

@section('title', 'Quên mật khẩu')

@section('content')
<div class="auth-page auth-page--compact mx-auto max-w-xl px-4 py-10 sm:py-14">
    <section class="auth-card auth-single-card rounded-[30px] border border-white/80 bg-white/55 p-6 shadow-[0_20px_60px_rgba(55,91,169,.14)] backdrop-blur-2xl sm:p-9" aria-labelledby="forgot-password-heading">
        <span class="auth-icon-badge grid size-12 place-items-center rounded-2xl text-blue-700"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg></span>
        <p class="mt-5 text-xs font-bold uppercase tracking-[.18em] text-blue-700">Khôi phục tài khoản</p>
        <h1 id="forgot-password-heading" class="mt-2 text-3xl font-extrabold tracking-tight text-[#102653]">Quên mật khẩu?</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Nhập email đã đăng ký. Nếu tài khoản tồn tại, chúng tôi sẽ gửi liên kết đặt lại mật khẩu.</p>
        <form method="POST" action="{{ route('password.email') }}" class="mt-6 grid gap-4">
            @csrf
            <label class="grid gap-1.5 text-xs font-bold text-slate-600">Email<input required type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="ban@email.com" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"></label>
            <button class="auth-primary-action rounded-xl px-4 py-3 text-sm font-bold text-white">Gửi liên kết khôi phục</button>
        </form>
        <a href="{{ route('login') }}" class="mt-5 block text-center text-xs font-bold text-blue-800 hover:underline">Quay lại đăng nhập</a>
    </section>
</div>
@endsection
