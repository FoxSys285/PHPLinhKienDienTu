@extends('layouts.admin')
@section('title', 'Chương trình ưu đãi')
@section('content')
<div class="mx-auto max-w-screen-2xl">
    <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-xs font-bold uppercase tracking-[.18em] text-blue-600">Bán hàng & tiếp thị</p><h1 class="mt-2 text-3xl font-extrabold tracking-tight text-[#172554]">Chương trình ưu đãi</h1><p class="mt-2 text-sm text-slate-500">Quản lý nội dung hiển thị, điều kiện giảm giá và thời gian áp dụng.</p></div>
        <a href="{{ route('admin.promotions.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 hover:bg-blue-700"><span aria-hidden="true">＋</span> Thêm chương trình</a>
    </div>
    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ([['Tổng chương trình', $stats['total'], 'text-blue-700'], ['Đang áp dụng', $stats['active'], 'text-emerald-600'], ['Sắp diễn ra', $stats['scheduled'], 'text-violet-600'], ['Lượt đã sử dụng', $stats['uses'], 'text-orange-600']] as [$label, $value, $color])
            <div class="rounded-2xl border border-white/80 bg-white/70 p-5 shadow-sm"><p class="text-xs font-semibold text-slate-500">{{ $label }}</p><p class="mt-3 text-3xl font-extrabold {{ $color }}">{{ number_format($value) }}</p></div>
        @endforeach
    </div>
    <section class="overflow-hidden rounded-2xl border border-white/80 bg-white/80 shadow-sm">
        <form method="GET" class="flex flex-wrap items-center gap-3 border-b border-slate-100 p-4">
            <label class="min-w-48 flex-1"><span class="sr-only">Tìm tên hoặc mã ưu đãi</span><input name="q" value="{{ request('q') }}" placeholder="Tìm theo tên hoặc mã ưu đãi..." class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm"></label>
            <label><span class="sr-only">Trạng thái chương trình</span><select name="status" class="rounded-xl border-slate-200 px-3 py-2.5 text-sm">
                @foreach (['' => 'Tất cả trạng thái', 'active' => 'Đang áp dụng', 'scheduled' => 'Sắp diễn ra', 'paused' => 'Tạm dừng', 'expired' => 'Đã hết hạn', 'exhausted' => 'Hết lượt'] as $key => $label)
                    <option value="{{ $key }}" @selected(request('status', '') === $key)>{{ $label }}</option>
                @endforeach
            </select></label>
            <button class="rounded-xl bg-[#172554] px-4 py-2.5 text-sm font-bold text-white">Lọc danh sách</button>
            <a href="{{ route('admin.promotions.index') }}" class="px-2 text-xs font-semibold text-slate-500">Đặt lại</a>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[920px] text-left text-sm">
                <thead class="bg-slate-50/80 text-xs font-semibold text-slate-500"><tr><th class="px-5 py-4">Chương trình</th><th class="px-4 py-4">Mức giảm & điều kiện</th><th class="px-4 py-4">Thời gian</th><th class="px-4 py-4">Trạng thái & lượt dùng</th><th class="px-5 py-4 text-right">Thao tác</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($promotions as $promotion)
                        <tr class="hover:bg-blue-50/30">
                            <td class="max-w-80 px-5 py-4"><div class="flex items-center gap-3"><img src="{{ $promotion->image_url }}" alt="" class="h-14 w-20 shrink-0 rounded-xl object-cover"><div><a href="{{ route('admin.promotions.edit', $promotion) }}" class="font-bold text-[#172554] hover:text-blue-600">{{ $promotion->title }}</a><p class="mt-1 font-mono text-xs font-semibold text-blue-600">{{ $promotion->code }}</p><p class="mt-1 text-[10px] text-slate-400">Thứ tự: {{ $promotion->sort_order }}</p></div></div></td>
                            <td class="px-4 py-4"><strong class="text-base text-blue-700">{{ $promotion->discount_label }}</strong><p class="mt-1 text-xs text-slate-500">Mua từ {{ number_format($promotion->minimum_purchase, 0, ',', '.') }}₫</p>@if ($promotion->maximum_discount)<p class="mt-1 text-xs text-slate-500">Tối đa {{ number_format($promotion->maximum_discount, 0, ',', '.') }}₫</p>@endif<p class="mt-1 text-[10px] font-semibold text-slate-400">{{ empty($promotion->category_slugs) ? 'Toàn cửa hàng' : count($promotion->category_slugs).' danh mục áp dụng' }}</p></td>
                            <td class="px-4 py-4 text-xs leading-6 text-slate-500"><p>{{ $promotion->starts_at?->format('d/m/Y H:i') ?? 'Không giới hạn bắt đầu' }}</p><p>→ {{ $promotion->expires_at?->format('d/m/Y H:i') ?? 'Không giới hạn kết thúc' }}</p></td>
                            <td class="px-4 py-4"><span @class(['inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold', 'bg-emerald-50 text-emerald-700' => $promotion->isAvailable(), 'bg-slate-100 text-slate-500' => ! $promotion->isAvailable()])>{{ $promotion->status_label }}</span><p class="mt-2 text-xs text-slate-500">{{ $promotion->used_count }} / {{ $promotion->usage_limit ?? '∞' }} lượt</p><p class="mt-1 text-[10px] text-blue-600">{{ $promotion->is_automatic ? 'Tự động áp dụng' : 'Nhập mã thủ công' }}</p></td>
                            <td class="px-5 py-4"><div class="flex justify-end gap-3"><a href="{{ route('admin.promotions.edit', $promotion) }}" class="font-bold text-blue-600 hover:underline">Sửa</a>@if ($promotion->is_active)<form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}">@csrf @method('DELETE')<button class="text-xs font-semibold text-rose-600 hover:underline">Tạm dừng</button></form>@endif</div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-14 text-center"><strong class="text-slate-700">Chưa có chương trình phù hợp</strong><p class="mt-2 text-xs text-slate-500">Thử đổi bộ lọc hoặc thêm chương trình ưu đãi mới.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 p-4">{{ $promotions->links() }}</div>
    </section>
    <p class="mt-4 text-xs leading-6 text-slate-500">Chương trình tạm dừng, hết hạn hoặc hết lượt sẽ ngừng hiển thị và ngừng tự áp dụng. Tạm dừng giữ nguyên lịch sử ưu đãi của các đơn hàng cũ.</p>
</div>
@endsection
