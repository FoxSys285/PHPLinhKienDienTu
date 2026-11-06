@extends('layouts.store')

@section('title', 'Trung tâm hỗ trợ')

@section('content')
<div class="support-page mx-auto max-w-7xl px-4 pb-10 pt-4 sm:pt-6">
    <section class="support-hero relative isolate flex min-h-[250px] overflow-hidden rounded-[28px] px-5 py-8 text-white shadow-[0_18px_46px_rgba(14,49,128,.28)] sm:min-h-[270px] sm:px-9 lg:min-h-[300px] lg:px-10" aria-labelledby="support-heading">
        <div class="support-hero-copy relative z-10 flex w-full max-w-[580px] items-center">
            <div>
            <span class="support-eyebrow"><span aria-hidden="true">✦</span> Trung tâm hỗ trợ Linh Kiện</span>
            <h1 id="support-heading" class="mt-3 text-3xl font-extrabold leading-[1.08] tracking-tight sm:text-4xl lg:text-[42px]">Bạn cần giúp chọn đúng linh kiện?</h1>
            <p class="mt-3 max-w-2xl text-xs font-medium leading-6 text-blue-50 sm:text-sm">Tìm hướng dẫn mua hàng, chính sách giao nhận và đổi trả, hoặc liên hệ cửa hàng khi cần hỗ trợ.</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('pages.contact') }}" class="support-primary-action">Liên hệ hỗ trợ <span aria-hidden="true">→</span></a>
            </div>
            </div>
        </div>
    </section>

    <section class="home-glass-card mt-4 rounded-3xl p-4 sm:p-5 lg:p-6" aria-labelledby="support-topics-heading">
        <div class="support-section-heading">
            <div><p class="support-kicker">Tìm câu trả lời</p><h2 id="support-topics-heading"><span class="home-section-mark"></span>Chúng tôi có thể giúp gì?</h2></div>
            <p>Chọn chủ đề phù hợp để xem hướng dẫn chi tiết.</p>
        </div>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <a href="{{ route('pages.shipping') }}" class="support-topic-card">
                <span class="support-topic-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 6h11v11H3zM14 10h4l3 3v4h-7z"/><circle cx="7" cy="19" r="1.5"/><circle cx="18" cy="19" r="1.5"/></svg></span>
                <span class="support-topic-title">Giao hàng & thanh toán</span>
                <span class="support-topic-copy">Phương thức giao nhận và các bước hoàn tất đơn hàng.</span>
                <span class="support-topic-link">Xem hướng dẫn <b aria-hidden="true">→</b></span>
            </a>
            <a href="{{ route('pages.returns') }}" class="support-topic-card">
                <span class="support-topic-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h13l-3-3m3 3-3 3M20 17H7l3 3m-3-3 3-3"/></svg></span>
                <span class="support-topic-title">Đổi trả & bảo hành</span>
                <span class="support-topic-copy">Điều kiện tiếp nhận và cách gửi yêu cầu hỗ trợ đơn hàng.</span>
                <span class="support-topic-link">Xem chính sách <b aria-hidden="true">→</b></span>
            </a>
            <a href="{{ route('account.index') }}" class="support-topic-card">
                <span class="support-topic-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/></svg></span>
                <span class="support-topic-title">Tài khoản & đơn hàng</span>
                <span class="support-topic-copy">Đăng nhập để xem đơn hàng và quản lý thông tin của bạn.</span>
                <span class="support-topic-link">Mở tài khoản <b aria-hidden="true">→</b></span>
            </a>
        </div>
    </section>

    <section class="support-faq-layout mt-4 grid gap-4 lg:grid-cols-[.85fr_1.5fr]" aria-labelledby="support-faq-heading">
        <div class="support-faq-intro rounded-3xl p-6 sm:p-8">
            <p class="support-kicker">Câu hỏi thường gặp</p>
            <h2 id="support-faq-heading" class="mt-2 text-2xl font-extrabold leading-tight">Thông tin cần biết trước khi đặt hàng</h2>
            <p class="mt-3 text-sm leading-6">Nếu chưa tìm thấy câu trả lời, hãy gửi yêu cầu qua trang liên hệ để chúng tôi hỗ trợ.</p>
            <a href="{{ route('pages.contact') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-bold">Đến trang liên hệ <span aria-hidden="true">→</span></a>
        </div>
        <div class="support-faq-list rounded-3xl p-2 sm:p-3">
            <details class="support-faq-item" open>
                <summary>Làm sao để kiểm tra mình đã chọn đúng linh kiện?<span aria-hidden="true">+</span></summary>
                <p>Đối chiếu đầy đủ MPN, package và các thông số chính trong datasheet trước khi đặt hàng.</p>
            </details>
            <details class="support-faq-item">
                <summary>Xem thông tin đổi trả và giao hàng ở đâu?<span aria-hidden="true">+</span></summary>
                <p>Chi tiết được cập nhật tại trang <a href="{{ route('pages.returns') }}">Đổi trả & bảo hành</a> và <a href="{{ route('pages.shipping') }}">Giao hàng</a>.</p>
            </details>
        </div>
    </section>
</div>
@endsection
