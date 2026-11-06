@extends('layouts.store')
@section('title', 'Thanh toán')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10"><div class="mb-7"><p class="text-xs font-bold tracking-wide text-emerald-700">Bước cuối</p><h1 class="mt-2 text-3xl font-bold tracking-tight text-emerald-950">Thông tin nhận hàng</h1><p class="mt-1 text-sm text-slate-500">Kiểm tra địa chỉ và phương thức thanh toán trước khi xác nhận.</p></div>
    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <p class="font-bold">Vui lòng kiểm tra lại thông tin đặt hàng.</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @php
        $checkoutPriceGroup = $user?->account_type === 'business' && $user->business_approved ? $user->price_group : 'retail';
        $selectedAddressId = old('saved_address_id', $addresses->first()?->id);
        $selectedAddress = $addresses->firstWhere('id', $selectedAddressId);
    @endphp
    <form action="{{ route('checkout.store') }}" method="POST" class="grid items-start gap-6 lg:grid-cols-[1fr_370px]">@csrf
        <div class="grid gap-5">
            <section class="rounded-lg border border-slate-200 bg-white p-5 sm:p-7">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-bold text-emerald-950">Thông tin liên hệ</h2>
                    @guest<a href="{{ route('login') }}" class="text-xs font-bold text-emerald-900 hover:underline">Đã có tài khoản? Đăng nhập</a>@endguest
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1.5 text-xs font-bold text-slate-600">Họ và tên<input required name="customer_name" value="{{ old('customer_name', $selectedAddress?->recipient_name ?? $user?->name) }}" class="rounded-lg border-slate-200 px-3 py-3 text-sm font-normal"></label>
                    <label class="grid gap-1.5 text-xs font-bold text-slate-600">Số điện thoại<input required name="customer_phone" value="{{ old('customer_phone', $selectedAddress?->recipient_phone ?? $user?->phone) }}" class="rounded-lg border-slate-200 px-3 py-3 text-sm font-normal"></label>
                    <label class="grid gap-1.5 text-xs font-bold text-slate-600 sm:col-span-2">Email nhận xác nhận<input type="email" name="customer_email" value="{{ old('customer_email', $user?->email) }}" class="rounded-lg border-slate-200 px-3 py-3 text-sm font-normal"><span class="text-[11px] font-normal text-slate-400">Email giúp bạn nhận thông báo và mã đơn hàng.</span></label>
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5 sm:p-7">
                <h2 class="text-lg font-bold text-emerald-950">Địa chỉ giao hàng</h2>
                @if ($addresses->isNotEmpty())
                    <label class="mt-5 grid gap-1.5 text-xs font-bold text-slate-600">
                        Chọn địa chỉ đã lưu
                        <select name="saved_address_id" data-saved-address-select class="rounded-lg border-slate-200 px-3 py-3 text-sm font-normal">
                            <option value="" @selected(! $selectedAddress)>Nhập địa chỉ mới</option>
                            @foreach ($addresses as $address)
                                <option value="{{ $address->id }}" data-province-code="{{ $address->province_code }}" data-province-name="{{ $address->province_name }}" data-ward-code="{{ $address->ward_code }}" data-ward-name="{{ $address->ward_name }}" data-address-line="{{ $address->address_line }}" data-note="{{ $address->note }}" data-recipient-name="{{ $address->recipient_name }}" data-recipient-phone="{{ $address->recipient_phone }}" @selected((string) $selectedAddressId === (string) $address->id)>{{ $address->recipient_name }} · {{ $address->address_line }}, {{ $address->ward_name }}, {{ $address->province_name }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif

                <div data-address-geo data-initial-province="{{ old('shipping_province_code', $selectedAddress?->province_code ?? '') }}" data-initial-ward="{{ old('shipping_ward_code', $selectedAddress?->ward_code ?? '') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1.5 text-xs font-bold text-slate-600">
                        Tỉnh / Thành phố
                        <select name="shipping_province_code" data-province-select required class="rounded-lg border-slate-200 px-3 py-3 text-sm font-normal"><option value="">Đang tải danh sách tỉnh/thành...</option></select>
                    </label>
                    <label class="grid gap-1.5 text-xs font-bold text-slate-600">
                        Xã / Phường
                        <select name="shipping_ward_code" data-ward-select required disabled class="rounded-lg border-slate-200 px-3 py-3 text-sm font-normal disabled:cursor-not-allowed disabled:bg-slate-100"><option value="">Chọn tỉnh/thành trước</option></select>
                    </label>
                    <input type="hidden" name="shipping_city" value="{{ old('shipping_city', $selectedAddress?->province_name) }}" data-province-name>
                    <input type="hidden" name="shipping_ward_name" value="{{ old('shipping_ward_name', $selectedAddress?->ward_name) }}" data-ward-name>
                </div>
                <p data-address-api-error hidden role="alert" class="mt-2 text-xs text-rose-700">Không tải được danh sách địa giới hành chính. <button type="button" data-address-api-retry class="font-bold underline">Thử lại</button></p>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1.5 text-xs font-bold text-slate-600 sm:col-span-2">Địa chỉ cụ thể<input required name="shipping_address" value="{{ old('shipping_address', $selectedAddress?->address_line) }}" placeholder="Số nhà, tên đường, tòa nhà..." class="rounded-lg border-slate-200 px-3 py-3 text-sm font-normal"></label>
                    <label class="grid gap-1.5 text-xs font-bold text-slate-600 sm:col-span-2">Ghi chú giao hàng<input name="customer_note" value="{{ old('customer_note', $selectedAddress?->note) }}" placeholder="Hướng dẫn thêm cho người giao" class="rounded-lg border-slate-200 px-3 py-3 text-sm font-normal"></label>
                </div>
            </section>
            <section class="rounded-lg border border-slate-200 bg-white p-5 sm:p-7"><h2 class="text-lg font-bold text-emerald-950">Thông tin xuất hóa đơn (không bắt buộc)</h2><div class="mt-5 grid gap-4 sm:grid-cols-2"><label class="grid gap-1.5 text-xs font-bold text-slate-600">Tên công ty<input name="company_name" value="{{ old('company_name',$user?->company_name) }}" class="rounded-lg border-slate-200 px-3 py-3 text-sm font-normal"></label><label class="grid gap-1.5 text-xs font-bold text-slate-600">Mã số thuế<input name="tax_number" value="{{ old('tax_number',$user?->tax_number) }}" class="rounded-lg border-slate-200 px-3 py-3 text-sm font-normal"></label></div></section>
            <section class="rounded-lg border border-slate-200 bg-white p-5 sm:p-7">
                <h2 class="text-lg font-bold text-emerald-950">Phương thức thanh toán</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 transition has-[:checked]:border-emerald-900 has-[:checked]:bg-emerald-50">
                        <input type="radio" name="payment_method" value="cod" @checked(old('payment_method', 'cod') === 'cod') class="mt-1 text-emerald-900 focus:ring-emerald-800">
                        <span><strong class="block text-sm">Thanh toán khi nhận hàng</strong><span class="mt-1 block text-xs text-slate-500">Bạn thanh toán khi nhận đơn; đơn hàng chờ nhân viên xác nhận.</span></span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 transition has-[:checked]:border-emerald-900 has-[:checked]:bg-emerald-50">
                        <input type="radio" name="payment_method" value="bank_transfer" @checked(old('payment_method') === 'bank_transfer') class="mt-1 text-emerald-900 focus:ring-emerald-800">
                        <span><strong class="block text-sm">{{ $paymentDriver === 'mock' ? 'Thanh toán online (mô phỏng)' : 'Chuyển khoản ngân hàng qua PayOS' }}</strong><span class="mt-1 block text-xs text-slate-500">{{ $paymentDriver === 'mock' ? 'Môi trường đồ án: bạn có thể mô phỏng giao dịch thành công hoặc thất bại ở bước tiếp theo.' : 'Sau khi xác nhận, PayOS mở trang thanh toán có mã QR để bạn quét bằng ứng dụng ngân hàng.' }}</span></span>
                    </label>
                </div>
                <p class="mt-3 text-xs leading-5 text-slate-500">{{ $paymentDriver === 'mock' ? 'Đây là giao dịch demo, không thu tiền thật. Chọn kết quả thành công hoặc thất bại trên trang mô phỏng.' : 'Đơn chuyển khoản chỉ được ghi nhận đã thanh toán sau khi máy chủ nhận và xác thực webhook giao dịch từ PayOS.' }}</p>
            </section>
        </div>
        <aside class="rounded-lg border border-slate-200 bg-white p-5 lg:sticky lg:top-32"><h2 class="text-lg font-bold text-emerald-950">Đơn hàng</h2><div class="mt-4 max-h-60 divide-y divide-slate-100 overflow-auto">@foreach($cart->items as $item)<div class="flex justify-between gap-3 py-3 text-sm"><span class="min-w-0"><strong class="block line-clamp-2">{{ $item->product->name }}</strong><span class="text-xs text-slate-500">{{ $item->product->sku }} × {{ $item->quantity }}</span></span><span class="shrink-0 font-semibold">{{ number_format($item->product->priceFor((int)$item->quantity,$checkoutPriceGroup)*$item->quantity,0,',','.') }}₫</span></div>@endforeach</div><label class="mt-4 grid gap-1.5 text-xs font-bold text-slate-600">Mã giảm giá<input name="coupon_code" value="{{ old('coupon_code') }}" placeholder="Ví dụ: MAKER10" class="rounded-lg border-slate-200 px-3 py-2.5 text-sm font-normal"></label><div class="mt-4 grid gap-3 border-t border-slate-100 pt-4 text-sm"><div class="flex justify-between"><span class="text-slate-500">Tạm tính</span><span>{{ number_format($subtotal,0,',','.') }}₫</span></div><div class="flex justify-between"><span class="text-slate-500">Phí giao hàng</span><span>{{ $shippingFee ? number_format($shippingFee,0,',','.').'₫' : 'Miễn phí' }}</span></div><div class="flex justify-between"><span class="text-slate-500">VAT</span><span>{{ config('store.vat_rate') > 0 ? 'Tính theo cấu hình' : 'Chưa áp dụng' }}</span></div><div class="flex justify-between border-t border-slate-200 pt-3 text-base font-bold text-emerald-950"><span>Tổng dự kiến</span><span>{{ number_format($subtotal+$shippingFee,0,',','.') }}₫</span></div></div><label class="mt-4 flex items-start gap-2 text-xs leading-5 text-slate-600"><input required type="checkbox" name="privacy_accepted" value="1" class="mt-0.5 rounded border-slate-300 text-emerald-900"><span>Tôi đồng ý với <a href="{{ route('pages.privacy') }}" class="font-bold text-emerald-900 underline">chính sách bảo vệ dữ liệu</a> và xác nhận thông tin đặt hàng là chính xác.</span></label><button class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-950 px-4 py-3.5 text-sm font-bold text-white hover:bg-emerald-800">Xác nhận đặt hàng <span>→</span></button><a href="{{ route('cart.index') }}" class="mt-3 block text-center text-xs font-bold text-slate-500 hover:text-emerald-900">Quay lại giỏ hàng</a></aside>
    </form>
</div>
@endsection
