@extends('layouts.store')

@section('title', 'Tạo tài khoản')

@section('content')
<div class="auth-page mx-auto max-w-6xl px-4 py-8 sm:py-12">
    <div class="auth-shell grid overflow-hidden rounded-[30px] border border-white/80 bg-white/35 shadow-[0_20px_60px_rgba(55,91,169,.14)] backdrop-blur-2xl lg:grid-cols-[.75fr_1.25fr]">
        <aside class="auth-promo-panel relative hidden flex-col justify-between overflow-hidden p-8 text-white lg:flex xl:p-10">
            <a href="{{ route('home') }}" class="relative z-10 inline-flex w-fit items-center gap-3" aria-label="Linh Kiện — Trang chủ">
                <x-brand-mark size="size-12" />
                <span><strong class="block text-lg font-extrabold">Linh Kiện</strong><span class="text-[10px] text-blue-100">Kết nối đam mê công nghệ</span></span>
            </a>
            <div class="relative z-10 max-w-sm py-10">
                <p class="text-xs font-bold uppercase tracking-[.2em] text-cyan-200">Tham gia cộng đồng</p>
                <h2 class="mt-3 text-3xl font-extrabold leading-tight xl:text-4xl">Tạo nên điều mới từ những linh kiện phù hợp.</h2>
                <p class="mt-4 text-sm leading-6 text-blue-100">Lưu thông tin mua hàng và theo dõi các đơn hàng của bạn.</p>
                <div class="mt-7 grid gap-3 text-sm text-blue-50"><span class="flex items-center gap-2"><span class="text-cyan-300">✓</span> Theo dõi đơn hàng thuận tiện</span><span class="flex items-center gap-2"><span class="text-cyan-300">✓</span> Lựa chọn tài khoản cá nhân hoặc doanh nghiệp</span></div>
            </div>
            <span class="relative z-10 text-xs text-blue-100/80">Bắt đầu hành trình công nghệ của bạn.</span>
        </aside>

        <section class="auth-card p-6 sm:p-9 lg:p-10" aria-labelledby="register-heading">
            <p class="text-xs font-bold uppercase tracking-[.18em] text-blue-700">Tài khoản mới</p>
            <h1 id="register-heading" class="mt-2 text-3xl font-extrabold tracking-tight text-[#102653]">Tạo tài khoản</h1>
            <p class="mt-1 text-sm text-slate-500">Mua nhanh hơn, theo dõi đơn hàng và lưu thông tin xuất hóa đơn.</p>

            <form method="POST" action="{{ route('register') }}" class="mt-7 grid gap-4 sm:grid-cols-2">
                @csrf
                <label class="grid gap-1.5 text-xs font-bold text-slate-600 sm:col-span-2">Họ và tên<input required name="name" value="{{ old('name') }}" autocomplete="name" placeholder="Tên của bạn" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"></label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-600">Email<input required type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="ban@email.com" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"></label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-600">Số điện thoại<input required name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="09xx xxx xxx" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"></label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-600">Mật khẩu<input required type="password" name="password" minlength="8" autocomplete="new-password" placeholder="Tối thiểu 8 ký tự" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"><span class="text-[10px] font-normal text-slate-400">Tối thiểu 8 ký tự</span></label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-600">Nhập lại mật khẩu<input required type="password" name="password_confirmation" autocomplete="new-password" placeholder="Nhập lại mật khẩu" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"></label>

                <fieldset class="grid gap-2 sm:col-span-2">
                    <legend class="text-xs font-bold text-slate-600">Loại tài khoản</legend>
                    <div class="mt-1 grid gap-3 sm:grid-cols-2">
                        <label class="auth-account-type-card flex cursor-pointer items-center gap-3 rounded-2xl border border-white/80 bg-white/45 p-4 has-[:checked]:border-blue-400 has-[:checked]:bg-blue-50/75">
                            <input type="radio" name="account_type" value="individual" @checked(old('account_type', 'individual') === 'individual') class="text-blue-700">
                            <span><strong class="block text-sm text-[#102653]">Cá nhân</strong><span class="text-xs text-slate-500">Maker, sinh viên, kỹ sư</span></span>
                        </label>
                        <label class="auth-account-type-card flex cursor-pointer items-center gap-3 rounded-2xl border border-white/80 bg-white/45 p-4 has-[:checked]:border-blue-400 has-[:checked]:bg-blue-50/75">
                            <input type="radio" name="account_type" value="business" @checked(old('account_type') === 'business') class="text-blue-700">
                            <span><strong class="block text-sm text-[#102653]">Doanh nghiệp</strong><span class="text-xs text-slate-500">Cần xét duyệt để nhận giá B2B</span></span>
                        </label>
                    </div>
                </fieldset>

                <label class="grid gap-1.5 text-xs font-bold text-slate-600 sm:col-span-2">Tên doanh nghiệp <span class="font-normal text-slate-400">(nếu đăng ký B2B)</span><input name="company_name" value="{{ old('company_name') }}" placeholder="Tên công ty" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"></label>
                <label class="grid gap-1.5 text-xs font-bold text-slate-600 sm:col-span-2">Mã số thuế <span class="font-normal text-slate-400">(không bắt buộc)</span><input name="tax_number" value="{{ old('tax_number') }}" placeholder="Mã số thuế doanh nghiệp" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"></label>
                <label class="flex items-start gap-2 text-xs leading-5 text-slate-600 sm:col-span-2"><input required type="checkbox" name="privacy_accepted" value="1" class="mt-0.5 rounded border-slate-300 text-blue-700"><span>Tôi đồng ý với <a href="{{ route('pages.privacy') }}" class="font-bold text-blue-800 underline">chính sách bảo vệ dữ liệu</a>.</span></label>
                <button class="auth-primary-action rounded-xl px-4 py-3.5 text-sm font-bold text-white sm:col-span-2">Tạo tài khoản</button>
            </form>
            <p class="mt-6 text-center text-sm text-slate-500">Đã có tài khoản? <a href="{{ route('login') }}" class="font-bold text-blue-800 hover:underline">Đăng nhập</a></p>
        </section>
    </div>
</div>
@endsection
