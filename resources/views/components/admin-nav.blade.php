@php
    $navigationItems = [
        ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'label' => 'Tổng quan', 'roles' => ['admin', 'sales', 'warehouse', 'accountant'], 'icon' => 'dashboard'],
        ['route' => 'admin.orders.index', 'active' => 'admin.orders.*', 'label' => 'Đơn hàng', 'roles' => ['admin', 'sales', 'warehouse', 'accountant'], 'icon' => 'orders'],
        ['route' => 'admin.products.index', 'active' => 'admin.products.*', 'label' => 'Sản phẩm', 'roles' => ['admin', 'sales'], 'icon' => 'products'],
        ['route' => 'admin.inventory.index', 'active' => 'admin.inventory.*', 'label' => 'Kho & kiểm kê', 'roles' => ['admin', 'warehouse'], 'icon' => 'inventory'],
        ['route' => 'admin.users.index', 'active' => 'admin.users.*', 'label' => 'Khách hàng & quyền', 'roles' => ['admin'], 'icon' => 'users'],
        ['route' => 'admin.audit.index', 'active' => 'admin.audit.*', 'label' => 'Nhật ký', 'roles' => ['admin'], 'icon' => 'audit'],
    ];
@endphp

<nav data-admin-navigation aria-label="Điều hướng quản trị" data-guide-target="admin-navigation" class="flex gap-2 overflow-x-auto pb-1 md:flex-col md:overflow-visible md:pb-0">
    @foreach ($navigationItems as $item)
        @continue(! in_array(auth()->user()->role, $item['roles'], true))
        <a href="{{ route($item['route']) }}" @class([
            'flex min-h-11 shrink-0 items-center gap-3 rounded-2xl px-3.5 py-2.5 text-sm font-semibold transition md:w-full',
            'bg-white/75 text-[#172554] shadow-sm ring-1 ring-white' => request()->routeIs($item['active']),
            'text-slate-600 hover:bg-white/55 hover:text-[#172554]' => ! request()->routeIs($item['active']),
        ]) @if (request()->routeIs($item['active'])) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" class="size-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                @switch($item['icon'])
                    @case('dashboard')
                        <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>
                        @break
                    @case('orders')
                        <path d="M6 3.5h12v17l-2-1.5-2 1.5-2-1.5-2 1.5-2-1.5-2 1.5z"/><path d="M9 8h6m-6 4h6m-6 4h3"/>
                        @break
                    @case('products')
                        <path d="m4 7 8-4 8 4-8 4-8-4Z"/><path d="m4 12 8 4 8-4M4 17l8 4 8-4"/>
                        @break
                    @case('inventory')
                        <path d="M3 7h18v14H3zM7 7V4h10v3M7 11h10m-10 4h6"/>
                        @break
                    @case('users')
                        <circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M17 8h5m-2.5-2.5v5"/>
                        @break
                    @case('audit')
                        <path d="M12 3 4 6v5c0 5 3.4 8.4 8 10 4.6-1.6 8-5 8-10V6z"/><path d="m9 12 2 2 4-4"/>
                        @break
                @endswitch
            </svg>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
