@extends('layouts.admin')

@section('title', $voucher->exists ? 'Sửa khuyến mãi' : 'Tạo khuyến mãi')

@section('content')
    <a class="admin-back-link" href="{{ route('admin.vouchers.index') }}"><span aria-hidden="true">←</span> Danh sách khuyến mãi</a>
    <div class="admin-page-heading"><div><span class="admin-eyebrow">ƯU ĐÃI</span><h1>{{ $voucher->exists ? 'Sửa mã '.$voucher->code : 'Tạo mã khuyến mãi' }}</h1><p>Thiết lập mức giảm, thời gian và điều kiện đơn hàng.</p></div></div>
    <section class="admin-panel" style="max-width:820px">
        <div class="admin-panel-body">
            <form method="post" action="{{ $voucher->exists ? route('admin.vouchers.update', $voucher) : route('admin.vouchers.store') }}">
                @csrf
                @if ($voucher->exists) @method('PUT') @endif
                <div class="admin-form-grid">
                    <div class="admin-field"><label for="voucherCode">Mã giảm giá</label><input id="voucherCode" name="code" value="{{ old('code', $voucher->code) }}" maxlength="40" placeholder="LINHKIEN10" required><small>Chỉ dùng chữ in hoa, số, dấu gạch ngang hoặc gạch dưới.</small></div>
                    <div class="admin-field"><label for="discountType">Loại giảm</label><select id="discountType" name="discount_type" required><option value="percent" @selected(old('discount_type', $voucher->discount_type ?: 'percent') === 'percent')>Theo phần trăm</option><option value="fixed" @selected(old('discount_type', $voucher->discount_type) === 'fixed')>Số tiền cố định</option></select></div>
                    <div class="admin-field"><label for="discountValue">Giá trị giảm</label><input id="discountValue" type="number" name="discount_value" value="{{ old('discount_value', $voucher->discount_value) }}" min="0.01" step="0.01" required></div>
                    <div class="admin-field"><label for="maxDiscount">Mức giảm tối đa (chỉ dùng với %)</label><input id="maxDiscount" type="number" name="max_discount" value="{{ old('max_discount', $voucher->max_discount) }}" min="0.01" step="0.01" placeholder="Để trống nếu không giới hạn"></div>
                    <div class="admin-field"><label for="minimumOrder">Giá trị đơn tối thiểu</label><input id="minimumOrder" type="number" name="minimum_order" value="{{ old('minimum_order', $voucher->minimum_order ?? 0) }}" min="0" step="1000" required></div>
                    <div class="admin-field"><label for="usageLimit">Tổng lượt sử dụng</label><input id="usageLimit" type="number" name="usage_limit" value="{{ old('usage_limit', $voucher->usage_limit) }}" min="1" step="1" placeholder="Để trống nếu không giới hạn"></div>
                    <div class="admin-field"><label for="startsAt">Bắt đầu</label><input id="startsAt" type="datetime-local" name="starts_at" value="{{ old('starts_at', $voucher->starts_at?->format('Y-m-d\TH:i')) }}"></div>
                    <div class="admin-field"><label for="endsAt">Kết thúc</label><input id="endsAt" type="datetime-local" name="ends_at" value="{{ old('ends_at', $voucher->ends_at?->format('Y-m-d\TH:i')) }}"></div>
                    <div class="admin-field admin-field-full"><label class="admin-check-row"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $voucher->is_active ?? true))> Bật mã để khách hàng có thể áp dụng</label></div>
                </div>
                <div style="display:flex;gap:8px;margin-top:22px"><button class="admin-button" type="submit">{{ $voucher->exists ? 'Lưu thay đổi' : 'Tạo mã' }}</button><a class="admin-button-secondary" href="{{ route('admin.vouchers.index') }}">Hủy</a></div>
            </form>
        </div>
    </section>
@endsection
