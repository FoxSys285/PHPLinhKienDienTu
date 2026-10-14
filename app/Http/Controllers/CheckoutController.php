<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Voucher;
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

        $cartItemsQuery = $cart->items()->with('product.category')->whereHas('product');

        if ($selectedIds !== []) {
            $cartItemsQuery->whereIn('product_id', $selectedIds);
        }

        $cartItems = $cartItemsQuery->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('checkout_error', 'Thêm sản phẩm vào giỏ trước khi thanh toán.');
        }

        $subtotal = $cartItems->sum(fn ($item): float => (float) $item->product->price * $item->quantity);
        $shippingFee = $subtotal >= 500000 ? 0 : 30000;
        $voucher = null;
        $discountAmount = 0.0;
        $voucherCode = $request->session()->get('checkout.voucher_code');

        if (is_string($voucherCode) && $voucherCode !== '') {
            $voucher = Voucher::query()->where('code', $voucherCode)->first();

            if ($voucher !== null && $this->voucherIsEligible($voucher, (float) $subtotal)) {
                $discountAmount = $this->voucherDiscount($voucher, (float) $subtotal);
            } else {
                $request->session()->forget('checkout.voucher_code');
                $voucher = null;
                session()->flash('checkout_error', 'Mã khuyến mãi không còn đủ điều kiện áp dụng.');
            }
        }

        return view('layouts.pages.user.thanhtoan', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'shippingFee' => $shippingFee,
            'voucher' => $voucher,
            'discountAmount' => $discountAmount,
            'total' => $subtotal + $shippingFee - $discountAmount,
        ]);
    }

    public function applyVoucher(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);
        $code = Str::upper(trim($validated['code']));
        $cart = $this->currentCart($request);
        $selectedIds = $request->session()->get('checkout.selected', []);
        $items = $cart->items()->with('product')->whereHas('product')->when($selectedIds !== [], fn ($query) => $query->whereIn('product_id', $selectedIds))->get();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['code' => 'Giỏ hàng đang trống.']);
        }

        $subtotal = $items->sum(fn ($item): float => (float) $item->product->price * $item->quantity);
        $voucher = Voucher::query()->where('code', $code)->first();

        if ($voucher === null || ! $this->voucherIsEligible($voucher, (float) $subtotal)) {
            return back()->withErrors(['code' => 'Mã không hợp lệ, đã hết hạn hoặc chưa đủ điều kiện sử dụng.']);
        }

        $request->session()->put('checkout.voucher_code', $voucher->code);

        return back()->with('status', 'Đã áp dụng mã '.$voucher->code.'.');
    }

    public function removeVoucher(Request $request): RedirectResponse
    {
        $request->session()->forget('checkout.voucher_code');

        return back()->with('status', 'Đã gỡ mã khuyến mãi.');
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

        $voucherCode = $request->session()->get('checkout.voucher_code');

        $order = DB::transaction(function () use ($cart, $request, $validated, $selectedIds, $quantities, $voucherCode): Order {
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
            $voucher = null;
            $discountAmount = 0.0;

            if (is_string($voucherCode) && $voucherCode !== '') {
                $voucher = Voucher::query()->where('code', $voucherCode)->lockForUpdate()->first();

                if ($voucher === null || ! $this->voucherIsEligible($voucher, $subtotal)) {
                    throw ValidationException::withMessages([
                        'code' => 'Mã khuyến mãi không còn đủ điều kiện. Vui lòng kiểm tra lại đơn hàng.',
                    ]);
                }

                $discountAmount = $this->voucherDiscount($voucher, $subtotal);
            }

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
                'discount_amount' => $discountAmount,
                'voucher_id' => $voucher?->id,
                'voucher_code' => $voucher?->code,
                'total' => $subtotal + $shippingFee - $discountAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_method'] === 'cod' ? 'unpaid' : 'awaiting_transfer',
                'status' => 'pending',
            ]);

            if ($voucher !== null) {
                $voucher->increment('used_count');
            }

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
        $request->session()->forget('checkout.voucher_code');

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
        $items = $cart->items()->with('product')->whereHas('product')->whereIn('product_id', $selectedIds)->get();

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

    private function voucherIsEligible(Voucher $voucher, float $subtotal): bool
    {
        $currentTime = now();

        return $voucher->is_active
            && ($voucher->starts_at === null || $voucher->starts_at->lte($currentTime))
            && ($voucher->ends_at === null || $voucher->ends_at->gte($currentTime))
            && $subtotal >= (float) $voucher->minimum_order
            && ($voucher->usage_limit === null || $voucher->used_count < $voucher->usage_limit);
    }

    private function voucherDiscount(Voucher $voucher, float $subtotal): float
    {
        $discount = $voucher->discount_type === 'percent'
            ? $subtotal * (float) $voucher->discount_value / 100
            : (float) $voucher->discount_value;

        if ($voucher->max_discount !== null) {
            $discount = min($discount, (float) $voucher->max_discount);
        }

        return round(min($discount, $subtotal), 2);
    }
}
