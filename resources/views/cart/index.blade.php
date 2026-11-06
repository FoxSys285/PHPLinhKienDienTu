@extends('layouts.store')

@section('title', 'Giỏ hàng của bạn')

@section('content')
<div class="cart-page mx-auto max-w-7xl px-4 py-8 sm:py-10">
    <header class="mb-6 sm:mb-8">
        <p class="text-xs font-bold uppercase tracking-[.18em] text-blue-700">Đơn hàng của bạn</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-[#102653]">Giỏ hàng</h1>
    </header>

    @if($cart->items->isEmpty())
        <section class="cart-empty-panel rounded-3xl border border-dashed border-blue-200 bg-white/55 px-5 py-16 text-center shadow-[0_14px_36px_rgba(55,91,169,.1)] backdrop-blur-xl">
            <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-blue-100/75 text-blue-700">
                <svg viewBox="0 0 24 24" class="size-7" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4h2l2.2 11.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L20 9H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
            </span>
            <h2 class="mt-4 text-xl font-bold text-[#102653]">Chưa có linh kiện trong giỏ</h2>
            <p class="mt-1 text-sm text-slate-500">Tìm sản phẩm theo mã hoặc duyệt danh mục để bắt đầu.</p>
            <a href="{{ route('products.index') }}" class="cart-primary-action mt-5 inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold text-white">Khám phá sản phẩm <span aria-hidden="true">→</span></a>
        </section>
    @else
        @php($priceGroup = auth()->user()?->account_type === 'business' && auth()->user()->business_approved ? auth()->user()->price_group : 'retail')

        <div class="cart-layout grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_340px] lg:gap-6">
            <section class="cart-items-panel overflow-hidden rounded-3xl border border-white/80 bg-white/50 shadow-[0_14px_36px_rgba(55,91,169,.1)] backdrop-blur-xl" aria-label="Sản phẩm trong giỏ">
                <div class="hidden grid-cols-[minmax(0,1fr)_120px_135px_120px_40px] gap-3 border-b border-white/70 bg-white/35 px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500 sm:grid">
                    <span>Sản phẩm</span><span class="text-center">Số lượng</span><span class="text-right">Đơn giá</span><span class="text-right">Thành tiền</span><span></span>
                </div>

                @foreach($cart->items as $item)
                    @if($item->product)
                        <article class="cart-line-item grid grid-cols-1 gap-4 border-b border-white/70 p-4 last:border-0 sm:grid-cols-[minmax(0,1fr)_120px_135px_120px_40px] sm:items-center sm:gap-3 sm:px-5">
                            <a href="{{ route('products.show', $item->product) }}" class="flex min-w-0 items-center gap-3">
                                @if($item->product->image_url)
                                    <span class="cart-item-thumbnail grid size-16 shrink-0 place-items-center overflow-hidden rounded-2xl border border-white/80 bg-white/55 p-1.5">
                                        <img src="{{ $item->product->image_url }}" alt="" data-product-image loading="lazy" class="size-full object-contain">
                                        <span data-product-image-fallback hidden class="font-mono text-xs font-bold text-blue-700">{{ mb_strtoupper(mb_substr($item->product->sku, 0, 2)) }}</span>
                                    </span>
                                @else
                                    <span class="cart-item-thumbnail grid size-16 shrink-0 place-items-center rounded-2xl border border-white/80 bg-blue-100/70 font-mono text-xs font-bold text-blue-800">{{ mb_strtoupper(mb_substr($item->product->sku, 0, 2)) }}</span>
                                @endif
                                <span class="min-w-0">
                                    <strong class="block line-clamp-2 text-sm font-bold text-[#102653]">{{ $item->product->name }}</strong>
                                    <span class="mt-1 block font-mono text-[10px] text-slate-500">{{ $item->product->sku }} · MOQ {{ $item->product->moq }}</span>
                                </span>
                            </a>

                            <form action="{{ route('cart.update', $item) }}" method="POST" class="flex items-center justify-between gap-3 sm:justify-center">
                                @csrf
                                @method('PATCH')
                                <label class="text-xs font-semibold text-slate-500 sm:sr-only">Số lượng</label>
                                <input type="number" name="quantity" min="{{ $item->product->moq }}" step="{{ $item->product->pack_size }}" value="{{ $item->quantity }}" class="cart-quantity-input w-24 rounded-xl border border-white/80 bg-white/65 px-2 py-2 text-center text-sm font-bold text-[#102653]" onchange="this.form.requestSubmit()">
                                <button class="text-xs font-bold text-blue-700 hover:underline sm:hidden">Cập nhật</button>
                            </form>

                            <div class="flex justify-between text-sm text-slate-600 sm:block sm:text-right"><span class="text-xs text-slate-500 sm:hidden">Đơn giá</span><span>{{ number_format($item->product->priceFor((int) $item->quantity, $priceGroup), 0, ',', '.') }}₫</span></div>
                            <div class="flex justify-between text-sm font-bold text-[#102653] sm:block sm:text-right"><span class="text-xs font-semibold text-slate-500 sm:hidden">Thành tiền</span><span>{{ number_format($item->product->priceFor((int) $item->quantity, $priceGroup) * $item->quantity, 0, ',', '.') }}₫</span></div>

                            <form action="{{ route('cart.destroy', $item) }}" method="POST" class="justify-self-end">
                                @csrf
                                @method('DELETE')
                                <button aria-label="Xóa sản phẩm" class="grid size-9 place-items-center rounded-xl text-slate-400 transition hover:bg-rose-50 hover:text-rose-700">
                                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16m-10 4v6m4-6v6M5 7l1 14h12l1-14M9 7V4h6v3"/></svg>
                                </button>
                            </form>
                        </article>
                    @endif
                @endforeach
            </section>

            <aside class="cart-order-summary rounded-3xl border border-white/80 bg-white/55 p-5 shadow-[0_14px_36px_rgba(55,91,169,.12)] backdrop-blur-xl sm:p-6">
                <h2 class="text-lg font-extrabold text-[#102653]">Tóm tắt đơn hàng</h2>
                <div class="mt-5 grid gap-3 text-sm">
                    <div class="flex justify-between text-slate-600"><span>Tạm tính</span><span>{{ number_format($subtotal, 0, ',', '.') }}₫</span></div>
                    <div class="flex justify-between text-slate-600"><span>Giao hàng dự kiến</span><span>{{ $shippingFee ? number_format($shippingFee, 0, ',', '.').'₫' : 'Miễn phí' }}</span></div>
                    <div class="mt-2 flex justify-between border-t border-blue-100 pt-4 text-base font-bold text-[#102653]"><span>Tổng dự kiến</span><span>{{ number_format($subtotal + $shippingFee, 0, ',', '.') }}₫</span></div>
                </div>
                <p class="mt-3 text-xs leading-5 text-slate-500">Mã giảm giá và thuế VAT (nếu áp dụng) sẽ được tính ở bước thanh toán.</p>
                <a href="{{ route('checkout.create') }}" class="cart-primary-action mt-5 flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3.5 text-sm font-bold text-white">Tiến hành thanh toán <span aria-hidden="true">→</span></a>
                <a href="{{ route('products.index') }}" class="mt-3 block text-center text-xs font-bold text-blue-800 hover:underline">Tiếp tục mua hàng</a>
                <div class="mt-5 rounded-2xl border border-white/80 bg-blue-50/65 p-3 text-xs leading-5 text-blue-950"><strong>Đóng gói an toàn</strong><br>Linh kiện nhạy cảm được bảo vệ khỏi tĩnh điện trong quá trình giao hàng.</div>
            </aside>
        </div>
    @endif
</div>
@endsection
