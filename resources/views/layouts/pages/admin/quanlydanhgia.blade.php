@extends('layouts.admin')

@section('title', 'Quản lý đánh giá')

@section('content')
    <div class="admin-page-heading"><div><span class="admin-eyebrow">NỘI DUNG CỬA HÀNG</span><h1>Quản lý đánh giá</h1><p>Duyệt nhận xét trước khi hiển thị trên trang sản phẩm.</p></div></div>
    <section class="admin-panel">
        <form class="admin-toolbar" method="get" action="{{ route('admin.reviews.index') }}">
            <div class="admin-field" style="flex:1 1 240px"><label for="reviewSearch">Tìm đánh giá</label><input id="reviewSearch" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tên khách, sản phẩm hoặc nội dung"></div>
            <div class="admin-field"><label for="reviewStatus">Trạng thái</label><select id="reviewStatus" name="status"><option value="">Tất cả</option><option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Chờ duyệt</option><option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Đã duyệt</option></select></div>
            <div class="admin-field"><label for="reviewRating">Số sao</label><select id="reviewRating" name="rating"><option value="">Tất cả</option>@foreach (range(5, 1) as $rating)<option value="{{ $rating }}" @selected((string) ($filters['rating'] ?? '') === (string) $rating)>{{ $rating }} sao</option>@endforeach</select></div>
            <button class="admin-button" type="submit">Lọc đánh giá</button>
            @if (count($filters))<a class="admin-button-secondary" href="{{ route('admin.reviews.index') }}">Xóa lọc</a>@endif
        </form>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Sản phẩm</th><th>Người đánh giá</th><th>Đánh giá</th><th>Nội dung</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
            @forelse ($reviews as $review)
                <tr>
                    <td><strong>{{ $review->product?->name ?? 'Sản phẩm đã xóa' }}</strong></td>
                    <td>{{ $review->customer_name }}<small>{{ $review->created_at?->format('d/m/Y') }}</small></td>
                    <td><strong style="color:#d89421">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</strong></td>
                    <td style="white-space:normal;min-width:220px;max-width:420px">{{ $review->content }}</td>
                    <td><span class="admin-status {{ $review->is_approved ? 'admin-status-success' : 'admin-status-warning' }}">{{ $review->is_approved ? 'Đã duyệt' : 'Chờ duyệt' }}</span></td>
                    <td><div class="admin-table-actions">
                        @unless ($review->is_approved)<form class="admin-inline-form" method="post" action="{{ route('admin.reviews.approve', $review) }}">@csrf @method('PATCH')<button class="admin-button" type="submit">Duyệt</button></form>@endunless
                        <form class="admin-inline-form" method="post" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Xóa đánh giá này?')">@csrf @method('DELETE')<button class="admin-button-danger" type="submit">Xóa</button></form>
                    </div></td>
                </tr>
            @empty
                <tr><td class="admin-empty" colspan="6">Chưa có đánh giá phù hợp.</td></tr>
            @endforelse
        </tbody></table></div>
        @if ($reviews->hasPages())<div class="admin-pagination">{{ $reviews->links() }}</div>@endif
    </section>
@endsection
