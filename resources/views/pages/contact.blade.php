@extends('layouts.store')
@section('title','Liên hệ hỗ trợ')
@section('content')
<div class="mx-auto max-w-4xl px-4 py-12"><p class="text-xs font-bold tracking-wide text-emerald-700">Chúng tôi sẵn sàng hỗ trợ</p><h1 class="mt-2 text-3xl font-bold tracking-tight text-emerald-950">Liên hệ cửa hàng</h1><p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Bạn cần xác nhận thông số, tìm linh kiện thay thế hoặc mua hàng số lượng lớn? Tra cứu sản phẩm hoặc xem các hướng dẫn hỗ trợ của chúng tôi.</p><div class="mt-7 grid gap-4 sm:grid-cols-2"><a href="{{ route('products.index') }}" class="rounded-lg border border-slate-200 bg-white p-5 hover:border-emerald-900/30"><span class="text-xs font-bold uppercase tracking-wide text-emerald-700">Tra cứu linh kiện</span><strong class="mt-2 block text-lg font-bold text-emerald-950">Tìm sản phẩm</strong><p class="mt-1 text-sm text-slate-500">Tìm theo mã SKU/MPN, thông số và danh mục.</p></a><a href="{{ route('account.index') }}" class="rounded-lg border border-slate-200 bg-white p-5 hover:border-emerald-900/30"><span class="text-xs font-bold uppercase tracking-wide text-emerald-700">Hỗ trợ đơn hàng</span><strong class="mt-2 block text-lg font-bold text-emerald-950">Mở tài khoản của bạn</strong><p class="mt-1 text-sm text-slate-500">Theo dõi đơn và gửi yêu cầu đổi trả.</p></a></div><section data-reveal-item class="mt-7 overflow-hidden rounded-3xl border border-white/80 bg-white/60 shadow-[0_18px_48px_rgba(35,72,145,.12)] backdrop-blur-xl">
    <div class="flex flex-wrap items-end justify-between gap-3 p-5 sm:p-6">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.16em] text-blue-700">Bản đồ tương tác</p>
            <h2 class="mt-1 text-xl font-extrabold text-[#10283e]">Vị trí minh họa tại TP. Hồ Chí Minh</h2>
            <p class="mt-1 text-xs text-slate-600">Điểm được chọn ngẫu nhiên theo yêu cầu, chưa phải địa chỉ cửa hàng.</p>
        </div>
        <a href="https://www.openstreetmap.org/?mlat=10.7751&mlon=106.7020#map=16/10.7751/106.7020" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-white/80 px-3.5 py-2.5 text-xs font-bold text-blue-900 transition hover:bg-blue-50">
            Mở bản đồ lớn
            <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M14 4h6v6m-11 3L20 4M18 13v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h6"/></svg>
        </a>
    </div>
    <div class="relative h-64 overflow-hidden border-t border-white/80 bg-blue-50 sm:h-[360px]">
        <iframe title="Bản đồ OpenStreetMap với vị trí minh họa ở Thành phố Hồ Chí Minh" src="https://www.openstreetmap.org/export/embed.html?bbox=106.691%2C10.768%2C106.713%2C10.782&layer=mapnik&marker=10.7751%2C106.7020" class="size-full border-0" loading="lazy" referrerpolicy="no-referrer" allowfullscreen></iframe>
        <button type="button" data-map-marker aria-expanded="false" aria-controls="contact-map-popup" aria-label="Xem thông tin marker minh họa" class="absolute left-1/2 top-1/2 grid size-11 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full border-2 border-white bg-blue-700 text-white shadow-[0_8px_24px_rgba(29,78,216,.45)] transition hover:scale-110 focus-visible:outline-offset-4">
            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
        </button>
        <div id="contact-map-popup" data-map-popup hidden role="status" class="absolute bottom-[calc(50%+2.4rem)] left-1/2 w-[min(19rem,calc(100%_-_2rem))] -translate-x-1/2 rounded-2xl border border-white/90 bg-white/95 p-4 text-left shadow-[0_16px_40px_rgba(15,35,65,.2)] backdrop-blur-xl">
            <div class="flex items-start justify-between gap-3">
                <div><strong class="block text-sm text-[#10283e]">Điểm tham khảo</strong><span class="mt-1 block text-xs text-slate-600">10.7751, 106.7020 · TP. Hồ Chí Minh</span><span class="mt-1 block text-[10px] font-semibold text-amber-700">Không phải địa chỉ cửa hàng</span></div>
                <button type="button" data-map-close aria-label="Đóng thông tin marker" class="grid size-7 shrink-0 place-items-center rounded-lg text-lg text-slate-500 hover:bg-slate-100">×</button>
            </div>
        </div>
    </div>
</section>
<p class="mt-4 rounded-2xl border border-amber-200/80 bg-amber-50/85 p-4 text-xs leading-5 text-amber-900">Thông tin hotline, email và địa chỉ thực chưa được cấu hình. Marker trên bản đồ chỉ là điểm minh họa được chọn ngẫu nhiên.</p></div>
@endsection
