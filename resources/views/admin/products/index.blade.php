@extends('layouts.admin')
@section('title', 'Quản lý sản phẩm')
@section('content')
@php
    $summaryCards = [
        ['label' => 'Tổng sản phẩm', 'value' => $stats['total'], 'note' => 'Trong danh mục cửa hàng', 'tone' => 'text-blue-700', 'icon' => 'SP'],
        ['label' => 'Đang kinh doanh', 'value' => $stats['active'], 'note' => 'Đang hiển thị với khách hàng', 'tone' => 'text-emerald-600', 'icon' => '✓'],
        ['label' => 'Sắp chạm mức tối thiểu', 'value' => $stats['low_stock'], 'note' => 'Cần theo dõi nhập hàng', 'tone' => 'text-amber-600', 'icon' => '↓'],
        ['label' => 'Đang ẩn', 'value' => $stats['hidden'], 'note' => 'Chưa hiển thị trên cửa hàng', 'tone' => 'text-slate-500', 'icon' => '◌'],
    ];
@endphp

<div class="mx-auto max-w-screen-2xl">
    <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-xs font-bold uppercase tracking-[.18em] text-blue-600">Danh mục & giá</p><h1 class="mt-2 text-3xl font-extrabold tracking-tight text-[#172554] sm:text-4xl">Sản phẩm</h1><p class="mt-2 text-sm text-slate-500">Quản lý danh mục, giá bán và mức tồn tối thiểu của linh kiện.</p></div>
        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700"><span aria-hidden="true" class="text-lg leading-none">＋</span> Thêm sản phẩm</a>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach ($summaryCards as $card)
            <div class="group relative overflow-hidden rounded-2xl border border-white/80 bg-white/75 p-5 shadow-[0_14px_36px_-28px_rgba(23,37,84,.4)] backdrop-blur transition hover:-translate-y-0.5 hover:shadow-lg">
                <span class="absolute -right-5 -top-7 size-24 rounded-full bg-blue-50/80 transition group-hover:scale-125"></span>
                <div class="relative flex items-start justify-between gap-3"><div><p class="text-xs font-semibold text-slate-500">{{ $card['label'] }}</p><p class="mt-3 text-3xl font-extrabold tracking-tight {{ $card['tone'] }}">{{ number_format($card['value']) }}</p><p class="mt-1 text-[11px] text-slate-400">{{ $card['note'] }}</p></div><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-xs font-black text-blue-700">{{ $card['icon'] }}</span></div>
            </div>
        @endforeach
    </div>

    <section class="overflow-hidden rounded-2xl border border-white/80 bg-white/80 shadow-sm">
        <form method="GET" class="flex flex-wrap items-center gap-3 border-b border-slate-100 p-4">
            <label class="min-w-48 flex-1"><span class="sr-only">Tìm kiếm sản phẩm</span><input name="q" value="{{ request('q') }}" placeholder="Tìm theo tên, SKU hoặc mã MPN..." class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm"></label>
            <span class="mr-1 text-xs text-slate-400">{{ number_format($products->total()) }} kết quả</span>
            <button class="rounded-xl bg-[#172554] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-900">Tìm sản phẩm</button>
            @if (request()->filled('q'))<a href="{{ route('admin.products.index') }}" class="px-2 text-xs font-semibold text-slate-500 hover:text-blue-700">Đặt lại</a>@endif
        </form>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-left text-sm">
                <thead class="bg-slate-50/80 text-xs font-semibold text-slate-500"><tr><th class="px-5 py-4">Sản phẩm</th><th class="px-4 py-4">Danh mục</th><th class="px-4 py-4 text-right">Giá bán</th><th class="px-4 py-4 text-right">Tồn kho</th><th class="px-4 py-4">Trạng thái</th><th class="px-5 py-4 text-right">Thao tác</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $product)
                        <tr class="transition hover:bg-blue-50/30">
                            <td class="max-w-[440px] px-5 py-4"><div class="flex items-center gap-3"><div class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-2xl border border-slate-100 bg-slate-50">@if ($product->image_url)<img src="{{ $product->image_url }}" alt="" data-product-image loading="lazy" class="size-full object-contain p-1.5"><span data-product-image-fallback hidden class="px-1 text-center text-[9px] text-slate-500">Ảnh lỗi</span>@else<span class="px-1 text-center text-[9px] font-semibold text-slate-400">Chưa có ảnh</span>@endif</div><div class="min-w-0"><strong class="block truncate text-sm text-[#172554]">{{ $product->name }}</strong><span class="mt-1 block truncate font-mono text-[10px] text-slate-500">{{ $product->sku }} @if ($product->mpn)<span class="text-slate-300">·</span> MPN {{ $product->mpn }}@endif</span></div></div></td>
                            <td class="px-4 py-4"><span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $product->category?->name ?? 'Chưa phân loại' }}</span></td>
                            <td class="px-4 py-4 text-right font-extrabold text-[#172554]">{{ number_format((float) $product->price, 0, ',', '.') }}₫</td>
                            <td class="px-4 py-4 text-right"><strong @class(['text-base font-extrabold', 'text-rose-700' => $product->availableStock() <= 0, 'text-amber-700' => $product->availableStock() > 0 && $product->availableStock() <= $product->minimum_stock, 'text-slate-800' => $product->availableStock() > $product->minimum_stock])>{{ number_format($product->stock) }}</strong><span class="block text-[11px] text-slate-400">Tối thiểu {{ number_format($product->minimum_stock) }}</span>@if ($product->reserved_stock > 0)<span class="block text-[10px] text-slate-400">Đang giữ {{ number_format($product->reserved_stock) }}</span>@endif</td>
                            <td class="px-4 py-4"><span @class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold', 'bg-emerald-50 text-emerald-700' => $product->is_active, 'bg-slate-100 text-slate-500' => ! $product->is_active])><span @class(['size-1.5 rounded-full', 'bg-emerald-500' => $product->is_active, 'bg-slate-400' => ! $product->is_active])></span>{{ $product->is_active ? 'Đang bán' : 'Đã ẩn' }}</span></td>
                            <td class="px-5 py-4 text-right"><a href="{{ route('admin.products.edit', $product) }}" class="inline-flex rounded-lg px-3 py-2 text-xs font-bold text-blue-700 transition hover:bg-blue-50">Chỉnh sửa <span class="ml-1" aria-hidden="true">→</span></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-16 text-center"><span class="mx-auto grid size-12 place-items-center rounded-2xl bg-blue-50 text-xl text-blue-600">⌕</span><strong class="mt-4 block text-sm text-[#172554]">Chưa có sản phẩm phù hợp</strong><p class="mt-1 text-xs text-slate-500">Thử một từ khóa khác hoặc tạo sản phẩm mới cho cửa hàng.</p><a href="{{ route('admin.products.create') }}" class="mt-4 inline-flex rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-700">＋ Thêm sản phẩm</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 p-4">{{ $products->links() }}</div>
    </section>
</div>
@endsection
