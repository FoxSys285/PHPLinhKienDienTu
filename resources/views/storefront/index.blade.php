@extends('layouts.store')

@section('title', ($category?->name ? $category->name.' — ' : '').'Linh kiện điện tử')

@section('content')
<div class="catalog-page mx-auto max-w-7xl px-4 pt-4 sm:pt-5">
    <x-store-hero :hero-products="$heroProducts" />

    <div class="mt-4 grid items-start gap-3 pb-8 lg:grid-cols-[205px_minmax(0,1fr)] lg:gap-3.5">
        <aside data-guide-target="catalog-filters" class="catalog-filter-panel h-fit rounded-xl border border-[#e1e9f4] bg-white p-3.5 shadow-[0_2px_8px_rgba(15,35,65,.05)] sm:p-4">
            <div class="mb-3 flex items-center gap-2 border-b border-slate-100 pb-3">
                <svg viewBox="0 0 24 24" class="size-4 text-[#142747]" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M4 5h16l-6.2 7.1V19l-3.6-1.8v-5.1z"/></svg>
                <h2 class="text-sm font-bold text-[#142747]">Bộ lọc sản phẩm</h2>
            </div>

            <form method="GET" action="{{ $category ? route('categories.show', $category) : route('products.index') }}" class="grid gap-3">
                <label class="grid gap-1 text-[11px] font-bold text-[#142747]">Từ khóa
                    <div class="relative"><input name="q" value="{{ request('q') }}" placeholder="Nhập tên sản phẩm..." class="w-full rounded-lg border border-[#d7e1ef] px-2.5 py-2 pr-8 text-xs font-normal text-slate-700 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"><svg viewBox="0 0 24 24" class="absolute right-2.5 top-1/2 size-4 -translate-y-1/2 text-[#142747]" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg></div>
                </label>

                <label class="grid gap-1 text-[11px] font-bold text-[#142747]">Danh mục
                    <select name="category" onchange="if(this.value) window.location.href=this.value" class="w-full rounded-lg border border-[#d7e1ef] bg-white px-2.5 py-2 text-xs font-normal text-slate-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="{{ route('products.index') }}{{ request()->except('page') ? '?'.http_build_query(request()->except('page')) : '' }}" @selected(! $category)>Tất cả danh mục</option>
                        @foreach($categories as $item)
                            <option value="{{ route('categories.show', $item) }}{{ request()->except('page') ? '?'.http_build_query(request()->except('page')) : '' }}" @selected($category?->id === $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="grid gap-1 text-[11px] font-bold text-[#142747]">Thương hiệu
                    <select name="brand" class="w-full rounded-lg border border-[#d7e1ef] bg-white px-2.5 py-2 text-xs font-normal text-slate-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">Tất cả thương hiệu</option>
                        @foreach($brands as $brand)<option value="{{ $brand }}" @selected(request('brand') === $brand)>{{ $brand }}</option>@endforeach
                    </select>
                </label>

                <fieldset class="grid gap-1">
                    <legend class="text-[11px] font-bold text-[#142747]">Khoảng giá</legend>
                    <div class="flex items-center gap-1.5"><input type="number" name="min_price" min="0" value="{{ request('min_price') }}" placeholder="Từ" aria-label="Giá tối thiểu" class="min-w-0 w-1/2 rounded-lg border border-[#d7e1ef] px-2 py-2 text-xs"><span class="text-slate-400">–</span><input type="number" name="max_price" min="0" value="{{ request('max_price') }}" placeholder="Đến" aria-label="Giá tối đa" class="min-w-0 w-1/2 rounded-lg border border-[#d7e1ef] px-2 py-2 text-xs"></div>
                </fieldset>

                @foreach($specFilters as $specName => $options)
                    <label class="grid gap-1 text-[11px] font-bold text-[#142747]">{{ $specName }}
                        <select name="spec[{{ $specName }}]" class="w-full rounded-lg border border-[#d7e1ef] bg-white px-2.5 py-2 text-xs font-normal text-slate-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            <option value="">Tất cả</option>
                            @foreach($options as $option)<option value="{{ $option }}" @selected(data_get(request('spec', []), $specName) === $option)>{{ $option }}</option>@endforeach
                        </select>
                    </label>
                @endforeach

                <label class="flex items-center gap-2 text-[11px] text-slate-700"><input type="checkbox" name="in_stock" value="1" @checked(request()->boolean('in_stock')) class="rounded border-slate-300 text-[#183652] focus:ring-[#183652]"> Chỉ xem hàng còn kho</label>
                <div class="grid gap-1.5 border-t border-slate-100 pt-2.5">
                    <button class="catalog-filter-submit inline-flex items-center justify-center gap-2 rounded-lg bg-[#10213d] px-3 py-2.5 text-xs font-bold text-white transition hover:bg-[#1b3c68]"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg>Áp dụng bộ lọc</button>
                    <a href="{{ $category ? route('categories.show', $category) : route('products.index') }}" class="py-1 text-center text-[11px] font-semibold text-slate-500 hover:text-[#183652]">↻ &nbsp;Xóa bộ lọc</a>
                </div>
            </form>
        </aside>

        <section class="catalog-results-panel min-w-0">
            <div class="mb-2.5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-[#142747] sm:text-2xl">{{ $category?->name ?? 'Linh kiện điện tử' }}</h1>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Tìm thấy {{ $products->total() }} sản phẩm
                        @if ($term)
                            phù hợp với “{{ $term }}”
                        @endif
                    </p>
                </div>
                <form method="GET" class="flex items-center gap-2 text-xs text-slate-600">
                    @foreach(request()->except('sort', 'page') as $key => $value)
                        @if(is_array($value))
                            @foreach($value as $nestedKey => $nestedValue)<input type="hidden" name="{{ $key }}[{{ $nestedKey }}]" value="{{ $nestedValue }}">@endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <label for="sort" class="hidden sm:inline">Sắp xếp:</label>
                    <select id="sort" name="sort" onchange="this.form.submit()" class="rounded-lg border border-[#d7e1ef] bg-white py-2 pl-2.5 pr-7 text-xs font-semibold text-[#142747] shadow-sm">
                        <option value="">Mặc định</option><option value="price_asc" @selected(request('sort') === 'price_asc')>Giá tăng dần</option><option value="price_desc" @selected(request('sort') === 'price_desc')>Giá giảm dần</option><option value="name" @selected(request('sort') === 'name')>Tên A–Z</option>
                    </select>
                </form>
            </div>
            <p class="mb-2.5 text-[11px] text-slate-500">Hiển thị <strong class="font-semibold text-slate-700">{{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}</strong> trong <strong class="font-semibold text-slate-700">{{ $products->total() }}</strong> sản phẩm</p>

            @if($products->isEmpty())
                <div class="catalog-empty rounded-xl border border-dashed border-slate-300 bg-white px-5 py-14 text-center">
                    <h2 class="text-lg font-semibold text-slate-900">Chưa tìm thấy linh kiện phù hợp</h2>
                    <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">Thử đổi SKU/MPN, nới khoảng giá hoặc gửi danh sách cần tìm.</p>
                    <div class="mt-5 flex justify-center gap-2"><a href="{{ route('products.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700">Xóa bộ lọc</a><a href="{{ route('pages.contact') }}" class="rounded-lg bg-[#10213d] px-4 py-2.5 text-sm font-semibold text-white">Liên hệ hỗ trợ tìm hàng</a></div>
                </div>
            @else
                <div class="grid grid-cols-2 gap-2.5 sm:gap-3 lg:grid-cols-4">@foreach($products as $product)@include('partials.product-card', ['product' => $product])@endforeach</div>
                <div class="mt-6">{{ $products->links() }}</div>
            @endif
        </section>
    </div>
</div>
@endsection
