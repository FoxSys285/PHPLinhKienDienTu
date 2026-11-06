@extends('layouts.store')
@section('title', str_starts_with((string) $intent->payment_link_id, 'MOCK-') ? 'Thanh toán mô phỏng' : 'Xác nhận thanh toán · PayOS')
@section('content')
@php($isMockPayment = str_starts_with((string) $intent->payment_link_id, 'MOCK-'))
<main class="mx-auto max-w-2xl px-4 py-16">
    <section class="rounded-2xl border border-slate-200 bg-white p-7 text-center shadow-sm sm:p-10" data-payos-status data-status-url="{{ route('checkout.payos.status', $intent->public_token) }}">
        <span class="mx-auto grid size-16 place-items-center rounded-full bg-blue-50 text-blue-700" aria-hidden="true">
            <svg class="size-8 animate-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        </span>
        <p class="mt-5 text-xs font-bold uppercase tracking-[.18em] text-blue-700">{{ $isMockPayment ? 'Thanh toán mô phỏng' : 'Thanh toán PayOS' }}</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ $isMockPayment && $wasCancelled ? 'Giao dịch mô phỏng đã thất bại' : ($isMockPayment && $intent->status === 'expired' ? 'Liên kết mô phỏng đã hết hạn' : 'Đang chờ xác nhận giao dịch') }}</h1>
        @if ($wasCancelled)
            <p class="mx-auto mt-3 max-w-lg text-sm leading-6 text-amber-800">{{ $isMockPayment ? 'Giao dịch thử đã bị hủy. Không có khoản tiền thật nào được thu; bạn có thể quay lại checkout để thử lại.' : 'Bạn đã quay lại từ trang thanh toán. Hệ thống sẽ cập nhật trạng thái khi nhận được xác nhận từ PayOS.' }}</p>
        @else
            <p class="mx-auto mt-3 max-w-lg text-sm leading-6 text-slate-600">{{ $isMockPayment && $intent->status === 'expired' ? 'Liên kết demo đã hết hạn, không có khoản tiền thật nào được thu. Hãy quay lại checkout để thử lại.' : ($isMockPayment ? 'Đây là giao dịch mô phỏng cho đồ án. Không kết nối ngân hàng và không thu tiền thật.' : 'Đừng đóng trang nếu bạn vừa quét mã QR. PayOS sẽ gửi xác nhận giao dịch trực tiếp đến hệ thống.') }}</p>
        @endif

        <div class="mx-auto mt-7 max-w-md rounded-xl bg-slate-50 p-5 text-left">
            <div class="flex justify-between gap-4 text-sm"><span class="text-slate-500">Số tiền</span><strong>{{ number_format($intent->amount, 0, ',', '.') }}₫</strong></div>
            <div class="mt-3 flex justify-between gap-4 text-sm"><span class="text-slate-500">Mã thanh toán</span><strong class="font-mono">{{ $intent->description }}</strong></div>
            <div class="mt-3 flex justify-between gap-4 text-sm"><span class="text-slate-500">Trạng thái</span><strong data-payment-status-label>{{ $intent->status === 'expired' ? 'Liên kết đã hết hạn' : ($intent->status === 'cancelled' ? 'Đã hủy' : 'Đang kiểm tra giao dịch') }}</strong></div>
            @if ($isMockPayment)<p class="mt-3 rounded-lg bg-amber-50 p-3 text-xs leading-5 text-amber-900">Mã tham chiếu demo: <strong class="font-mono">{{ $intent->reference ?: 'Chưa phát sinh' }}</strong>. Mã này không đại diện cho giao dịch ngân hàng.</p>@endif
        </div>

        <div class="mt-7 flex flex-wrap justify-center gap-3">
            @if ($intent->checkout_url && $intent->status === 'pending')
                <a href="{{ $intent->checkout_url }}" class="rounded-lg bg-slate-950 px-5 py-3 text-sm font-bold text-white hover:bg-slate-800">{{ $isMockPayment ? 'Mở lại trang mô phỏng' : 'Mở lại trang QR PayOS' }}</a>
            @endif
            @if ($isMockPayment && in_array($intent->status, ['cancelled', 'expired'], true))<a href="{{ route('checkout.create') }}" class="rounded-lg bg-emerald-950 px-5 py-3 text-sm font-bold text-white">Quay lại thanh toán</a>@endif
            <a href="{{ route('home') }}" class="rounded-lg border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Về trang chủ</a>
        </div>
        <p class="mt-5 text-xs text-slate-400">Trang này tự kiểm tra trạng thái từ hệ thống mỗi vài giây.</p>
    </section>
</main>
<script>
    (() => {
        const panel = document.querySelector('[data-payos-status]');

        if (!panel) {
            return;
        }

        const statusLabel = panel.querySelector('[data-payment-status-label]');
        const poll = async () => {
            try {
                const response = await fetch(panel.dataset.statusUrl, { headers: { Accept: 'application/json' } });
                const status = await response.json();

                if (status.status === 'paid' && status.redirect_url) {
                    window.location.assign(status.redirect_url);
                    return;
                }

                if (statusLabel && ['expired', 'cancelled'].includes(status.status)) {
                    statusLabel.textContent = status.status === 'expired' ? 'Liên kết đã hết hạn' : 'Đã hủy';
                    return;
                }
            } catch (error) {
                console.warn('Không kiểm tra được trạng thái thanh toán.', error);
            }

            window.setTimeout(poll, 3000);
        };

        window.setTimeout(poll, 1500);
    })();
</script>
@endsection
