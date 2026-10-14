@extends('layouts.admin')

@section('title', 'Quản lý người dùng')

@section('content')
    <div class="admin-page-heading"><div><span class="admin-eyebrow">TÀI KHOẢN</span><h1>Quản lý người dùng</h1><p>Xem hồ sơ, lịch sử mua hàng và khóa hoặc mở tài khoản khách hàng.</p></div></div>
    <section class="admin-panel">
        <form class="admin-toolbar" method="get" action="{{ route('admin.users.index') }}">
            <div class="admin-field" style="flex:1 1 250px"><label for="userSearch">Tìm người dùng</label><input id="userSearch" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Họ tên hoặc email"></div>
            <div class="admin-field"><label for="userStatus">Trạng thái</label><select id="userStatus" name="status"><option value="">Tất cả</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Đang hoạt động</option><option value="blocked" @selected(($filters['status'] ?? '') === 'blocked')>Đã khóa</option></select></div>
            <button class="admin-button" type="submit">Tìm kiếm</button>
            @if (count($filters))<a class="admin-button-secondary" href="{{ route('admin.users.index') }}">Xóa lọc</a>@endif
        </form>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Người dùng</th><th>Ngày đăng ký</th><th>Số đơn</th><th>Trạng thái</th><th>Lý do khóa</th><th>Thao tác</th></tr></thead><tbody>
            @forelse ($users as $user)
                <tr>
                    <td><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></td>
                    <td>{{ $user->created_at?->format('d/m/Y') }}</td>
                    <td>{{ $user->orders_count }}</td>
                    <td><span class="admin-status {{ $user->is_blocked ? 'admin-status-danger' : 'admin-status-success' }}">{{ $user->is_blocked ? 'Đã khóa' : 'Hoạt động' }}</span></td>
                    <td>{{ $user->blocked_reason ?: '—' }}</td>
                    <td><div class="admin-table-actions"><a class="admin-button-link" href="{{ route('admin.users.show', $user) }}">Xem</a>
                        <form class="admin-inline-form" method="post" action="{{ route('admin.users.status', $user) }}" onsubmit="return confirm('{{ $user->is_blocked ? 'Mở khóa tài khoản này?' : 'Khóa tài khoản này?' }}')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="is_blocked" value="{{ $user->is_blocked ? 0 : 1 }}">
                            @if (! $user->is_blocked)<input type="hidden" name="blocked_reason" value="Admin khóa tài khoản">@endif
                            <button class="{{ $user->is_blocked ? 'admin-button-secondary' : 'admin-button-danger' }}" type="submit">{{ $user->is_blocked ? 'Mở khóa' : 'Khóa' }}</button>
                        </form>
                    </div></td>
                </tr>
            @empty
                <tr><td class="admin-empty" colspan="6">Không tìm thấy tài khoản phù hợp.</td></tr>
            @endforelse
        </tbody></table></div>
        @if ($users->hasPages())<div class="admin-pagination">{{ $users->links() }}</div>@endif
    </section>
@endsection
