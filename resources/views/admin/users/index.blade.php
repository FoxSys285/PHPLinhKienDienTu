@extends('layouts.admin')
@section('title', 'Tài khoản & phân quyền')
@section('content')
@php
    $roleLabels = [
        'customer' => 'Khách hàng',
        'admin' => 'Quản trị viên',
        'sales' => 'Kinh doanh',
        'warehouse' => 'Kho vận',
        'accountant' => 'Kế toán',
    ];
    $summaryCards = [
        ['label' => 'Tổng tài khoản', 'value' => $stats['total'], 'note' => 'Khách hàng và nhân sự', 'tone' => 'text-blue-700', 'icon' => 'TK'],
        ['label' => 'Khách hàng', 'value' => $stats['customers'], 'note' => 'Có thể mua hàng và theo dõi đơn', 'tone' => 'text-violet-600', 'icon' => 'KH'],
        ['label' => 'Nhân sự nội bộ', 'value' => $stats['staff'], 'note' => 'Có quyền vận hành cửa hàng', 'tone' => 'text-emerald-600', 'icon' => 'NV'],
        ['label' => 'Quản trị viên', 'value' => $stats['administrators'], 'note' => 'Quản lý quyền hệ thống', 'tone' => 'text-amber-600', 'icon' => 'AD'],
    ];
@endphp

<div class="mx-auto max-w-screen-2xl">
    <div class="mb-7">
        <p class="text-xs font-bold uppercase tracking-[.18em] text-blue-600">Tài khoản & chăm sóc khách hàng</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-[#172554] sm:text-4xl">Khách hàng & phân quyền</h1>
        <p class="mt-2 text-sm text-slate-500">Quản lý tài khoản, phân công vai trò và kiểm soát quyền truy cập nội bộ.</p>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach ($summaryCards as $card)
            <div class="group relative overflow-hidden rounded-2xl border border-white/80 bg-white/75 p-5 shadow-[0_14px_36px_-28px_rgba(23,37,84,.4)] backdrop-blur transition hover:-translate-y-0.5 hover:shadow-lg">
                <span class="absolute -right-5 -top-7 size-24 rounded-full bg-blue-50/80 transition group-hover:scale-125"></span>
                <div class="relative flex items-start justify-between gap-3"><div><p class="text-xs font-semibold text-slate-500">{{ $card['label'] }}</p><p class="mt-3 text-3xl font-extrabold tracking-tight {{ $card['tone'] }}">{{ number_format($card['value']) }}</p><p class="mt-1 text-[11px] text-slate-400">{{ $card['note'] }}</p></div><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-xs font-black text-blue-700">{{ $card['icon'] }}</span></div>
            </div>
        @endforeach
    </div>

    <section class="overflow-hidden rounded-2xl border border-white/80 bg-white/80 shadow-sm">
        <form method="GET" class="flex flex-wrap items-center gap-3 border-b border-slate-100 p-4">
            <label class="min-w-48 flex-1"><span class="sr-only">Tìm tài khoản</span><input name="q" value="{{ request('q') }}" placeholder="Tìm theo tên hoặc email..." class="w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm"></label>
            <span class="mr-1 text-xs text-slate-400">{{ number_format($users->total()) }} tài khoản</span>
            <button class="rounded-xl bg-[#172554] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-900">Tìm tài khoản</button>
            @if (request()->filled('q'))<a href="{{ route('admin.users.index') }}" class="px-2 text-xs font-semibold text-slate-500 hover:text-blue-700">Đặt lại</a>@endif
        </form>
        <div class="divide-y divide-slate-100">
            @forelse ($users as $user)
                <article class="grid gap-5 p-5 transition hover:bg-blue-50/20 xl:grid-cols-[minmax(260px,.9fr)_minmax(300px,.8fr)] xl:items-center">
                    <div class="min-w-0">
                        <div class="flex items-center gap-3">
                            <span class="grid size-12 shrink-0 place-items-center rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-100 text-sm font-black text-[#172554]">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                            <div class="min-w-0"><strong class="block truncate text-sm font-extrabold text-[#172554]">{{ $user->name }}</strong><span class="mt-1 block truncate text-xs text-slate-500">{{ $user->email }}</span>@if ($user->phone)<span class="mt-0.5 block text-[11px] text-slate-400">{{ $user->phone }}</span>@endif</div>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-2"><span @class(['rounded-full px-2.5 py-1 text-[10px] font-bold', 'bg-blue-50 text-blue-700' => $user->role === 'customer', 'bg-violet-50 text-violet-700' => $user->role === 'admin', 'bg-slate-100 text-slate-600' => ! in_array($user->role, ['customer', 'admin'], true)])>{{ $roleLabels[$user->role] ?? $user->role }}</span><span class="text-[10px] text-slate-400">Tham gia {{ $user->created_at->format('d/m/Y') }}</span></div>
                    </div>

                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid gap-3 rounded-2xl border border-slate-100 bg-slate-50/80 p-4 sm:grid-cols-[minmax(180px,1fr)_auto] sm:items-end">
                        @csrf
                        @method('PATCH')
                        <label class="grid gap-1.5 text-[11px] font-bold text-slate-500">Vai trò truy cập
                            <select name="role" class="rounded-xl border-slate-200 bg-white text-xs" @disabled($user->id === auth()->id())>
                                @foreach ($roleLabels as $value => $label)<option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>@endforeach
                            </select>
                            @if ($user->id === auth()->id())<span class="text-[10px] font-medium text-slate-400">Không thể tự đổi vai trò tài khoản đang đăng nhập.</span>@endif
                        </label>
                        <button class="rounded-xl bg-[#172554] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-blue-900">Lưu vai trò</button>
                    </form>
                </article>
            @empty
                <div class="px-6 py-16 text-center"><span class="mx-auto grid size-12 place-items-center rounded-2xl bg-blue-50 text-xl text-blue-600">⌕</span><strong class="mt-4 block text-sm text-[#172554]">Không tìm thấy tài khoản phù hợp</strong><p class="mt-1 text-xs text-slate-500">Thử tìm theo tên hoặc email.</p><a href="{{ route('admin.users.index') }}" class="mt-3 inline-flex text-xs font-bold text-blue-700 hover:underline">Xóa bộ lọc</a></div>
            @endforelse
        </div>
        <div class="border-t border-slate-100 p-4">{{ $users->links() }}</div>
    </section>
</div>
@endsection
