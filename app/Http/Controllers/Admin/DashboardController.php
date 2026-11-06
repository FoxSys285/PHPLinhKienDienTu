<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'ordersToday' => Order::query()->whereDate('created_at', today())->count(),
            'pendingOrders' => Order::query()->whereIn('status', ['pending_confirmation', 'pending_payment'])->count(),
            'revenueThisMonth' => Order::query()->where('status', 'completed')->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('total'),
            'lowStock' => Product::query()->where('is_active', true)->whereColumn('stock', '<=', 'minimum_stock')->count(),
            'recentOrders' => Order::query()->latest()->take(8)->get(),
            'lowStockProducts' => Product::query()->where('is_active', true)->whereColumn('stock', '<=', 'minimum_stock')->orderBy('stock')->take(6)->get(),
        ]);
    }
}
