<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    private const STATUSES = ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'];

    private const PAYMENT_STATUSES = ['unpaid', 'awaiting_transfer', 'paid'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:pending,confirmed,shipping,completed,cancelled'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $orders = Order::query()
            ->when($filters['q'] ?? null, function ($query, string $term): void {
                $query->where(function ($query) use ($term): void {
                    $query->where('order_number', 'like', '%'.$term.'%')
                        ->orWhere('customer_name', 'like', '%'.$term.'%')
                        ->orWhere('customer_phone', 'like', '%'.$term.'%')
                        ->orWhere('customer_email', 'like', '%'.$term.'%');
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['from'] ?? null, fn ($query, string $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, string $to) => $query->whereDate('created_at', '<=', $to))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('layouts.pages.admin.quanlydonhang', [
            'orders' => $orders,
            'statuses' => self::STATUSES,
            'filters' => $filters,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items', 'user', 'voucher']);

        return view('layouts.pages.admin.chitietdonhang', [
            'order' => $order,
            'statuses' => self::STATUSES,
            'paymentStatuses' => self::PAYMENT_STATUSES,
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,shipping,completed,cancelled'],
            'payment_status' => ['nullable', 'in:unpaid,awaiting_transfer,paid'],
        ]);

        DB::transaction(function () use ($order, $validated): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $currentStatus = $lockedOrder->status;
            $nextStatus = $validated['status'];
            $allowedTransitions = [
                'pending' => ['confirmed', 'cancelled'],
                'confirmed' => ['shipping', 'cancelled'],
                'shipping' => ['completed'],
                'completed' => [],
                'cancelled' => [],
            ];

            if ($nextStatus !== $currentStatus && ! in_array($nextStatus, $allowedTransitions[$currentStatus] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Không thể chuyển đơn hàng từ trạng thái hiện tại sang trạng thái đã chọn.',
                ]);
            }

            if ($nextStatus === 'cancelled' && $currentStatus !== 'cancelled') {
                $items = $lockedOrder->items()->get();

                foreach ($items as $item) {
                    if ($item->product_id === null) {
                        continue;
                    }

                    $product = Product::query()->lockForUpdate()->find($item->product_id);

                    if ($product !== null) {
                        $product->increment('stock', $item->quantity);
                        $product->update([
                            'sold_count' => max(0, $product->sold_count - $item->quantity),
                        ]);
                    }
                }

                if ($lockedOrder->voucher_id !== null) {
                    Voucher::query()->whereKey($lockedOrder->voucher_id)->where('used_count', '>', 0)->decrement('used_count');
                }
            }

            $paymentStatus = $validated['payment_status'] ?? $lockedOrder->payment_status;

            if ($nextStatus === 'completed' && $lockedOrder->payment_method === 'cod') {
                $paymentStatus = 'paid';
            }

            if ($nextStatus === 'completed' && $lockedOrder->payment_method === 'bank_transfer' && $paymentStatus !== 'paid') {
                throw ValidationException::withMessages([
                    'payment_status' => 'Xác nhận đã thanh toán trước khi hoàn thành đơn chuyển khoản.',
                ]);
            }

            $lockedOrder->update([
                'status' => $nextStatus,
                'payment_status' => $paymentStatus,
            ]);
        });

        return redirect()->route('admin.orders.show', $order)->with('status', 'Đã cập nhật đơn hàng.');
    }
}
