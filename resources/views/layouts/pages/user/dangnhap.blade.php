@extends('layouts.auth')

@section('title', 'Đăng nhập')

@section('content')
    <div class="auth-form-content">
        <span class="form-eyebrow">CHÀO MỪNG BẠN TRỞ LẠI</span>
        <h2>Đăng nhập</h2>
        <p class="form-intro">Đăng nhập để theo dõi đơn hàng và quản lý tài khoản Mạch Việt.</p>
        @if ($errors->any())<div class="auth-errors" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form class="auth-form" action="{{ route('login.store') }}" method="post">
            @csrf
            <div class="form-field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div>
            <div class="form-field"><label for="password">Mật khẩu</label><div class="password-field"><input id="password" type="password" name="password" autocomplete="current-password" required><button class="password-toggle" type="button" data-password-target="password" aria-label="Hiện mật khẩu"></button></div></div>
            <label class="check-label" style="margin-bottom:22px"><input type="checkbox" name="remember" value="1"> Ghi nhớ đăng nhập</label>
            <button class="auth-submit" type="submit">Đăng nhập <span aria-hidden="true">→</span></button>
        </form>
        <p class="auth-switch">Chưa có tài khoản? <a href="{{ route('register') }}">Tạo tài khoản khách hàng</a></p>
    </div>
@endsection
