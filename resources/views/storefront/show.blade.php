@extends('layouts.store')

@section('title', $product->name.' · '.$product->sku)

@push('meta')
<meta name="description" content="{{ Str::limit(strip_tags($product->description), 155) }}">
<meta property="og:image" content="{{ $product->image_url }}">
<script type="application/ld+json">{!! Illuminate\Support\Js::from(['@context' => 'https://schema.org', '@type' => 'Product', 'name' => $product->name, 'image' => $product->image_url, 'sku' => $product->sku, 'mpn' => $product->mpn, 'brand' => $product->brand ? ['@type' => 'Brand', 'name' => $product->brand] : null, 'description' => $product->description, 'offers' => ['@type' => 'Offer', 'priceCurrency' => 'VND', 'price' => $displayPrice, 'availability' => $product->availableStock() > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock']]) !!}</script>
@endpush

@section('content')
@php($available = $product->availableStock())
<div class="product-detail mx-auto max-w-7xl px-4 py-7 sm:py-9">
    <nav aria-label="Đường dẫn" class="mb-5 flex flex-wrap items-center gap-2 text-xs text-slate-500">
        <a href="{{ route('home') }}" class="hover:text-emerald-900">Trang chủ</a><span>/</span>
        <a href="{{ route('products.index') }}" class="hover:text-emerald-900">Sản phẩm</a>
        @if($product->category)<span>/</span><a href="{{ route('categories.show', $product->category) }}" class="hover:text-emerald-900">{{ $product->category->name }}</a>@endif
        <span>/</span><span class="font-mono text-slate-700">{{ $product->sku }}</span>
    </nav>

    <div class="grid gap-7 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:gap-10">
        <div class="product-image-panel relative grid min-h-[320px] place-items-center overflow-hidden rounded-lg border border-slate-200 bg-white sm:min-h-[440px]">
            @if($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" data-product-image decoding="async" fetchpriority="high" class="size-full max-h-[500px] object-contain p-8 sm:p-12">
                <div data-product-image-fallback hidden class="text-sm text-slate-500">Ảnh sản phẩm tạm thời không tải được.</div>
            @else
                <div class="text-sm text-slate-500">Chưa có ảnh sản phẩm.</div>
            @endif
        </div>

        <div class="product-info-panel flex flex-col">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $product->brand ?: 'Linh kiện điện tử' }}@if($product->mpn)<span class="px-1">·</span>MPN {{ $product->mpn }}@endif</p>
            <h1 class="mt-2 text-2xl font-bold leading-tight tracking-tight text-slate-900 sm:text-3xl">{{ $product->name }}</h1>
            <p class="mt-2 font-mono text-xs text-slate-500">SKU: {{ $product->sku }}</p>

            <div class="product-price-row mt-5 flex flex-wrap items-baseline gap-x-3 gap-y-1 border-y border-slate-200 py-4">
                <span data-product-price class="text-2xl font-bold text-slate-900">{{ number_format((float) $displayPrice, 0, ',', '.') }}₫</span>
                @if($product->compare_at_price)<span class="text-sm text-slate-400 line-through">{{ number_format((float) $product->compare_at_price, 0, ',', '.') }}₫</span>@endif
                <span class="text-xs text-slate-500">Giá chưa gồm phí giao hàng</span>
            </div>

            @if($product->description)<p class="mt-4 text-sm leading-6 text-slate-600">{{ $product->description }}</p>@endif

            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                <span class="font-medium {{ $available > 0 ? 'text-emerald-800' : 'text-slate-500' }}">{{ $available > 0 ? 'Còn hàng · '.$available.'+' : ($product->allow_backorder ? 'Có thể đặt trước' : 'Tạm hết hàng') }}</span>
                <span class="text-slate-500">MOQ {{ $product->moq }} · Bội số {{ $product->pack_size }}</span>
                @if($product->is_rohs)<span class="text-slate-500">RoHS</span>@endif
                @if($product->is_esd_sensitive)<span class="text-slate-500">Đóng gói chống tĩnh điện</span>@endif
            </div>

            @if($product->priceTiers->isNotEmpty())
                <div class="product-tier-panel mt-5 overflow-hidden rounded-lg border border-slate-200">
                    <div class="border-b border-slate-200 bg-slate-50 px-4 py-3"><h2 class="text-sm font-semibold text-slate-800">Giá theo số lượng</h2></div>
                    <table class="w-full text-left text-xs">
                        <thead class="text-slate-500"><tr><th class="px-4 py-2.5 font-medium">Số lượng</th><th class="px-4 py-2.5 text-right font-medium">Đơn giá</th><th class="px-4 py-2.5 text-right font-medium">Giảm</th></tr></thead>
                        <tbody>@foreach($product->priceTiers as $tier)<tr class="border-t border-slate-100"><td class="px-4 py-2.5">{{ $tier->minimum_quantity }}{{ $tier->maximum_quantity ? '–'.$tier->maximum_quantity : '+' }}</td><td class="px-4 py-2.5 text-right font-semibold">{{ number_format((float) $tier->unit_price, 0, ',', '.') }}₫</td><td class="px-4 py-2.5 text-right text-slate-500">{{ $tier->unit_price < $product->price ? number_format(round((1 - (float) $tier->unit_price / (float) $product->price) * 100), 0).'%' : '—' }}</td></tr>@endforeach</tbody>
                    </table>
                </div>
            @endif

            <form action="{{ route('cart.store', $product) }}" method="POST" class="product-buy-form mt-5 flex flex-wrap items-end gap-3" data-tier-prices="{{ $product->priceTiers->map(fn ($tier) => ['min' => $tier->minimum_quantity, 'max' => $tier->maximum_quantity, 'price' => (float) $tier->unit_price])->toJson() }}" data-base-price="{{ (float) $displayPrice }}">
                @csrf
                <label class="grid gap-1.5 text-xs font-semibold text-slate-600">Số lượng<input type="number" name="quantity" value="{{ $product->moq }}" min="{{ $product->moq }}" step="{{ $product->pack_size }}" data-quantity-input class="w-32 rounded-md border border-slate-300 px-3 py-2.5 text-sm font-semibold text-slate-900 focus:border-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-100"></label>
                <button @disabled($available === 0 && ! $product->allow_backorder) class="min-h-[42px] flex-1 rounded-md bg-emerald-950 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:bg-slate-300">{{ $available === 0 && ! $product->allow_backorder ? 'Tạm hết hàng' : 'Thêm vào giỏ' }}</button>
            </form>
            @if($product->allow_backorder && $product->backorder_eta)<p class="mt-2 text-xs text-slate-500">Dự kiến có hàng: {{ $product->backorder_eta->format('d/m/Y') }}</p>@endif
            @if($available === 0 && ! $product->allow_backorder)
                <form method="POST" action="{{ route('products.restock', $product) }}" class="mt-4 flex flex-wrap items-end gap-2 rounded-lg bg-slate-50 p-3">
                    @csrf
                    <label class="grid min-w-0 flex-1 gap-1 text-xs font-medium text-slate-600">Email nhận thông báo<input required type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" placeholder="ban@email.com" class="rounded-md border-slate-300 px-3 py-2.5 text-sm font-normal"></label>
                    <button class="rounded-md border border-slate-300 bg-white px-3 py-2.5 text-xs font-semibold text-slate-800 hover:border-emerald-800">Báo khi có hàng</button>
                </form>
            @endif
            @if($product->datasheet_url)<a href="{{ $product->datasheet_url }}" target="_blank" rel="noopener noreferrer" class="mt-4 w-fit text-sm font-semibold text-emerald-900 hover:underline">Mở datasheet PDF <span aria-hidden="true">↗</span></a>@endif
        </div>
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-[1.2fr_.8fr]">
        <section class="product-detail-card rounded-lg border border-slate-200 bg-white p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-slate-900">Thông số kỹ thuật</h2>
            <div class="mt-3 divide-y divide-slate-100">@forelse((array) $product->specifications as $key => $value)<div class="grid grid-cols-[minmax(120px,.7fr)_1.3fr] gap-4 py-3 text-sm"><span class="text-slate-500">{{ $key }}</span><span class="font-medium text-slate-900">{{ $value }}</span></div>@empty<p class="py-4 text-sm text-slate-500">Thông số đang được cập nhật.</p>@endforelse</div>
        </section>
        <section class="product-detail-card rounded-lg border border-slate-200 bg-white p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-slate-900">Thông tin đặt hàng</h2>
            <ul class="mt-3 grid gap-3 text-sm leading-6 text-slate-600"><li>Đối chiếu mã linh kiện trước khi đóng gói.</li><li>Linh kiện nhạy ESD được đóng gói chống tĩnh điện.</li><li>Liên hệ cửa hàng để được hỗ trợ với đơn hàng số lượng lớn.</li></ul>
            <div class="mt-4 flex flex-wrap gap-4"><a href="{{ route('pages.shipping') }}" class="inline-block text-sm font-semibold text-emerald-900 hover:underline">Xem giao hàng và thanh toán</a><a href="{{ route('pages.contact') }}" class="inline-block text-sm font-semibold text-emerald-900 hover:underline">Liên hệ cửa hàng</a></div>
        </section>
    </div>

    @if($relatedProducts->isNotEmpty())
        <section class="mt-10">
            <div class="flex items-end justify-between gap-3"><div><p class="text-sm text-slate-500">Cùng danh mục</p><h2 class="mt-1 text-xl font-bold text-slate-900">Sản phẩm liên quan</h2></div>@if($product->category)<a href="{{ route('categories.show', $product->category) }}" class="text-sm font-semibold text-emerald-900 hover:underline">Xem danh mục</a>@endif</div>
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">@foreach($relatedProducts as $related)@include('partials.product-card', ['product' => $related])@endforeach</div>
        </section>
    @endif

    <section class="mt-10 grid gap-5 lg:grid-cols-2">
        <div class="product-detail-card rounded-lg border border-slate-200 bg-white p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-slate-900">Đánh giá sản phẩm</h2>
            @forelse($reviews as $review)<article class="border-b border-slate-100 py-4 last:border-0"><div class="flex items-center justify-between gap-3"><p class="font-semibold text-slate-800">{{ $review->user?->name ?? 'Khách hàng' }}</p><span class="text-amber-500" aria-label="{{ $review->rating }} trên 5 sao">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span></div><p class="mt-1 text-sm font-medium">{{ $review->title }}</p><p class="mt-1 text-sm leading-6 text-slate-600">{{ $review->body }}</p></article>@empty<p class="mt-2 text-sm text-slate-500">Chưa có đánh giá được duyệt.</p>@endforelse
        </div>
        <div class="product-detail-card rounded-lg border border-slate-200 bg-white p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-slate-900">Gửi đánh giá</h2>
            @auth
                <form action="{{ route('reviews.store', $product) }}" method="POST" class="mt-4 grid gap-3">
                    @csrf
                    <label class="grid gap-1 text-xs font-semibold text-slate-600">Số sao<select name="rating" class="rounded-md border-slate-300 text-sm"><option value="5">5 — Rất tốt</option><option value="4">4 — Tốt</option><option value="3">3 — Bình thường</option><option value="2">2 — Chưa tốt</option><option value="1">1 — Không hài lòng</option></select></label>
                    <input name="title" placeholder="Tiêu đề đánh giá" class="rounded-md border-slate-300 text-sm">
                    <textarea name="body" rows="3" placeholder="Chia sẻ trải nghiệm sử dụng" class="rounded-md border-slate-300 text-sm"></textarea>
                    <button class="w-fit rounded-md bg-emerald-950 px-4 py-2.5 text-sm font-semibold text-white">Gửi đánh giá</button>
                    <p class="text-xs text-slate-500">Chỉ khách đã mua và hoàn tất đơn mới được đánh giá. Nội dung cần được duyệt trước khi hiển thị.</p>
                </form>
            @else
                <p class="mt-2 text-sm text-slate-500">Đăng nhập bằng tài khoản đã mua sản phẩm để gửi đánh giá.</p>
                <a href="{{ route('login') }}" class="mt-3 inline-flex rounded-md border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-800">Đăng nhập</a>
            @endauth
        </div>
    </section>
</div>
@endsection
