@php
    $available = $product->availableStock();
    $unitPrice = $product->priceFor((int) $product->moq);
@endphp
<article data-product-card class="group flex h-full min-w-0 flex-col overflow-hidden rounded-3xl border border-blue-200/90 bg-white/85 p-3 shadow-[0_8px_20px_rgba(38,89,180,.06)] transition duration-200 hover:-translate-y-1 hover:border-blue-400 hover:shadow-[0_14px_28px_rgba(38,89,180,.12)]">
    <a href="{{ route('products.show', $product) }}" class="flex flex-1 flex-col">
        <div class="relative grid aspect-[1.15/1] place-items-center overflow-hidden rounded-2xl bg-white p-3">
            @if ($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" data-product-image loading="lazy" decoding="async" class="size-full object-contain transition duration-300 group-hover:scale-105">
            @endif
            <div data-product-image-fallback @if ($product->image_url) hidden @endif class="grid justify-items-center gap-2 text-blue-200">
                <svg viewBox="0 0 48 48" class="size-14" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="12" y="12" width="24" height="24" rx="4"/><rect x="19" y="19" width="10" height="10" rx="1"/><path d="M18 5v7m12-7v7M18 36v7m12-7v7M5 18h7m-7 12h7m24-12h7m-7 12h7"/></svg>
                <span class="text-[10px] text-slate-400">Chưa có ảnh</span>
            </div>
            <span class="absolute right-0 top-0 rounded-full bg-gradient-to-r from-cyan-500 to-blue-600 px-2 py-1 text-[10px] font-extrabold text-white shadow-sm sm:text-xs">{{ $promotion->type === 'percentage' ? '−'.$promotion->discount_label : 'Có ưu đãi' }}</span>
        </div>
        <h3 class="mt-3 line-clamp-2 min-h-10 text-xs font-extrabold leading-5 tracking-tight text-[#10285a] sm:text-sm">{{ $product->name }}</h3>
        <p class="mt-1 text-[10px] font-medium {{ $available > 0 ? 'text-emerald-600' : 'text-slate-400' }}">{{ $available > 0 ? 'Còn hàng' : ($product->allow_backorder ? 'Nhận đặt trước' : 'Tạm hết hàng') }}</p>
        <div class="mt-auto flex flex-wrap items-baseline gap-x-2 gap-y-1 pt-3">
            @if ($product->compare_at_price > $unitPrice)
                <del class="text-[11px] text-slate-400">{{ number_format($product->compare_at_price, 0, ',', '.') }}₫</del>
            @endif
            <strong class="text-sm font-black tracking-tight text-blue-600 sm:text-lg">{{ number_format($unitPrice, 0, ',', '.') }}₫</strong>
        </div>
    </a>
    <form action="{{ route('cart.store', $product) }}" method="POST" class="mt-3">
        @csrf
        <input type="hidden" name="quantity" value="{{ $product->moq }}">
        <button @disabled($available === 0 && ! $product->allow_backorder) class="inline-flex min-h-10 w-full items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-[#008aff] to-blue-700 px-2 py-2 text-[11px] font-bold text-white transition hover:brightness-110 disabled:cursor-not-allowed disabled:from-slate-200 disabled:to-slate-200 disabled:text-slate-400 sm:text-xs">
            <svg viewBox="0 0 24 24" class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2.2 11.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20 9H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
            {{ $available === 0 && ! $product->allow_backorder ? 'Tạm hết hàng' : 'Thêm vào giỏ' }}
        </button>
    </form>
</article>
