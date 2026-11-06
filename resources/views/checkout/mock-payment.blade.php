@extends('layouts.store')
@section('title', 'Thanh toán mô phỏng')
@section('content')
<main class="mx-auto max-w-2xl px-4 py-12 sm:py-16">
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-900/5">
        <div class="bg-emerald-950 px-6 py-7 text-white sm:px-9">
            <p class="text-xs font-bold uppercase tracking-[.2em] text-emerald-200">Môi trường đồ án</p>
            <h1 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">Mô phỏng thanh toán online</h1>
            <p class="mt-2 max-w-lg text-sm leading-6 text-emerald-100">Chọn kết quả giao dịch để đi tiếp trong quy trình. Trang này không kết nối ngân hàng và không thu tiền thật.</p>
        </div>

        <div class="p-6 sm:p-9">
            <dl class="grid gap-4 rounded-2xl bg-slate-50 p-5 sm:grid-cols-2">
                <div><dt class="text-xs text-slate-500">Mã thanh toán</dt><dd class="mt-1 font-mono text-sm font-bold text-slate-950">{{ $intent->description }}</dd></div>
                <div><dt class="text-xs text-slate-500">Số tiền mô phỏng</dt><dd class="mt-1 text-lg font-black text-slate-950">{{ number_format((float) $intent->amount, 0, ',', '.') }}₫</dd></div>
                <div class="sm:col-span-2"><dt class="text-xs text-slate-500">Tham chiếu demo</dt><dd class="mt-1 font-mono text-xs text-slate-700">DEMO-{{ $intent->order_code }}</dd></div>
            </dl>

            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-950">
                Chỉ nhấn thành công để tạo đơn hàng đã thanh toán trong hệ thống. Nếu chọn thất bại, đơn hàng sẽ không được tạo và phần hàng đã giữ chỗ sẽ được giải phóng.
            </div>

            <form method="POST" action="{{ route('checkout.mock-payment.complete', ['token' => $intent->public_token]) }}" class="mt-6 grid gap-3 sm:grid-cols-2">
                @csrf
                <button name="outcome" value="success" class="rounded-xl bg-emerald-800 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-emerald-900">Mô phỏng giao dịch thành công</button>
                <button name="outcome" value="failed" class="rounded-xl border border-rose-200 bg-white px-5 py-3.5 text-sm font-bold text-rose-800 transition hover:bg-rose-50">Mô phỏng giao dịch thất bại</button>
            </form>

            <p class="mt-5 text-center text-xs text-slate-500">Liên kết demo có thời hạn. Nếu hết hạn, hãy quay lại checkout để tạo giao dịch mới.</p>
        </div>
    </section>
</main>
@endsection
