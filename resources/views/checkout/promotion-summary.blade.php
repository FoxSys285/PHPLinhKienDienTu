<div aria-live="polite">
    @if ($quote['coupon'])
        <div class="mt-5 rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50 to-cyan-50 p-4">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-blue-600">Khuyến mãi đang áp dụng</p>
            <h3 class="mt-1 text-sm font-extrabold text-blue-950">{{ $quote['coupon']->title ?: $quote['coupon']->code }}</h3>
            <div class="mt-2 flex items-center justify-between gap-2">
                <span class="rounded-md border border-blue-200 bg-white px-2 py-1 font-mono text-xs font-bold text-blue-700">{{ $quote['coupon']->code }}</span>
                <strong class="text-sm text-blue-700">−{{ number_format($quote['discount'], 0, ',', '.') }}₫</strong>
            </div>
            <p class="mt-2 text-[11px] leading-5 text-blue-800/80">Đã chọn ưu đãi cho tổng thanh toán thấp nhất. Mỗi đơn dùng một ưu đãi; điều kiện được kiểm tra lại khi đặt hàng.</p>
        </div>
    @else
        <p class="mt-4 rounded-xl bg-slate-50 p-3 text-xs leading-5 text-slate-500">Chưa có ưu đãi phù hợp giúp giảm tổng thanh toán cho giỏ hàng này. <a href="{{ route('pages.promotions') }}" class="font-bold text-blue-700">Xem các chương trình →</a></p>
    @endif
    @if (count($quote['eligible']) > 1)
        <details class="mt-3 text-xs text-slate-500">
            <summary class="cursor-pointer font-semibold">{{ count($quote['eligible']) }} ưu đãi đủ điều kiện</summary>
            <ul class="mt-2 grid gap-2">
                @foreach ($quote['eligible'] as $candidate)
                    <li class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 p-2"><span>{{ $candidate['coupon']->code }}</span><span>Giảm {{ number_format($candidate['discount'], 0, ',', '.') }}₫</span></li>
                @endforeach
            </ul>
        </details>
    @endif
    <dl class="mt-4 grid gap-3 border-t border-slate-100 pt-4 text-sm">
        <div class="flex justify-between gap-3"><dt class="text-slate-500">Tạm tính</dt><dd>{{ number_format($quote['subtotal'], 0, ',', '.') }}₫</dd></div>
        <div class="flex justify-between gap-3 text-blue-700"><dt>Giảm khuyến mãi</dt><dd>−{{ number_format($quote['discount'], 0, ',', '.') }}₫</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-slate-500">Phí giao hàng</dt><dd>{{ $quote['shipping'] ? number_format($quote['shipping'], 0, ',', '.').'₫' : 'Miễn phí' }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-slate-500">VAT</dt><dd>{{ number_format($quote['vat'], 0, ',', '.') }}₫</dd></div>
        <div class="flex justify-between gap-3 border-t border-slate-200 pt-3 text-base font-extrabold text-emerald-950"><dt>Tổng thanh toán</dt><dd>{{ number_format($quote['total'], 0, ',', '.') }}₫</dd></div>
    </dl>
</div>
