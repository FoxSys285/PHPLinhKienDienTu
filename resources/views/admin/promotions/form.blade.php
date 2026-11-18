@extends('layouts.admin')
@section('title', $promotion->exists ? 'Chỉnh sửa ưu đãi' : 'Thêm chương trình ưu đãi')
@section('content')
<div class="mx-auto max-w-6xl">
    <a href="{{ route('admin.promotions.index') }}" class="text-xs font-bold text-slate-500 hover:text-blue-600">← Danh sách ưu đãi</a>
    <div class="mb-7 mt-4"><p class="text-xs font-bold uppercase tracking-[.18em] text-blue-600">Chương trình khuyến mãi</p><h1 class="mt-2 text-3xl font-extrabold tracking-tight">{{ $promotion->exists ? 'Chỉnh sửa chương trình' : 'Tạo ưu đãi mới' }}</h1><p class="mt-2 text-sm text-slate-500">Nội dung và điều kiện bên dưới được dùng chung cho cửa hàng và thanh toán.</p></div>
    <form action="{{ $promotion->exists ? route('admin.promotions.update', $promotion) : route('admin.promotions.store') }}" method="POST" enctype="multipart/form-data" class="grid items-start gap-6 lg:grid-cols-[1fr_340px]">
        @csrf
        @if ($promotion->exists) @method('PUT') @endif
        <div class="grid gap-5">
            <section class="rounded-2xl border border-white/80 bg-white/80 p-5 shadow-sm sm:p-6">
                <h2 class="text-base font-extrabold">Thông tin chương trình</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-2 text-xs font-bold text-slate-600 sm:col-span-2">Tên chương trình *<input name="title" value="{{ old('title', $promotion->title) }}" maxlength="150" required class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal" placeholder="Ví dụ: Bắt đầu dự án, nhẹ chi phí"></label>
                    <label class="grid gap-2 text-xs font-bold text-slate-600">Mã ưu đãi *<input name="code" value="{{ old('code', $promotion->code) }}" maxlength="60" required class="rounded-xl border-slate-200 px-3 py-3 font-mono text-sm font-normal uppercase" placeholder="MAKER10"></label>
                    <label class="grid gap-2 text-xs font-bold text-slate-600">Nhãn trên ảnh *<input name="eyebrow" value="{{ old('eyebrow', $promotion->eyebrow) }}" maxlength="80" required class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal" placeholder="Dành cho Maker"></label>
                    <label class="grid gap-2 text-xs font-bold text-slate-600 sm:col-span-2">Đường dẫn chương trình<input name="slug" value="{{ old('slug', $promotion->slug) }}" maxlength="180" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal" placeholder="Để trống để tạo từ tên chương trình"><span class="text-[11px] font-normal text-slate-400">Chữ thường không dấu, chữ số và dấu gạch nối. Nên giữ đường dẫn sau khi đã chia sẻ.</span></label>
                    <label class="grid gap-2 text-xs font-bold text-slate-600 sm:col-span-2">Mô tả ngắn *<textarea name="description" rows="3" maxlength="1000" required class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal">{{ old('description', $promotion->description) }}</textarea></label>
                    <label class="grid gap-2 text-xs font-bold text-slate-600 sm:col-span-2">Ghi chú điều kiện bổ sung<textarea name="terms" rows="3" maxlength="5000" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal" placeholder="Điều kiện chính được hiển thị tự động từ cài đặt bên dưới.">{{ old('terms', $promotion->terms) }}</textarea></label>
                </div>
            </section>
            <section class="rounded-2xl border border-white/80 bg-white/80 p-5 shadow-sm sm:p-6">
                <h2 class="text-base font-extrabold">Mức giảm & phạm vi áp dụng</h2>
                <p class="mt-2 text-xs leading-5 text-slate-500">Mức mua tối thiểu và tiền giảm được tính trên các sản phẩm thuộc danh mục áp dụng, trước phí giao hàng và VAT.</p>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-2 text-xs font-bold text-slate-600">Hình thức giảm *<select name="type" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"><option value="percentage" @selected(old('type', $promotion->type) === 'percentage')>Theo phần trăm (%)</option><option value="fixed" @selected(old('type', $promotion->type) === 'fixed')>Số tiền cố định (₫)</option></select></label>
                    <label class="grid gap-2 text-xs font-bold text-slate-600">Mức giảm *<input type="number" name="discount_value" min="0.01" step="0.01" value="{{ old('discount_value', $promotion->discount_value) }}" required class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"><span class="text-[11px] font-normal text-slate-400">Giảm theo % không vượt quá 100.</span></label>
                    <label class="grid gap-2 text-xs font-bold text-slate-600">Giá trị mua tối thiểu (₫) *<input type="number" name="minimum_purchase" min="0" step="1" value="{{ old('minimum_purchase', $promotion->minimum_purchase) }}" required class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"></label>
                    <label class="grid gap-2 text-xs font-bold text-slate-600">Giảm tối đa (₫)<input type="number" name="maximum_discount" min="1" step="1" value="{{ old('maximum_discount', $promotion->maximum_discount) }}" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal" placeholder="Để trống nếu không giới hạn"></label>
                </div>
                <fieldset class="mt-5"><legend class="text-xs font-bold text-slate-600">Danh mục áp dụng</legend><p class="mt-1 text-[11px] text-slate-400">Không chọn danh mục: áp dụng toàn cửa hàng.</p><div class="mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach ($categories as $category)
                        <label class="flex items-center gap-2 rounded-xl border border-slate-100 bg-white p-3 text-xs text-slate-600"><input type="checkbox" name="category_slugs[]" value="{{ $category->slug }}" @checked(in_array($category->slug, old('category_slugs', $promotion->category_slugs ?? []), true)) class="rounded border-slate-300 text-blue-600">{{ $category->name }}</label>
                    @endforeach
                </div></fieldset>
            </section>
            <section class="rounded-2xl border border-white/80 bg-white/80 p-5 shadow-sm sm:p-6">
                <h2 class="text-base font-extrabold">Thời gian & giới hạn</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-2 text-xs font-bold text-slate-600">Ngày bắt đầu<input type="datetime-local" name="starts_at" value="{{ old('starts_at', $promotion->starts_at?->format('Y-m-d\TH:i')) }}" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"></label>
                    <label class="grid gap-2 text-xs font-bold text-slate-600">Ngày kết thúc<input type="datetime-local" name="expires_at" value="{{ old('expires_at', $promotion->expires_at?->format('Y-m-d\TH:i')) }}" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal"></label>
                    <label class="grid gap-2 text-xs font-bold text-slate-600">Tổng lượt sử dụng tối đa<input type="number" name="usage_limit" min="1" value="{{ old('usage_limit', $promotion->usage_limit) }}" class="rounded-xl border-slate-200 px-3 py-3 text-sm font-normal" placeholder="Để trống nếu không giới hạn"></label>
                    <div class="rounded-xl bg-blue-50 p-4 text-xs leading-6 text-blue-800"><strong>{{ $promotion->used_count ?? 0 }} lượt</strong> đã dùng / giữ chỗ thanh toán. Lượt được trả lại khi liên kết thanh toán hết hạn hoặc bị hủy.</div>
                </div>
                <p class="mt-3 text-[11px] leading-5 text-slate-400">Để trống thời gian để không giới hạn. Thời gian theo múi giờ {{ config('app.timezone') }}.</p>
            </section>
        </div>
        <div class="grid gap-5">
            <section class="overflow-hidden rounded-2xl border border-white/80 bg-white/80 shadow-sm">
                <img data-promotion-image-preview src="{{ $promotion->image_url }}" alt="Ảnh xem trước chương trình" class="aspect-[3/2] w-full object-cover">
                <div class="p-5"><h2 class="text-sm font-extrabold">Hình ảnh chương trình</h2>
                    <label class="mt-4 grid gap-2 text-xs font-bold text-slate-600">Tải ảnh riêng<input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-promotion-image-upload class="w-full rounded-lg border border-slate-200 bg-white p-2 text-xs font-normal"><span class="text-[11px] font-normal text-slate-400">JPG, PNG, WebP · Tối đa 4 MB · Nên dùng ảnh ngang 3:2.</span></label>
                    <fieldset class="mt-4"><legend class="text-xs font-bold text-slate-600">Hoặc chọn ảnh có sẵn</legend><div class="mt-3 grid grid-cols-2 gap-2">
                        @foreach ($imagePresets as $key => $label)
                            @php($presetUrl = asset(match ($key) { 'tools' => 'images/home-promotion-tools.webp', 'chips' => 'images/home-hero-cpu.webp', default => 'images/promotions/'.$key.'.png' }))
                            <label class="cursor-pointer overflow-hidden rounded-xl border border-slate-200 bg-white has-[:checked]:border-blue-500 has-[:checked]:ring-2 has-[:checked]:ring-blue-100">
                                <img src="{{ $presetUrl }}" alt="" loading="lazy" class="aspect-[3/2] w-full object-cover">
                                <span class="flex items-center gap-1.5 p-2 text-[10px] font-semibold text-slate-600"><input type="radio" name="image_preset" value="{{ $key }}" data-promotion-image-choice data-image-url="{{ $presetUrl }}" @checked(old('image_preset') === $key) class="size-3 text-blue-600">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div></fieldset>
                    <label class="mt-3 flex items-center gap-2 text-[11px] text-slate-500"><input type="radio" name="image_preset" value="" data-promotion-image-choice data-image-url="{{ $promotion->image_url }}" @checked(! old('image_preset'))>Giữ ảnh hiện tại</label>
                </div>
            </section>
            <section class="rounded-2xl border border-white/80 bg-white/80 p-5 shadow-sm">
                <h2 class="text-sm font-extrabold">Hiển thị & xuất bản</h2>
                <label class="mt-4 grid gap-2 text-xs font-bold text-slate-600">Màu nhấn<select name="theme" class="rounded-xl border-slate-200 px-3 py-2.5 text-sm font-normal">@foreach (['blue' => 'Xanh dương', 'cyan' => 'Xanh ngọc', 'violet' => 'Tím', 'amber' => 'Cam'] as $key => $label)<option value="{{ $key }}" @selected(old('theme', $promotion->theme) === $key)>{{ $label }}</option>@endforeach</select></label>
                <label class="mt-4 grid gap-2 text-xs font-bold text-slate-600">Thứ tự hiển thị<input type="number" name="sort_order" min="0" max="99999" value="{{ old('sort_order', $promotion->sort_order) }}" required class="rounded-xl border-slate-200 px-3 py-2.5 text-sm font-normal"><span class="text-[11px] font-normal text-slate-400">Số nhỏ xuất hiện trước.</span></label>
                <label class="mt-5 flex items-start gap-3 text-xs text-slate-600"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $promotion->is_active)) class="mt-0.5 rounded border-slate-300 text-blue-600"><span><strong class="block">Kích hoạt chương trình</strong><span class="mt-1 block leading-5 text-slate-400">Chỉ hiển thị khi đang trong thời hạn và còn lượt.</span></span></label>
                <label class="mt-4 flex items-start gap-3 text-xs text-slate-600"><input type="checkbox" name="is_automatic" value="1" @checked(old('is_automatic', $promotion->is_automatic)) class="mt-0.5 rounded border-slate-300 text-blue-600"><span><strong class="block">Tự động áp dụng khi thanh toán</strong><span class="mt-1 block leading-5 text-slate-400">Tắt để khách cần nhập mã. Ưu đãi không cộng dồn.</span></span></label>
                <button class="mt-6 w-full rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 hover:bg-blue-700">{{ $promotion->exists ? 'Lưu thay đổi' : 'Tạo chương trình' }}</button>
                <a href="{{ route('admin.promotions.index') }}" class="mt-3 block py-2 text-center text-xs font-semibold text-slate-500">Quay lại danh sách</a>
            </section>
        </div>
    </form>
</div>
@endsection
