<div>
    <!-- The only way to do great work is to love what you do. - Steve Jobs -->
</div>
@extends('layouts.user')

@php
    $products = $products ?? [];
    $productCategories = $productCategories ?? ['Tất cả', 'Bo mạch', 'Cảm biến', 'Nguồn', 'Dụng cụ'];
    $query = request('q', $query ?? '');
    $selectedCategory = $selectedCategory ?? 'Tất cả';
    $selectedCategorySlug = request('category', $selectedCategorySlug ?? '');
    $sort = request('sort', $sort ?? 'featured');
@endphp

@section('title', $query === '' ? 'Sản phẩm' : 'Kết quả tìm kiếm')

@section('content')
    <nav class="breadcrumb" aria-label="Đường dẫn">
        <a href="{{ route('home') }}">Trang chủ</a><span>›</span><span>{{ $query === '' ? 'Sản phẩm' : 'Kết quả tìm kiếm' }}</span>
    </nav>

    <section class="listing-intro">
        <div>
            <span class="eyebrow">KHÁM PHÁ CỬA HÀNG</span>
            <h1>{{ $query === '' ? 'Linh kiện điện tử' : 'Kết quả tìm kiếm' }}</h1>
            <p>{{ $query === '' ? 'Tìm mọi thứ bạn cần để biến ý tưởng thành sản phẩm.' : 'Kết quả phù hợp với “' . $query . '”.' }}</p>
        </div>
        <span class="listing-count">Tìm thấy <strong>{{ $totalProducts ?? count($products) }}</strong> sản phẩm</span>
    </section>

    <div class="filter-bar" aria-label="Bộ lọc sản phẩm">
        <span class="filter-label">Danh mục</span>
        @foreach ($productCategories as $category)
            @php($categoryName = is_string($category) ? $category : data_get($category, 'name', ''))
            @php($categoryFilter = is_string($category) ? $category : data_get($category, 'filter', data_get($category, 'slug')))
            <a class="filter-chip {{ strcasecmp($selectedCategory, $categoryName) === 0 ? 'active' : '' }}" href="{{ route('products.index', ['q' => $query, 'category' => $categoryName === 'Tất cả' ? null : $categoryFilter, 'sort' => $sort]) }}">{{ $categoryName }}</a>
        @endforeach
        <form class="sort-form" action="{{ route('products.index') }}" method="get">
            <input type="hidden" name="q" value="{{ $query }}">
            <input type="hidden" name="category" value="{{ $selectedCategorySlug }}">
            <label class="visually-hidden" for="sortProducts">Sắp xếp sản phẩm</label>
            <select id="sortProducts" class="sort-select" name="sort" onchange="this.form.submit()">
                <option value="featured" @selected($sort === 'featured')>Sắp xếp: Nổi bật</option>
                <option value="price-asc" @selected($sort === 'price-asc')>Giá: Thấp đến cao</option>
                <option value="price-desc" @selected($sort === 'price-desc')>Giá: Cao đến thấp</option>
                <option value="name" @selected($sort === 'name')>Tên sản phẩm</option>
            </select>
        </form>
    </div>

    @if (count($products) > 0)
        <div class="product-grid catalog-grid">
            @foreach ($products as $product)
                @php($productId = data_get($product, 'id'))
                <article class="product-card">
                    <a class="product-image-wrap" href="{{ route('products.show', $productId) }}">
                        <img src="{{ data_get($product, 'image') }}" alt="{{ data_get($product, 'name') }}" loading="lazy">
                        <span class="product-badge">{{ data_get($product, 'badge') }}</span>
                    </a>
                    <div class="product-info">
                        <span class="catalog-category">{{ data_get($product, 'category.name', data_get($product, 'category')) }}</span>
                        <a class="product-title" href="{{ route('products.show', $productId) }}">{{ data_get($product, 'name') }}</a>
                        <div class="product-rating"><span class="rating-stars" aria-label="5 sao">★★★★★</span><span>{{ data_get($product, 'rating', 0) }}</span><span>·</span><span>Đã bán {{ data_get($product, 'sold_count', 0) }}</span></div>
                        <div class="product-price-row"><span class="product-price">{{ data_get($product, 'formatted_price', number_format((float) data_get($product, 'price', 0), 0, ',', '.') . '₫') }}</span><span class="product-old-price">{{ data_get($product, 'formatted_old_price') }}</span></div>
                        <a class="product-card-link" href="{{ route('products.show', $productId) }}">Xem chi tiết <span>→</span></a>
                        <form class="catalog-add-form" action="{{ route('cart.store') }}" method="post">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $productId }}">
                            <input type="hidden" name="quantity" value="1">
                            <button class="button-primary catalog-add-button" type="submit" @disabled((int) data_get($product, 'stock', 0) < 1)>Thêm vào giỏ hàng</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <section class="search-empty-state">
            <span aria-hidden="true">⌕</span>
            <h2>Chưa tìm thấy sản phẩm phù hợp</h2>
            <p>Thử từ khóa khác hoặc bỏ bớt bộ lọc để xem thêm sản phẩm.</p>
            <a class="button-outline" href="{{ route('products.index') }}">Xem tất cả sản phẩm</a>
        </section>
    @endif
@endsection
