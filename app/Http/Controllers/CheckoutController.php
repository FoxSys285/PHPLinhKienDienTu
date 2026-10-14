<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $cart = $this->currentCart($request);
        $requestedIds = $request->old(
            'selected',
            $request->query('selected', $request->session()->get('checkout.selected', [])),
        );
        $selectedIds = is_array($requestedIds)
            ? array_values(array_filter(array_map('intval', $requestedIds), fn (int $id): bool => $id > 0))
            : [];

        $cartItemsQuery = $cart->items()->with('product.category');

        if ($selectedIds !== []) {
            $cartItemsQuery->whereIn('product_id', $selectedIds);
        }

        $cartItems = $cartItemsQuery->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('checkout_error', 'Thêm sản phẩm vào giỏ trước khi thanh toán.');
        }

        $subtotal = $cartItems->sum(fn ($item): float => (float) $item->product->price * $item->quantity);
        $shippingFee = $subtotal >= 500000 ? 0 : 30000;

        return view('layouts.pages.user.thanhtoan', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'shippingFee' => $shippingFee,
            'total' => $subtotal + $shippingFee,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['required', 'integer', 'distinct'],
            'quantities' => ['nullable', 'array'],
            'quantities.*' => ['required', 'integer', 'min:1', 'max:99'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'min:9', 'max:40'],
            'shipping_address' => ['required', 'string', 'min:10', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'in:cod,bank_transfer'],
        ], [
            'selected.required' => 'Vui lòng chọn ít nhất một sản phẩm để thanh toán.',
            'selected.min' => 'Vui lòng chọn ít nhất một sản phẩm để thanh toán.',
        ]);

        $cart = $this->currentCart($request);
        $selectedIds = array_map('intval', $validated['selected']);
        $quantities = $validated['quantities'] ?? [];

        $order = DB::transaction(function () use ($cart, $request, $validated, $selectedIds, $quantities): Order {
            $cartItems = $cart->items()
                ->whereIn('product_id', $selectedIds)
                ->lockForUpdate()
                ->get();

            if ($cartItems->count() !== count($selectedIds)) {
                throw ValidationException::withMessages([
                    'selected' => 'Một số sản phẩm không còn trong giỏ hàng. Vui lòng kiểm tra lại.',
                ]);
            }

            $products = Product::query()
                ->whereKey($selectedIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines = [];
            $subtotal = 0.0;

            foreach ($cartItems as $cartItem) {
                $product = $products->get($cartItem->product_id);
                $quantity = (int) ($quantities[$cartItem->product_id] ?? $cartItem->quantity);

                if (! $product || ! $product->is_active || $quantity > $product->stock) {
                    throw ValidationException::withMessages([
                        'selected' => 'Sản phẩm không còn đủ số lượng. Vui lòng cập nhật giỏ hàng.',
                    ]);
                }

                $unitPrice = (float) $product->price;
                $lineTotal = round($unitPrice * $quantity, 2);
                $subtotal += $lineTotal;
                $lines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $shippingFee = $subtotal >= 500000 ? 0 : 30000;
            $order = Order::query()->create([
                'user_id' => $request->user()?->id,
                'order_number' => 'MV'.Str::upper((string) Str::ulid()),
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'shipping_address' => $validated['shipping_address'],
                'note' => $validated['note'] ?? null,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total' => $subtotal + $shippingFee,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_method'] === 'cod' ? 'unpaid' : 'awaiting_transfer',
                'status' => 'pending',
            ]);

            foreach ($lines as $line) {
                $product = $line['product'];
                $quantity = $line['quantity'];

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_price' => $line['unit_price'],
                    'quantity' => $quantity,
                    'line_total' => $line['line_total'],
                ]);

                $product->decrement('stock', $quantity);
                $product->increment('sold_count', $quantity);
            }

            $cart->items()->whereIn('product_id', $selectedIds)->delete();

            return $order;
        });

        $request->session()->forget('checkout.selected');

        return redirect()->route('orders.success', ['order' => $order->order_number]);
    }

    public function start(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['required', 'integer', 'distinct'],
            'quantities' => ['required', 'array'],
            'quantities.*' => ['required', 'integer', 'min:1', 'max:99'],
        ], [
            'selected.required' => 'Vui lòng chọn ít nhất một sản phẩm để thanh toán.',
            'selected.min' => 'Vui lòng chọn ít nhất một sản phẩm để thanh toán.',
        ]);

        $cart = $this->currentCart($request);
        $selectedIds = array_map('intval', $validated['selected']);
        $items = $cart->items()->with('product')->whereIn('product_id', $selectedIds)->get();

        if ($items->count() !== count($selectedIds)) {
            return redirect()->route('cart.index')->withErrors([
                'selected' => 'Một số sản phẩm không còn trong giỏ hàng. Vui lòng kiểm tra lại.',
            ]);
        }

        foreach ($items as $item) {
            $quantity = (int) ($validated['quantities'][$item->product_id] ?? $item->quantity);

            if ($quantity > $item->product->stock) {
                return redirect()->route('cart.index')->withErrors([
                    'quantities.'.$item->product_id => 'Số lượng vượt quá tồn kho của sản phẩm.',
                ]);
            }

            $item->update(['quantity' => $quantity]);
        }

        $request->session()->put('checkout.selected', $selectedIds);

        return redirect()->route('checkout.index');
    }

    public function success(Order $order): View
    {
        return view('layouts.pages.user.donhangdatthanhcong', [
            'order' => $order,
        ]);
    }

    private function currentCart(Request $request): Cart
    {
        return Cart::query()->firstOrCreate(
            ['session_id' => $request->session()->getId()],
            ['user_id' => $request->user()?->id],
        );
    }
}
