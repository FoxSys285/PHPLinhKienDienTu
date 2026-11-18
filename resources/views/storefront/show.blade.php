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
            <ul class="mt-3 grid gap-3 text-sm leading-6 text-slate-600"><li>Đối chiếu mã linh kiện trước khi đóng gói.</li><li>Linh kiện nhạy ESD được đóng gói chống tĩnh điện.</li><li>Liên hệ cửa hàng nếu cần hỗ trợ kiểm tra tình trạng hàng.</li></ul>
            <div class="mt-4 flex flex-wrap gap-4"><a href="{{ route('pages.shipping') }}" class="inline-block text-sm font-semibold text-emerald-900 hover:underline">Xem giao hàng và thanh toán</a><a href="{{ route('pages.contact') }}" class="inline-block text-sm font-semibold text-emerald-900 hover:underline">Liên hệ cửa hàng</a></div>
        </section>
    </div>

    @if($relatedProducts->isNotEmpty())
        <section class="mt-10">
            <div class="flex items-end justify-between gap-3"><div><p class="text-sm text-slate-500">Cùng danh mục</p><h2 class="mt-1 text-xl font-bold text-slate-900">Sản phẩm liên quan</h2></div>@if($product->category)<a href="{{ route('categories.show', $product->category) }}" class="text-sm font-semibold text-emerald-900 hover:underline">Xem danh mục</a>@endif</div>
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">@foreach($relatedProducts as $related)@include('partials.product-card', ['product' => $related])@endforeach</div>
        </section>
    @endif

    <section class="mt-10 grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
        <div class="product-detail-card rounded-2xl border border-white/80 bg-white/90 p-5 shadow-xl shadow-blue-950/5 backdrop-blur sm:p-6">
            <div class="flex items-end justify-between gap-3 border-b border-slate-100 pb-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.16em] text-blue-700">Cảm nhận khách hàng</p>
                    <h2 class="mt-1 text-lg font-semibold text-slate-900">Đánh giá sản phẩm</h2>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-800">Đã duyệt</span>
            </div>
            @forelse($reviews as $review)
                <article class="border-b border-slate-100 py-4 last:border-0">
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-semibold text-slate-800">{{ $review->user?->name ?? 'Khách hàng' }}</p>
                        <span class="flex text-lg leading-none" aria-label="Đánh giá {{ number_format((float) $review->rating, 1) }} trên 5 sao">
                            @for ($star = 1; $star <= 5; $star++)
                                @php($starFill = max(0, min(1, (float) $review->rating - $star + 1)) * 100)
                                <span class="relative inline-block text-slate-300" aria-hidden="true">★<span class="absolute inset-y-0 left-0 overflow-hidden text-amber-500" style="width: {{ $starFill }}%">★</span></span>
                            @endfor
                        </span>
                    </div>
                    @if ($review->body)
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $review->body }}</p>
                    @endif
                    @if ($review->images || $review->video_url)
                        <div class="mt-3 flex flex-wrap items-start gap-3">
                            @foreach ((array) $review->images as $imageUrl)
                                <a href="{{ $imageUrl }}" target="_blank" rel="noreferrer">
                                    <img src="{{ $imageUrl }}" alt="Ảnh trong đánh giá" loading="lazy" class="size-28 rounded-lg border border-slate-200 object-cover">
                                </a>
                            @endforeach
                            @if ($review->video_url)
                                <video src="{{ $review->video_url }}" controls playsinline preload="metadata" class="max-h-64 max-w-full rounded-lg bg-black"></video>
                            @endif
                        </div>
                    @endif
                </article>
            @empty
                <p class="mt-2 text-sm text-slate-500">Chưa có đánh giá được duyệt.</p>
            @endforelse
        </div>
        <div class="product-detail-card rounded-2xl border border-white/80 bg-white/90 p-5 shadow-xl shadow-blue-950/5 backdrop-blur sm:p-6">
            @auth
                <form action="{{ route('reviews.store', $product) }}" method="POST" enctype="multipart/form-data" data-review-form class="grid gap-4">
                    @csrf
                    <div class="rounded-2xl bg-gradient-to-br from-blue-50 via-white to-indigo-50 p-5 ring-1 ring-blue-100/70">
                        <div class="flex items-start gap-3">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-lg text-white shadow-md shadow-blue-600/20" aria-hidden="true">✦</span>
                            <div>
                                <h2 class="font-bold text-slate-900">Gửi đánh giá</h2>
                                <p class="mt-1 text-xs leading-5 text-slate-600">Chia sẻ trải nghiệm thật để giúp khách hàng khác chọn sản phẩm phù hợp.</p>
                            </div>
                        </div>
                        <fieldset class="mt-5">
                            <legend class="text-xs font-semibold text-slate-600">Chọn số sao</legend>
                            <div class="review-rating mt-2 flex flex-row-reverse justify-end" role="radiogroup" aria-label="Chọn mức đánh giá từ 1 đến 5 sao, mỗi nửa sao">
                                @foreach ([5, 4.5, 4, 3.5, 3, 2.5, 2, 1.5, 1] as $rating)
                                    <input id="review-rating-{{ str_replace('.', '-', (string) $rating) }}" type="radio" name="rating" value="{{ $rating }}" required @checked((float) old('rating', 0) === (float) $rating) class="peer sr-only">
                                    <label for="review-rating-{{ str_replace('.', '-', (string) $rating) }}" aria-label="{{ $rating }} sao" class="review-rating__segment {{ $rating === 1 ? 'review-rating__segment--full' : ($rating == floor($rating) ? 'review-rating__segment--right' : 'review-rating__segment--left') }} peer-checked:text-amber-400"><span aria-hidden="true">★</span></label>
                                @endforeach
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Từ 1 đến 5 sao · Chọn được nửa sao</p>
                        </fieldset>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="grid content-start gap-2 rounded-xl border border-dashed border-slate-300 bg-white p-3 text-xs font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-blue-400 hover:shadow-md">
                            <label for="review-images-input" class="flex cursor-pointer items-center gap-2"><span class="grid size-8 place-items-center rounded-lg bg-blue-50 text-base text-blue-700" aria-hidden="true">▧</span>Thêm hình ảnh</label>
                            <input id="review-images-input" type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" data-review-images-input class="w-full text-xs font-normal file:mr-2 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:font-semibold file:text-blue-800">
                            <span class="text-[11px] font-normal text-slate-500">Tối đa 5 ảnh · JPG, PNG, WebP · 4 MB/ảnh</span>
                            <span data-review-images-feedback aria-live="polite" class="hidden text-[11px] font-semibold text-blue-700"></span>
                            <div data-review-images-preview class="hidden grid grid-cols-5 gap-1.5"></div>
                        </div>
                        <div class="grid content-start gap-2 rounded-xl border border-dashed border-slate-300 bg-white p-3 text-xs font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-blue-400 hover:shadow-md">
                            <label for="review-video-input" class="flex cursor-pointer items-center gap-2"><span class="grid size-8 place-items-center rounded-lg bg-indigo-50 text-base text-indigo-700" aria-hidden="true">▷</span>Thêm video</label>
                            <input id="review-video-input" type="file" name="video" accept="video/mp4,video/quicktime,video/webm" data-review-video-input class="w-full text-xs font-normal file:mr-2 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:font-semibold file:text-indigo-800">
                            <span class="text-[11px] font-normal text-slate-500">Tối đa 1 video · MP4, MOV, WebM · 50 MB</span>
                            <video data-review-video-preview controls playsinline preload="metadata" class="hidden mt-1 max-h-36 w-full rounded-lg bg-black"></video>
                        </div>
                    </div>
                    <textarea name="body" rows="5" maxlength="2000" placeholder="Chia sẻ trải nghiệm sử dụng" class="rounded-xl border-slate-200 bg-slate-50/70 px-4 py-3 text-sm placeholder:text-slate-400 focus:bg-white">{{ old('body') }}</textarea>
                    @error('images')<p class="text-xs text-rose-700">{{ $message }}</p>@enderror
                    @error('video')<p class="text-xs text-rose-700">{{ $message }}</p>@enderror
                    <button class="w-fit rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:shadow-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700">Gửi đánh giá</button>
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
