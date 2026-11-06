@extends('layouts.store')

@section('title', 'Hồ sơ tài khoản')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:py-10">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
            <div>
                <a href="{{ route('account.index') }}" class="text-xs font-bold text-[#164d8b] hover:text-blue-700">← Quay lại tài khoản</a>
                <p class="mt-5 text-xs font-bold uppercase tracking-[.16em] text-blue-700">Thông tin cá nhân</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-[#10283e]">Hồ sơ của bạn</h1>
                <p class="mt-1 text-sm text-slate-500">Cập nhật thông tin liên hệ và lưu các địa chỉ nhận hàng.</p>
            </div>
            <a href="{{ route('account.orders.index') }}" class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-[#c9d7e8] bg-white px-4 py-2.5 text-sm font-bold text-[#164d8b] shadow-sm transition hover:border-blue-300 hover:bg-blue-50">
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 4h14v16H5zM8 8h8m-8 4h8m-8 4h4"/></svg>
                Lịch sử đặt hàng
            </a>
        </div>

        <div class="grid items-start gap-6 xl:grid-cols-[.85fr_1.15fr]">
            <div class="grid content-start gap-6">
                <section class="rounded-2xl border border-[#dce6f2] bg-white p-5 shadow-[0_8px_24px_rgba(27,59,100,.045)] sm:p-7">
                    <div class="flex items-start gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700">
                            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/></svg>
                        </span>
                        <div>
                            <h2 class="font-bold text-[#10283e]">Thông tin cá nhân</h2>
                            <p class="mt-1 text-xs text-slate-500">Thông tin dùng để liên hệ và xác nhận đơn hàng.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('account.profile.update') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
                        @csrf
                        @method('PUT')

                        <label class="grid gap-1.5 text-xs font-bold text-slate-600 sm:col-span-2">
                            Họ và tên
                            <input name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" class="rounded-xl px-3 py-3 text-sm font-normal">
                        </label>
                        <label class="grid gap-1.5 text-xs font-bold text-slate-600">
                            Email
                            <input value="{{ $user->email }}" readonly class="rounded-xl bg-slate-50 px-3 py-3 text-sm font-normal text-slate-500">
                        </label>
                        <label class="grid gap-1.5 text-xs font-bold text-slate-600">
                            Số điện thoại
                            <input name="phone" value="{{ old('phone', $user->phone) }}" required autocomplete="tel" class="rounded-xl px-3 py-3 text-sm font-normal">
                        </label>
                        <label class="grid gap-1.5 text-xs font-bold text-slate-600 sm:col-span-2">
                            Tên công ty
                            <input name="company_name" value="{{ old('company_name', $user->company_name) }}" autocomplete="organization" class="rounded-xl px-3 py-3 text-sm font-normal">
                        </label>
                        <label class="grid gap-1.5 text-xs font-bold text-slate-600 sm:col-span-2">
                            Mã số thuế
                            <input name="tax_number" value="{{ old('tax_number', $user->tax_number) }}" class="rounded-xl px-3 py-3 text-sm font-normal">
                        </label>
                        <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#10283e] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#183f63] sm:col-span-2">
                            Lưu thông tin cá nhân
                        </button>
                    </form>
                </section>

                <section class="rounded-2xl border border-[#dce6f2] bg-white p-5 shadow-[0_8px_24px_rgba(27,59,100,.045)] sm:p-7">
                    <div class="flex items-start gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-700">
                            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3m-4 5v2"/></svg>
                        </span>
                        <div>
                            <h2 class="font-bold text-[#10283e]">Đổi mật khẩu</h2>
                            <p class="mt-1 text-xs text-slate-500">Nhập mật khẩu hiện tại và xác nhận mật khẩu mới.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('account.profile.password.update') }}" class="mt-6 grid gap-4">
                        @csrf
                        @method('PATCH')

                        <label class="grid gap-1.5 text-xs font-bold text-slate-600">
                            Mật khẩu hiện tại
                            <input type="password" name="current_password" required autocomplete="current-password" class="rounded-xl px-3 py-3 text-sm font-normal">
                        </label>
                        <label class="grid gap-1.5 text-xs font-bold text-slate-600">
                            Mật khẩu mới
                            <input type="password" name="password" required minlength="8" autocomplete="new-password" class="rounded-xl px-3 py-3 text-sm font-normal">
                        </label>
                        <label class="grid gap-1.5 text-xs font-bold text-slate-600">
                            Xác nhận mật khẩu mới
                            <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="rounded-xl px-3 py-3 text-sm font-normal">
                        </label>
                        <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#10283e] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#183f63]">
                            Cập nhật mật khẩu
                        </button>
                    </form>
                </section>
            </div>

            <section class="rounded-2xl border border-[#dce6f2] bg-white p-5 shadow-[0_8px_24px_rgba(27,59,100,.045)] sm:p-7">
                <div class="flex items-start gap-3">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-indigo-50 text-indigo-700">
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                    </span>
                    <div>
                        <h2 class="font-bold text-[#10283e]">Địa chỉ giao hàng</h2>
                        <p class="mt-1 text-xs text-slate-500">Lưu nhiều địa chỉ để chọn nhanh khi đặt hàng.</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-3">
                    @forelse ($addresses as $address)
                        <article class="rounded-xl border {{ $address->is_default ? 'border-blue-200 bg-blue-50/50' : 'border-slate-200 bg-white' }} p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-bold text-slate-900">{{ $address->recipient_name }}</h3>
                                        @if ($address->is_default)
                                            <span class="rounded-full bg-blue-100 px-2.5 py-1 text-[10px] font-bold text-blue-800">Mặc định</span>
                                        @endif
                                    </div>
                                    <p class="mt-2 text-xs font-semibold text-slate-700">Số điện thoại: {{ $address->recipient_phone }}</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-600">{{ $address->address_line }}, {{ $address->ward_name }}, {{ $address->province_name }}</p>
                                    @if ($address->note)
                                        <p class="mt-1 text-xs text-slate-500">Ghi chú: {{ $address->note }}</p>
                                    @endif
                                </div>

                                <div class="flex shrink-0 items-center gap-2">
                                    @unless ($address->is_default)
                                        <form method="POST" action="{{ route('account.addresses.default', $address) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="rounded-lg border border-blue-200 bg-white px-3 py-2 text-[11px] font-bold text-blue-800 transition hover:bg-blue-50">Đặt mặc định</button>
                                        </form>
                                    @endunless
                                    <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" onsubmit="return confirm('Bạn có chắc muốn xóa địa chỉ này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg border border-rose-200 bg-white px-3 py-2 text-[11px] font-bold text-rose-700 transition hover:bg-rose-50">Xóa</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center">
                            <p class="text-sm font-semibold text-slate-700">Bạn chưa lưu địa chỉ giao hàng.</p>
                            <p class="mt-1 text-xs text-slate-500">Thêm địa chỉ bên dưới để sử dụng lại khi thanh toán.</p>
                        </div>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('account.addresses.store') }}" class="mt-6 grid gap-4 border-t border-slate-100 pt-6">
                    @csrf
                    <div>
                        <h3 class="text-sm font-bold text-[#10283e]">Thêm địa chỉ mới</h3>
                        <p class="mt-1 text-xs text-slate-500">Danh sách tỉnh/thành và xã/phường được tải từ API địa giới hành chính hiện hành.</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-1.5 text-xs font-bold text-slate-600 sm:col-span-2">
                            Người nhận
                            <input name="new_address[recipient_name]" value="{{ old('new_address.recipient_name', $user->name) }}" required maxlength="150" class="rounded-xl px-3 py-3 text-sm font-normal">
                        </label>
                        <label class="grid gap-1.5 text-xs font-bold text-slate-600 sm:col-span-2">
                            Số điện thoại người nhận
                            <input name="new_address[recipient_phone]" value="{{ old('new_address.recipient_phone', $user->phone) }}" required maxlength="30" class="rounded-xl px-3 py-3 text-sm font-normal">
                        </label>
                    </div>

                    <div data-address-geo data-initial-province="{{ old('new_address.province_code', '') }}" data-initial-ward="{{ old('new_address.ward_code', '') }}" class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-1.5 text-xs font-bold text-slate-600">
                            Tỉnh / Thành phố
                            <select name="new_address[province_code]" data-province-select required class="rounded-xl px-3 py-3 text-sm font-normal">
                                <option value="">Đang tải danh sách tỉnh/thành...</option>
                            </select>
                        </label>
                        <label class="grid gap-1.5 text-xs font-bold text-slate-600">
                            Xã / Phường
                            <select name="new_address[ward_code]" data-ward-select required disabled class="rounded-xl px-3 py-3 text-sm font-normal disabled:cursor-not-allowed disabled:bg-slate-100">
                                <option value="">Chọn tỉnh/thành trước</option>
                            </select>
                        </label>
                        <input type="hidden" name="new_address[province_name]" value="{{ old('new_address.province_name') }}" data-province-name>
                        <input type="hidden" name="new_address[ward_name]" value="{{ old('new_address.ward_name') }}" data-ward-name>
                    </div>
                    <p data-address-api-error hidden role="alert" class="text-xs text-rose-700">Không tải được danh sách địa giới hành chính. <button type="button" data-address-api-retry class="font-bold underline">Thử lại</button></p>

                    <label class="grid gap-1.5 text-xs font-bold text-slate-600">
                        Địa chỉ cụ thể
                        <input name="new_address[address_line]" value="{{ old('new_address.address_line') }}" required maxlength="500" placeholder="Số nhà, tên đường, tòa nhà..." class="rounded-xl px-3 py-3 text-sm font-normal">
                    </label>
                    <label class="grid gap-1.5 text-xs font-bold text-slate-600">
                        Ghi chú
                        <textarea name="new_address[note]" rows="2" maxlength="500" placeholder="Hướng dẫn thêm cho người giao hàng" class="rounded-xl px-3 py-3 text-sm font-normal">{{ old('new_address.note') }}</textarea>
                    </label>
                    <label class="flex items-center gap-2 text-xs font-medium text-slate-600">
                        <input type="checkbox" name="new_address[is_default]" value="1" @checked(old('new_address.is_default')) class="rounded border-slate-300 text-blue-700 focus:ring-blue-600">
                        Đặt làm địa chỉ mặc định
                    </label>
                    <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-3 text-sm font-bold text-white transition hover:bg-blue-800">
                        Thêm địa chỉ giao hàng
                    </button>
                </form>
            </section>
        </div>
    </div>
@endsection
