<div>
    <!-- He who is contented is rich. - Laozi -->
</div>
@extends('layouts.user')

@php
    $galleryImages = $galleryImages ?? [];
    $specifications = $specifications ?? [];
    $features = $features ?? [];
    $reviews = $reviews ?? [];
    $ratingDistribution = $ratingDistribution ?? [];
    $relatedProducts = $relatedProducts ?? [];
@endphp

@section('title', data_get($product ?? null, 'name', 'Chi tiết sản phẩm'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/product-detail.css') }}">
@endpush

@section('content')
    @if (! isset($product) || ! $product)
        <section class="search-empty-state">
            <h1>Chưa có thông tin sản phẩm</h1>
            <p>Thông tin chi tiết sẽ hiển thị khi sản phẩm được kết nối với dữ liệu của cửa hàng.</p>
            <a class="button-primary" href="{{ route('products.index') }}">Quay lại danh sách sản phẩm</a>
        </section>
    @else
        @php($productId = data_get($product, 'id'))
        @php($productName = data_get($product, 'name', 'Sản phẩm'))
        <nav class="detail-breadcrumb mb-3" aria-label="Đường dẫn">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index', ['category' => data_get($product, 'category.slug')]) }}">{{ data_get($product, 'category.name', data_get($product, 'category')) }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $productName }}</li>
            </ol>
        </nav>

        <section class="detail-card card">
            <div class="detail-gallery">
                <div class="detail-gallery-main">
                    <img id="mainProductImage" src="{{ data_get($product, 'image') }}" alt="{{ $productName }}">
                    <span class="product-badge">{{ data_get($product, 'badge') }}</span>
                    <span class="gallery-zoom-hint" aria-hidden="true">Ảnh sản phẩm</span>
                </div>
                <div class="gallery-thumbnails" aria-label="Thư viện ảnh sản phẩm">
                    <button class="gallery-thumb active" type="button" aria-label="Xem ảnh chính"><img src="{{ data_get($product, 'image') }}" alt=""></button>
                    @foreach ($galleryImages as $image)
                        <button class="gallery-thumb" type="button" aria-label="Xem ảnh sản phẩm"><img src="{{ $image }}" alt=""></button>
                    @endforeach
                </div>
            </div>
            <div class="detail-info">
                <span class="detail-label">{{ data_get($product, 'category.name', data_get($product, 'category')) }}</span>
                <h1>{{ $productName }}</h1>
                <div class="detail-meta">
                    <a href="#reviews" class="detail-rating-link"><span class="rating-stars" aria-label="{{ data_get($product, 'rating', 0) }} trên 5 sao">★★★★★</span><strong>{{ data_get($product, 'rating', 0) }}</strong><span>({{ $reviewCount ?? count($reviews) }} đánh giá)</span></a>
                    <span class="meta-separator">|</span><span>Đã bán {{ data_get($product, 'sold_count', 0) }}</span><span class="meta-separator">|</span><span>Mã: {{ data_get($product, 'sku', $productId) }}</span>
                </div>
                <div class="detail-price-block">
                    <div>
                        <span class="detail-price">{{ data_get($product, 'formatted_price', number_format((float) data_get($product, 'price', 0), 0, ',', '.') . '₫') }}</span>
                        <span class="detail-old-price">{{ data_get($product, 'formatted_old_price') }}</span>
                    </div>
                    <div class="detail-stock"><i class="stock-dot"></i>{{ data_get($product, 'stock_label', 'Còn hàng') }} <span>·</span> Sẵn sàng giao</div>
                </div>
                <p class="detail-description">{{ data_get($product, 'description') }}</p>
                <div class="detail-specs">
                    @foreach ($features as $feature)
                        <div class="detail-spec"><span aria-hidden="true">✓</span>{{ $feature }}</div>
                    @endforeach
                </div>
                <form action="{{ route('cart.store') }}" method="post" class="purchase-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $productId }}">
                    <label class="purchase-quantity">Số lượng<input class="form-control" id="quantity" name="quantity" type="number" min="1" max="{{ min(99, (int) data_get($product, 'stock', 99)) }}" value="{{ old('quantity', 1) }}"></label>
                    <button class="button-primary btn btn-primary" type="submit" @disabled(data_get($product, 'stock', 0) < 1)><span aria-hidden="true">＋</span> Thêm vào giỏ</button>
                    <button class="button-outline btn btn-outline-primary" type="submit" name="buy_now" value="1" @disabled(data_get($product, 'stock', 0) < 1)>Mua ngay</button>
                </form>
                <div class="product-assurances">
                    <span><strong>↗ Giao hàng nhanh</strong>Toàn quốc 1–3 ngày</span>
                    <span><strong>✓ Bảo hành rõ ràng</strong>Hỗ trợ kỹ thuật</span>
                    <span><strong>♧ Đóng gói an toàn</strong>Chống sốc linh kiện</span>
                </div>
            </div>
        </section>

        <nav class="detail-tabs nav nav-tabs" aria-label="Nội dung sản phẩm">
            <a class="nav-link active" href="#specifications">Thông số kỹ thuật</a><a class="nav-link" href="#description">Mô tả sản phẩm</a><a class="nav-link" href="#reviews">Đánh giá ({{ $reviewCount ?? count($reviews) }})</a>
        </nav>

        <section class="specification-panel card" id="specifications">
            <div class="detail-section-heading">
                <div><span class="eyebrow">THÔNG SỐ</span><h2>Thông tin chi tiết</h2></div>
                <span class="detail-section-caption badge rounded-pill">Mã: {{ data_get($product, 'sku', $productId) }}</span>
            </div>
            <div class="table-responsive">
                <table class="specification-table table table-striped table-hover align-middle mb-0">
                    <tbody>
                        <tr><th scope="row">Danh mục</th><td>{{ data_get($product, 'category.name', data_get($product, 'category')) }}</td></tr>
                        <tr><th scope="row">Tình trạng</th><td>{{ data_get($product, 'stock_label', 'Còn hàng') }}</td></tr>
                        @foreach ($specifications as $specification)
                            <tr><th scope="row">{{ data_get($specification, 'name') }}</th><td>{{ data_get($specification, 'value') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="detail-description-panel card" id="description">
            <div class="card-body">
                <span class="eyebrow">GIỚI THIỆU</span>
                <h2>Mô tả sản phẩm</h2>
                <p>{{ data_get($product, 'description') }}</p>
                <div class="detail-feature-list">
                    @foreach ($features as $feature)
                        <div class="detail-feature"><span aria-hidden="true">✓</span>{{ $feature }}</div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="reviews-panel card" id="reviews">
            <div class="detail-section-heading"><div><span class="eyebrow">CHIA SẺ TRẢI NGHIỆM</span><h2>Đánh giá từ khách hàng</h2></div><span class="review-total-label">{{ $reviewCount ?? count($reviews) }} đánh giá</span></div>
            @if (session('review_status'))
                <div class="review-notice" role="status">{{ session('review_status') }}</div>
            @endif
            <div class="review-summary">
                <div class="review-score"><div class="review-score-value"><strong>{{ data_get($product, 'rating', 0) }}</strong><span>/ 5</span></div><span class="rating-stars" aria-label="{{ data_get($product, 'rating', 0) }} trên 5 sao">★★★★★</span><small>Dựa trên {{ $reviewCount ?? count($reviews) }} đánh giá</small></div>
                <div class="rating-breakdown">
                    @foreach ($ratingDistribution as $row)
                        <div class="rating-breakdown-row"><span>{{ data_get($row, 'stars') }} <span class="rating-stars">★</span></span><span class="rating-track"><i style="width:{{ data_get($row, 'percent', 0) }}%"></i></span><span>{{ data_get($row, 'count', 0) }}</span></div>
                    @endforeach
                </div>
                <div class="review-invite"><span class="review-invite-icon" aria-hidden="true">✦</span><div><strong>Bạn đã sử dụng sản phẩm?</strong><p>Chia sẻ trải nghiệm để giúp cộng đồng maker chọn đúng linh kiện.</p></div></div>
            </div>
            @if ($errors->any())
                <div class="review-notice is-error" role="alert">{{ $errors->first() }}</div>
            @endif
            <form action="{{ route('products.reviews.store', $product) }}" method="post" class="review-form">
                @csrf
                <h3>Viết đánh giá của bạn</h3>
                @guest
                    <label class="review-content-label" for="reviewName">Tên của bạn</label>
                    <input class="form-control" id="reviewName" name="name" value="{{ old('name') }}" maxlength="120" required>
                @endguest
                <fieldset class="review-rating-field">
                    <legend>Chọn số sao</legend>
                    <div class="star-picker">
                        @for ($rating = 5; $rating >= 1; $rating--)
                            <input id="rating-{{ $rating }}" type="radio" name="rating" value="{{ $rating }}" @checked((int) old('rating') === $rating) required>
                            <label for="rating-{{ $rating }}" title="{{ $rating }} sao">★</label>
                        @endfor
                    </div>
                </fieldset>
                <label class="review-content-label" for="reviewContent">Nhận xét</label>
                <textarea class="form-control" id="reviewContent" name="content" rows="4" minlength="10" maxlength="1000" required placeholder="Chia sẻ chất lượng, khả năng sử dụng hoặc trải nghiệm mua hàng của bạn...">{{ old('content') }}</textarea>
                <div class="review-form-footer"><small>Tối thiểu 10 ký tự, tối đa 1.000 ký tự.</small><button class="button-primary btn btn-primary" type="submit">Gửi đánh giá</button></div>
            </form>
            <div class="review-list list-group list-group-flush">
                @forelse ($reviews as $review)
                    @php($reviewRating = max(0, min(5, (int) data_get($review, 'rating', 0))))
                    <article class="review-item list-group-item">
                        <span class="review-avatar">{{ data_get($review, 'initials', 'KH') }}</span>
                        <div class="review-content">
                            <div class="review-author-row"><strong>{{ data_get($review, 'customer_name', 'Khách hàng') }}</strong><span>{{ data_get($review, 'created_at') }}</span></div>
                            <div class="review-stars" aria-label="{{ $reviewRating }} trên 5 sao"><span class="rating-stars">{{ str_repeat('★', $reviewRating) }}</span><span class="rating-stars-empty">{{ str_repeat('☆', 5 - $reviewRating) }}</span></div>
                            <p>{{ data_get($review, 'content') }}</p>
                        </div>
                    </article>
                @empty
                    <p class="empty-state">Chưa có đánh giá cho sản phẩm này.</p>
                @endforelse
            </div>
        </section>

        <section class="product-section related-products">
            <div class="section-heading"><div><span class="eyebrow">CÓ THỂ BẠN SẼ THÍCH</span><h2>Sản phẩm liên quan</h2></div><a class="text-link" href="{{ route('products.index') }}">Xem cửa hàng <span>→</span></a></div>
            <div class="product-grid">
                @foreach ($relatedProducts as $relatedProduct)
                    @php($relatedProductId = data_get($relatedProduct, 'id'))
                    <article class="product-card">
                        <a class="product-image-wrap" href="{{ route('products.show', $relatedProductId) }}"><img src="{{ data_get($relatedProduct, 'image') }}" alt="{{ data_get($relatedProduct, 'name') }}" loading="lazy"><span class="product-badge">{{ data_get($relatedProduct, 'badge') }}</span></a>
                        <div class="product-info">
                            <a class="product-title" href="{{ route('products.show', $relatedProductId) }}">{{ data_get($relatedProduct, 'name') }}</a>
                            <div class="product-rating"><span class="rating-stars">★★★★★</span><span>{{ data_get($relatedProduct, 'rating', 0) }}</span><span>·</span><span>Đã bán {{ data_get($relatedProduct, 'sold_count', 0) }}</span></div>
                            <div class="product-price-row"><span class="product-price">{{ data_get($relatedProduct, 'formatted_price', number_format((float) data_get($relatedProduct, 'price', 0), 0, ',', '.') . '₫') }}</span><span class="product-old-price">{{ data_get($relatedProduct, 'formatted_old_price') }}</span></div>
                            <a class="product-card-link" href="{{ route('products.show', $relatedProductId) }}">Xem chi tiết <span>→</span></a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.gallery-thumb').forEach(function (button) {
            button.addEventListener('click', function () {
                var image = button.querySelector('img');
                var mainImage = document.getElementById('mainProductImage');
                if (!image || !mainImage) {
                    return;
                }
                mainImage.src = image.src;
                document.querySelectorAll('.gallery-thumb').forEach(function (thumbnail) {
                    thumbnail.classList.toggle('active', thumbnail === button);
                });
            });
        });
    </script>
@endpush
