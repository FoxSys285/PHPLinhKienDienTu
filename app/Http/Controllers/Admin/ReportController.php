<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $fromDate = $filters['from'] ?? $filters['to'] ?? now()->startOfMonth()->toDateString();
        $from = Carbon::parse($fromDate)->startOfDay();
        $to = Carbon::parse($filters['to'] ?? now()->toDateString())->endOfDay();

        if ($to->lt($from)) {
            $to = $from->copy()->endOfDay();
        }

        $paidOrders = Order::query()
            ->where('status', 'completed')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to]);

        $dailyRevenue = (clone $paidOrders)
            ->selectRaw('DATE(created_at) as period, SUM(subtotal - discount_amount) as revenue')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('period')
            ->get();
        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$from, $to])
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as units_sold, SUM(order_items.line_total) as revenue')
            ->groupBy('order_items.product_name')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();

        return view('layouts.pages.admin.thongkebaocao', [
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'revenue' => (clone $paidOrders)->selectRaw('SUM(subtotal - discount_amount) as revenue')->value('revenue') ?? 0,
            'completedOrders' => (clone $paidOrders)->count(),
            'unitsSold' => OrderItem::query()->whereIn('order_id', (clone $paidOrders)->select('id'))->sum('quantity'),
            'dailyRevenue' => $dailyRevenue,
            'topProducts' => $topProducts,
            'pendingOrders' => Order::query()->whereIn('status', ['pending', 'confirmed', 'shipping'])->count(),
        ]);
    }
}
