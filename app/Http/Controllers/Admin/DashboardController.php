<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'orders_today' => Order::query()->whereDate('created_at', today())->count(),
            'pending_orders' => Order::query()->whereIn('status', ['pending', 'confirmed', 'shipping'])->count(),
            'customers' => User::query()->where('role', '!=', 'Admin')->count(),
            'pending_reviews' => Review::query()->where('is_approved', false)->count(),
            'revenue' => Order::query()
                ->where('status', 'completed')
                ->where('payment_status', 'paid')
                ->selectRaw('SUM(subtotal - discount_amount) as revenue')
                ->value('revenue') ?? 0,
        ];

        return view('layouts.pages.admin.dashboard', [
            'stats' => $stats,
            'lowStockProducts' => Product::query()->where('is_active', true)->where('stock', '<=', 5)->orderBy('stock')->limit(5)->get(),
            'latestOrders' => Order::query()->latest()->limit(8)->get(),
        ]);
    }
}
