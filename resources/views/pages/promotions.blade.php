<div>
    <!-- Very little is needed to make a happy life. - Marcus Aurelius -->
</div>
@extends('layouts.store')

@section('title', 'Khuyến mãi linh kiện điện tử')

@section('content')
<div class="promotions-page mx-auto max-w-7xl px-4 pb-10 pt-4 sm:pt-6">
    <section class="promotion-hero relative isolate flex min-h-[250px] overflow-hidden rounded-[28px] text-white shadow-[0_18px_46px_rgba(14,49,128,.28)] sm:min-h-[270px] lg:min-h-[300px]" aria-labelledby="promotions-heading">
        <div class="promotion-hero-copy relative z-10 flex w-full max-w-[580px] items-center px-5 py-8 sm:px-9 lg:px-10">
            <div>
            <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3 py-1.5 text-[10px] font-semibold text-white/95 shadow-sm backdrop-blur-md sm:text-xs"><span class="text-orange-300" aria-hidden="true">🔥</span> Ưu đãi dành cho bạn</span>
            <h1 id="promotions-heading" class="mt-3 text-3xl font-extrabold leading-[1.08] tracking-tight sm:text-4xl lg:text-[42px]">Thêm cảm hứng,<br><span class="promotion-title-highlight mt-1 block">nhẹ chi phí dự án.</span></h1>
            <p class="mt-3 max-w-xl text-xs font-medium leading-6 text-blue-50 sm:text-sm">Khám phá ưu đãi linh kiện và mức giá theo số lượng cho dự án của bạn.</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('products.index') }}" class="promotion-primary-action inline-flex items-center gap-2 rounded-full px-5 py-3 text-sm font-bold text-white">Khám phá sản phẩm <span aria-hidden="true">→</span></a>
                <a href="{{ route('pages.support') }}" class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-5 py-3 text-sm font-semibold text-white backdrop-blur-md transition hover:bg-white/20">Tìm hiểu thêm</a>
            </div>
            </div>
        </div>
    </section>

    <section class="home-glass-card mt-4 rounded-3xl p-4 sm:p-5 lg:p-6" aria-labelledby="promotion-types-heading">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-blue-700">Ưu đãi nổi bật</p>
                <h2 id="promotion-types-heading" class="mt-1 flex items-center gap-2 text-lg font-extrabold tracking-tight text-[#142747] sm:text-xl"><span class="home-section-mark"></span>Chọn ưu đãi phù hợp</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Từ đơn hàng đầu tiên đến nhu cầu mua linh kiện cho dự án và doanh nghiệp.</p>
            </div>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-3">
            <article class="promotion-offer-card promotion-offer-card--first rounded-3xl p-5 sm:p-6">
                <span class="promotion-offer-icon"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 12v8H4v-8m-2-5h20v5H2zm10 0v13m0-13H8.5a2.5 2.5 0 1 1 2.1-3.8L12 7zm0 0h3.5a2.5 2.5 0 1 0-2.1-3.8L12 7z"/></svg></span>
                <p class="mt-4 text-xs font-bold uppercase tracking-[.16em] text-blue-700">Khách hàng mới</p>
                <h3 class="mt-2 text-xl font-extrabold text-[#102653]">Ưu đãi đơn hàng đầu tiên</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">Khám phá chương trình ưu đãi dành cho lần mua hàng đầu tiên tại Linh Kiện.</p>
                <a href="{{ route('products.index') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-blue-800">Mua sắm ngay <span aria-hidden="true">→</span></a>
            </article>

            <article class="promotion-offer-card promotion-offer-card--volume rounded-3xl p-5 sm:p-6">
                <span class="promotion-offer-icon"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 19V5m0 14h17M8 15l3-4 3 2 5-7"/><circle cx="19" cy="6" r="1.5"/></svg></span>
                <p class="mt-4 text-xs font-bold uppercase tracking-[.16em] text-blue-700">Mua theo số lượng</p>
                <h3 class="mt-2 text-xl font-extrabold text-[#102653]">Giá tốt theo bậc</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">Một số sản phẩm có mức giá theo số lượng. Xem chi tiết ngay tại trang sản phẩm.</p>
                <a href="{{ route('products.index') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-blue-800">Xem sản phẩm <span aria-hidden="true">→</span></a>
            </article>

            <article class="promotion-offer-card promotion-offer-card--project rounded-3xl p-5 sm:p-6">
                <span class="promotion-offer-icon"><svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 4h16v16H4zM8 8h8m-8 4h8m-8 4h5"/></svg></span>
                <p class="mt-4 text-xs font-bold uppercase tracking-[.16em] text-blue-700">Dự án & doanh nghiệp</p>
                <h3 class="mt-2 text-xl font-extrabold text-[#102653]">Hỗ trợ đơn hàng dự án</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">Liên hệ cửa hàng để được hỗ trợ khi mua linh kiện cho dự án hoặc doanh nghiệp.</p>
                <a href="{{ route('pages.contact') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-blue-800">Thông tin liên hệ <span aria-hidden="true">→</span></a>
            </article>
        </div>
    </section>

    <section class="promotion-bottom-cta home-glass-card mt-4 flex flex-col justify-between gap-5 rounded-3xl p-5 sm:flex-row sm:items-center sm:p-7">
        <div><p class="text-xs font-bold uppercase tracking-[.16em] text-blue-700">Cần hỗ trợ chọn linh kiện?</p><h2 class="mt-1 text-xl font-extrabold text-[#102653]">Đội ngũ Linh Kiện sẵn sàng đồng hành.</h2><p class="mt-1 text-sm text-slate-600">Xem hướng dẫn, chính sách giao hàng hoặc gửi yêu cầu hỗ trợ.</p></div>
        <a href="{{ route('pages.support') }}" class="promotion-secondary-action inline-flex shrink-0 items-center justify-center gap-2 rounded-full px-5 py-3 text-sm font-bold text-blue-800">Trung tâm hỗ trợ <span aria-hidden="true">→</span></a>
    </section>
</div>
@endsection
