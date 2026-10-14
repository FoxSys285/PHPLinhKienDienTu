<div>
    <!-- Do what you can, with what you have, where you are. - Theodore Roosevelt -->
</div>
@extends('layouts.user')

@section('title', 'Trang chủ')

@section('content')
    <section class="hero-banner">
        <div class="hero-content">
            <span class="hero-kicker">DÀNH CHO NGƯỜI YÊU CÔNG NGHỆ</span>
            <h1>Ý tưởng của bạn,<br><em>bắt đầu từ đây.</em></h1>
            <p>Linh kiện chất lượng, dụng cụ tin cậy và cảm hứng cho dự án tiếp theo của bạn.</p>
            <a class="hero-cta" href="{{ route('products.index') }}">Khám phá sản phẩm <span>→</span></a>
            <div class="hero-dots" aria-hidden="true"><i></i><i></i><i></i></div>
        </div>
    </section>

    <section class="benefit-row" aria-label="Lợi ích mua sắm">
        <div class="benefit-item"><span class="benefit-icon" aria-hidden="true">↗</span><span><strong>Giao hàng toàn quốc</strong><small>Đóng gói cẩn thận, giao nhanh</small></span></div>
        <div class="benefit-item"><span class="benefit-icon" aria-hidden="true">✓</span><span><strong>Linh kiện chính hãng</strong><small>Kiểm tra kỹ trước khi gửi</small></span></div>
        <div class="benefit-item"><span class="benefit-icon" aria-hidden="true">⌕</span><span><strong>Tư vấn kỹ thuật miễn phí</strong><small>Cùng bạn hoàn thiện dự án</small></span></div>
    </section>

    <section class="product-section">
        <div class="section-heading">
            <div><span class="eyebrow">LỰA CHỌN CỦA CỘNG ĐỒNG</span><h2>Sản phẩm nổi bật</h2><p>Những món linh kiện được các maker yêu thích nhất.</p></div>
            <a class="text-link" href="{{ route('products.index') }}">Xem tất cả <span>→</span></a>
        </div>
        <div class="product-grid">
            @forelse ($featuredProducts ?? [] as $product)
                @php($productId = data_get($product, 'id'))
                <article class="product-card">
                    <a class="product-image-wrap" href="{{ route('products.show', $productId) }}">
                        <img src="{{ data_get($product, 'image') }}" alt="{{ data_get($product, 'name') }}" loading="lazy">
                        <span class="product-badge">{{ data_get($product, 'badge') }}</span>
                    </a>
                    <div class="product-info">
                        <a class="product-title" href="{{ route('products.show', $productId) }}">{{ data_get($product, 'name') }}</a>
                        <div class="product-rating"><span class="rating-stars" aria-label="5 sao">★★★★★</span><span>{{ data_get($product, 'rating', 0) }}</span><span>·</span><span>Đã bán {{ data_get($product, 'sold_count', 0) }}</span></div>
                        <div class="product-price-row"><span class="product-price">{{ data_get($product, 'formatted_price', number_format((float) data_get($product, 'price', 0), 0, ',', '.') . '₫') }}</span><span class="product-old-price">{{ data_get($product, 'formatted_old_price') }}</span></div>
                        <a class="product-card-link" href="{{ route('products.show', $productId) }}">Xem chi tiết <span>→</span></a>
                    </div>
                </article>
            @empty
                <p class="empty-state">Sản phẩm nổi bật sẽ được hiển thị tại đây.</p>
            @endforelse
        </div>
    </section>

    <section class="product-section">
        <div class="section-heading">
            <div><span class="eyebrow">TÌM NHANH THEO NHU CẦU</span><h2>Danh mục được quan tâm</h2></div>
            <a class="text-link" href="{{ route('products.index') }}">Tất cả danh mục <span>→</span></a>
        </div>
        <div class="category-tiles">
            @forelse ($categories ?? [] as $category)
                <a class="category-tile" href="{{ route('products.index', ['category' => data_get($category, 'filter', data_get($category, 'slug'))]) }}">
                    <span class="category-tile-icon" aria-hidden="true">{{ data_get($category, 'icon', '▦') }}</span>
                    <span><strong>{{ data_get($category, 'name', 'Danh mục') }}</strong><small>{{ data_get($category, 'product_count', 0) }} sản phẩm <span>→</span></small></span>
                </a>
            @empty
                <p class="empty-state">Danh mục sản phẩm sẽ được hiển thị tại đây.</p>
            @endforelse
        </div>
    </section>
@endsection
