@extends('layouts.store')

@section('title', 'Đặt mật khẩu mới')

@section('content')
<div class="auth-page auth-page--compact mx-auto max-w-xl px-4 py-10 sm:py-14">
    <section class="auth-card auth-single-card rounded-[30px] border border-white/80 bg-white/55 p-6 shadow-[0_20px_60px_rgba(55,91,169,.14)] backdrop-blur-2xl sm:p-9" aria-labelledby="reset-password-heading">
        <span class="auth-icon-badge grid size-12 place-items-center rounded-2xl text-blue-700"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3m-4 4v3"/></svg></span>
        <p class="mt-5 text-xs font-bold uppercase tracking-[.18em] text-blue-700">Bảo mật tài khoản</p>
        <h1 id="reset-password-heading" class="mt-2 text-3xl font-extrabold tracking-tight text-[#102653]">Đặt mật khẩu mới</h1>
        <form method="POST" action="{{ route('password.update') }}" class="mt-6 grid gap-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <label class="grid gap-1.5 text-xs font-bold text-slate-600">Email<input required type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"></label>
            <label class="grid gap-1.5 text-xs font-bold text-slate-600">Mật khẩu mới<input required type="password" name="password" minlength="8" autocomplete="new-password" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"></label>
            <label class="grid gap-1.5 text-xs font-bold text-slate-600">Nhập lại mật khẩu<input required type="password" name="password_confirmation" minlength="8" autocomplete="new-password" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"></label>
            <button class="auth-primary-action rounded-xl px-4 py-3 text-sm font-bold text-white">Cập nhật mật khẩu</button>
        </form>
    </section>
</div>
@endsection
