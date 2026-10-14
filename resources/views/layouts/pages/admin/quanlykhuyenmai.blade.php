@extends('layouts.admin')

@section('title', 'Quản lý khuyến mãi')

@section('content')
    <div class="admin-page-heading"><div><span class="admin-eyebrow">ƯU ĐÃI</span><h1>Quản lý khuyến mãi</h1><p>Tạo mã giảm giá, đặt điều kiện và theo dõi lượt sử dụng.</p></div><a class="admin-button" href="{{ route('admin.vouchers.create') }}"><span aria-hidden="true">＋</span> Tạo mã mới</a></div>
    <section class="admin-panel">
        <form class="admin-toolbar" method="get" action="{{ route('admin.vouchers.index') }}">
            <div class="admin-field" style="flex:1 1 230px"><label for="voucherSearch">Tìm mã</label><input id="voucherSearch" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Ví dụ: LINHKIEN10"></div>
            <div class="admin-field"><label for="voucherStatus">Trạng thái</label><select id="voucherStatus" name="status"><option value="">Tất cả</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Đang bật</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Đã tắt</option></select></div>
            <button class="admin-button" type="submit">Lọc mã</button>
            @if (count($filters))<a class="admin-button-secondary" href="{{ route('admin.vouchers.index') }}">Xóa lọc</a>@endif
        </form>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Mã</th><th>Mức giảm</th><th>Đơn tối thiểu</th><th>Thời hạn</th><th>Lượt dùng</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
            @forelse ($vouchers as $voucher)
                @php
                    $isAvailable = $voucher->is_active
                        && ($voucher->starts_at === null || $voucher->starts_at->isPast())
                        && ($voucher->ends_at === null || $voucher->ends_at->isFuture())
                        && ($voucher->usage_limit === null || $voucher->used_count < $voucher->usage_limit);
                @endphp
                <tr>
                    <td><strong>{{ $voucher->code }}</strong></td>
                    <td>{{ $voucher->discount_type === 'percent' ? number_format((float) $voucher->discount_value, 0).'%' : number_format((float) $voucher->discount_value, 0, ',', '.').'₫' }}@if ($voucher->max_discount)<small>Tối đa {{ number_format((float) $voucher->max_discount, 0, ',', '.') }}₫</small>@endif</td>
                    <td>{{ number_format((float) $voucher->minimum_order, 0, ',', '.') }}₫</td>
                    <td>{{ $voucher->starts_at?->format('d/m/Y') ?? 'Không giới hạn' }}<small>đến {{ $voucher->ends_at?->format('d/m/Y') ?? 'Không giới hạn' }}</small></td>
                    <td>{{ $voucher->used_count }} / {{ $voucher->usage_limit ?? '∞' }}</td>
                    <td><span class="admin-status {{ $isAvailable ? 'admin-status-success' : 'admin-status-warning' }}">{{ $isAvailable ? 'Đang áp dụng' : ($voucher->is_active ? 'Chưa khả dụng' : 'Đã tắt') }}</span></td>
                    <td><div class="admin-table-actions"><a class="admin-button-link" href="{{ route('admin.vouchers.edit', $voucher) }}">Sửa</a><form class="admin-inline-form" method="post" action="{{ route('admin.vouchers.destroy', $voucher) }}" onsubmit="return confirm('Xóa mã {{ $voucher->code }}?')">@csrf @method('DELETE')<button class="admin-button-danger" type="submit">Xóa</button></form></div></td>
                </tr>
            @empty
                <tr><td class="admin-empty" colspan="7">Chưa có mã khuyến mãi.</td></tr>
            @endforelse
        </tbody></table></div>
        @if ($vouchers->hasPages())<div class="admin-pagination">{{ $vouchers->links() }}</div>@endif
    </section>
@endsection
