@extends('layouts.auth')

@section('title', 'Tạo tài khoản')

@section('content')
    <div class="auth-form-content register-content">
        <span class="form-eyebrow">THAM GIA MẠCH VIỆT</span>
        <h2>Tạo tài khoản</h2>
        <p class="form-intro">Lưu thông tin đơn hàng và mua sắm linh kiện thuận tiện hơn.</p>
        @if ($errors->any())<div class="auth-errors" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form class="auth-form" action="{{ route('register.store') }}" method="post">
            @csrf
            <div class="form-field"><label for="name">Họ và tên</label><input id="name" name="name" value="{{ old('name') }}" maxlength="120" autocomplete="name" required></div>
            <div class="form-field"><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" required></div>
            <div class="form-field"><label for="password">Mật khẩu</label><div class="password-field"><input id="password" type="password" name="password" minlength="8" autocomplete="new-password" required><button class="password-toggle" type="button" data-password-target="password" aria-label="Hiện mật khẩu"></button></div></div>
            <div class="form-field"><label for="password_confirmation">Nhập lại mật khẩu</label><div class="password-field"><input id="password_confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required><button class="password-toggle" type="button" data-password-target="password_confirmation" aria-label="Hiện mật khẩu"></button></div></div>
            <button class="auth-submit" type="submit">Tạo tài khoản <span aria-hidden="true">→</span></button>
        </form>
        <p class="auth-switch">Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p>
    </div>
@endsection
