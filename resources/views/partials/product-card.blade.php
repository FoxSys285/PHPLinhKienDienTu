@php($available = $product->availableStock())
@php($cardPriceGroup = auth()->user()?->account_type === 'business' && auth()->user()->business_approved ? auth()->user()->price_group : 'retail')

<article data-product-card data-guide-target="product-card" class="product-card group flex h-full flex-col overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-[0_2px_8px_rgba(15,35,65,.05)] transition duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-[0_8px_22px_rgba(15,35,65,.10)]">
    <a href="{{ route('products.show', $product) }}" class="flex flex-1 flex-col">
        <div class="relative grid aspect-[1/0.82] place-items-center overflow-hidden bg-gradient-to-br from-[#f8faff] to-[#edf2f8] p-4 sm:p-5">
            @if($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" data-product-image loading="lazy" decoding="async" class="size-full object-contain transition duration-300 group-hover:scale-[1.04]">
                <div data-product-image-fallback hidden class="grid justify-items-center gap-2 text-slate-400">
                    <svg viewBox="0 0 48 48" class="size-12" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="5" width="38" height="38" rx="4"/><circle cx="17" cy="17" r="3"/><path d="m8 37 12-12 7 7 5-5 8 8"/></svg>
                    <span class="text-xs font-medium">Chưa có ảnh</span>
                </div>
            @else
                <div class="grid justify-items-center gap-2 text-slate-400">
                    <svg viewBox="0 0 48 48" class="size-12" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="5" width="38" height="38" rx="4"/><circle cx="17" cy="17" r="3"/><path d="m8 37 12-12 7 7 5-5 8 8"/></svg>
                    <span class="text-xs font-medium">Chưa có ảnh</span>
                </div>
            @endif
            <span class="absolute left-2.5 top-2.5 max-w-[90%] truncate rounded-full bg-white/95 px-2 py-1 text-[10px] font-semibold text-[#183652] shadow-sm">
                {{ $product->category?->name ?? 'Linh kiện' }}
            </span>
        </div>
        <div class="flex flex-1 flex-col px-3 py-3 sm:px-3.5 sm:py-3.5">
            <h2 class="line-clamp-2 min-h-10 text-[13px] font-semibold leading-5 text-[#142747] sm:text-sm">{{ $product->name }}</h2>
            <p class="mt-1 line-clamp-1 font-mono text-[10px] text-slate-400">{{ $product->sku }}</p>
            <div class="mt-auto flex items-end justify-between gap-2 pt-2.5">
                <p class="text-sm font-extrabold text-orange-600 sm:text-base">{{ number_format($product->priceFor((int) $product->moq, $cardPriceGroup), 0, ',', '.') }}₫</p>
                <span class="shrink-0 text-[10px] font-medium {{ $available > 0 ? 'text-emerald-700' : 'text-slate-500' }}">{{ $available > 0 ? '✓ Còn hàng' : ($product->allow_backorder ? 'Đặt trước' : 'Tạm hết') }}</span>
            </div>
        </div>
    </a>
    <form action="{{ route('cart.store', $product) }}" method="POST" class="px-3 pb-3 sm:px-3.5 sm:pb-3.5">
        @csrf
        <input type="hidden" name="quantity" value="{{ $product->moq }}">
        <button @disabled($available === 0 && ! $product->allow_backorder) class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-blue-200 bg-white px-3 py-2 text-xs font-semibold text-[#183652] transition hover:border-[#183652] hover:bg-blue-50 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400">
            @if($available > 0 || $product->allow_backorder)<svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2.2 11.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20 9H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>@endif
            {{ $available === 0 && ! $product->allow_backorder ? 'Tạm hết hàng' : 'Thêm vào giỏ' }}
        </button>
    </form>
</article>
