<?php

namespace App\Services;

use App\Mail\OrderStatusUpdated;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderShipmentEvent;
use App\Models\PaymentIntent;
use App\Models\Product;
use App\Models\ReturnRequest;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private AuditLogService $auditLogs) {}

    /** @param array<string, mixed> $data */
    public function placeOrder(Cart $cart, array $data, ?User $user): Order
    {
        return DB::transaction(function () use ($cart, $data, $user): Order {
            $cartItems = $cart->items()->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Giỏ hàng đang trống.']);
            }

            $products = $this->lockedProductsFor($cartItems->pluck('product_id')->all());
            $priceGroup = $user?->account_type === 'business' && $user->business_approved
                ? $user->price_group
                : 'retail';
            $subtotal = 0;
            $orderLines = [];

            foreach ($cartItems as $cartItem) {
                $product = $products->get($cartItem->product_id);

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages(['cart' => 'Một sản phẩm trong giỏ hàng không còn được bán.']);
                }

                $quantity = (int) $cartItem->quantity;

                if ($quantity < $product->moq || $quantity % max(1, $product->pack_size) !== 0) {
                    throw ValidationException::withMessages([
                        'cart' => "{$product->name}: số lượng phải từ {$product->moq} và theo bội số đóng gói {$product->pack_size}.",
                    ]);
                }

                if (! $product->allow_backorder && $product->availableStock() < $quantity) {
                    throw ValidationException::withMessages(['cart' => "{$product->name} hiện không đủ tồn kho."]);
                }

                $unitPrice = $product->priceFor($quantity, $priceGroup);
                $lineTotal = round($unitPrice * $quantity, 2);
                $subtotal += $lineTotal;
                $orderLines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $coupon = null;
            $discount = 0;
            $couponCode = strtoupper(trim((string) ($data['coupon_code'] ?? '')));

            if ($couponCode !== '') {
                $coupon = Coupon::query()->where('code', $couponCode)->lockForUpdate()->first();
                $discount = $coupon?->discountFor($subtotal) ?? 0;

                if ($discount <= 0) {
                    throw ValidationException::withMessages(['coupon_code' => 'Mã giảm giá không hợp lệ, đã hết hạn hoặc chưa đủ điều kiện.']);
                }
            }

            $shipping = $subtotal - $discount >= (float) config('store.free_shipping_threshold')
                ? 0
                : (float) config('store.standard_shipping_fee');
            $vat = round(max(0, $subtotal - $discount) * ((float) config('store.vat_rate') / 100), 2);
            $onlinePayment = in_array($data['payment_method'], ['vnpay', 'momo'], true);
            $order = Order::query()->create([
                'user_id' => $user?->id,
                'guest_access_token' => $user ? null : Str::random(64),
                'coupon_id' => $coupon?->id,
                'order_number' => 'LK'.now()->format('ymd').Str::upper(Str::random(6)),
                'status' => $onlinePayment ? 'pending_payment' : 'pending_confirmation',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'cod_status' => $data['payment_method'] === 'cod' ? 'pending_collection' : 'not_applicable',
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'],
                'shipping_address' => $data['shipping_address'],
                'shipping_city' => $data['shipping_city'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'tax_number' => $data['tax_number'] ?? null,
                'subtotal' => $subtotal,
                'discount_total' => $discount,
                'shipping_total' => $shipping,
                'vat_total' => $vat,
                'total' => $subtotal - $discount + $shipping + $vat,
                'customer_note' => $data['customer_note'] ?? null,
                'reserved_until' => $onlinePayment ? now()->addMinutes(15) : null,
            ]);
            $this->auditLogs->record('order.created', $order, null, ['status' => $order->status, 'total' => $order->total], $user);

            foreach ($orderLines as $line) {
                /** @var Product $product */
                $product = $line['product'];
                $order->items()->create([
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'product_name' => $product->name,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => $line['line_total'],
                ]);

                if ($onlinePayment && ! $product->allow_backorder) {
                    $product->increment('reserved_stock', $line['quantity']);
                    $product->inventoryMovements()->create([
                        'user_id' => $user?->id,
                        'type' => 'reserve',
                        'quantity' => (int) $line['quantity'],
                        'reference' => $order->order_number,
                        'note' => 'Giữ hàng trong 15 phút chờ thanh toán.',
                    ]);
                }
            }

            $coupon?->increment('used_count');
            $cart->items()->delete();

            return $order->load('items');
        });
    }

    /** @param array<string, mixed> $data */
    public function createPayOSIntent(Cart $cart, array $data, ?User $user): PaymentIntent
    {
        return DB::transaction(function () use ($cart, $data, $user): PaymentIntent {
            $lockedCart = Cart::query()->lockForUpdate()->findOrFail($cart->id);
            $staleIntents = PaymentIntent::query()
                ->where('cart_id', $lockedCart->id)
                ->whereIn('status', ['creating', 'pending'])
                ->where('expires_at', '<=', now())
                ->lockForUpdate()
                ->get();

            foreach ($staleIntents as $staleIntent) {
                $this->releasePayOSReservations($staleIntent);
                $staleIntent->update(['status' => 'expired']);
            }

            $existingIntent = PaymentIntent::query()
                ->where('cart_id', $lockedCart->id)
                ->whereIn('status', ['creating', 'pending'])
                ->where('expires_at', '>', now())
                ->latest('id')
                ->first();

            if ($existingIntent) {
                if ($existingIntent->status === 'pending' && $existingIntent->checkout_url) {
                    return $existingIntent;
                }

                throw ValidationException::withMessages(['payment_method' => 'Đang tạo liên kết thanh toán cho giỏ hàng này. Vui lòng thử lại sau ít phút.']);
            }

            $cartItems = $lockedCart->items()->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Giỏ hàng đang trống.']);
            }

            $products = $this->lockedProductsFor($cartItems->pluck('product_id')->all());
            $priceGroup = $user?->account_type === 'business' && $user->business_approved
                ? $user->price_group
                : 'retail';
            $subtotal = 0.0;
            $lines = [];

            foreach ($cartItems as $cartItem) {
                $product = $products->get($cartItem->product_id);

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages(['cart' => 'Một sản phẩm trong giỏ hàng không còn được bán.']);
                }

                $quantity = (int) $cartItem->quantity;

                if ($quantity < $product->moq || $quantity % max(1, $product->pack_size) !== 0) {
                    throw ValidationException::withMessages([
                        'cart' => "{$product->name}: số lượng phải từ {$product->moq} và theo bội số đóng gói {$product->pack_size}.",
                    ]);
                }

                if (! $product->allow_backorder && $product->availableStock() < $quantity) {
                    throw ValidationException::withMessages(['cart' => "{$product->name} hiện không đủ tồn kho."]);
                }

                $unitPrice = $product->priceFor($quantity, $priceGroup);
                $lineTotal = round($unitPrice * $quantity, 2);
                $subtotal += $lineTotal;
                $lines[] = [
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'allow_backorder' => $product->allow_backorder,
                ];
            }

            $coupon = null;
            $discount = 0.0;
            $couponCode = strtoupper(trim((string) ($data['coupon_code'] ?? '')));

            if ($couponCode !== '') {
                $coupon = Coupon::query()->where('code', $couponCode)->lockForUpdate()->first();
                $discount = $coupon?->discountFor($subtotal) ?? 0;

                if ($discount <= 0) {
                    throw ValidationException::withMessages(['coupon_code' => 'Mã giảm giá không hợp lệ, đã hết hạn hoặc chưa đủ điều kiện.']);
                }
            }

            $shipping = $subtotal - $discount >= (float) config('store.free_shipping_threshold')
                ? 0.0
                : (float) config('store.standard_shipping_fee');
            $vat = round(max(0, $subtotal - $discount) * ((float) config('store.vat_rate') / 100), 2);
            $total = (int) round($subtotal - $discount + $shipping + $vat);

            if ($total < 1) {
                throw ValidationException::withMessages(['payment_method' => 'Số tiền thanh toán phải lớn hơn 0₫.']);
            }

            $orderCode = now()->getTimestamp() * 1000 + random_int(0, 999);
            $intent = PaymentIntent::query()->create([
                'user_id' => $user?->id,
                'cart_id' => $lockedCart->id,
                'order_code' => $orderCode,
                'public_token' => Str::random(64),
                'status' => 'creating',
                'amount' => $total,
                'description' => 'LK'.substr((string) $orderCode, -7),
                'payload' => [
                    'checkout' => [
                        'customer_name' => $data['customer_name'],
                        'customer_email' => $data['customer_email'] ?? null,
                        'customer_phone' => $data['customer_phone'],
                        'shipping_address' => $data['shipping_address'],
                        'shipping_city' => $data['shipping_city'] ?? null,
                        'company_name' => $data['company_name'] ?? null,
                        'tax_number' => $data['tax_number'] ?? null,
                        'customer_note' => $data['customer_note'] ?? null,
                    ],
                    'pricing' => [
                        'coupon_id' => $coupon?->id,
                        'subtotal' => $subtotal,
                        'discount_total' => $discount,
                        'shipping_total' => $shipping,
                        'vat_total' => $vat,
                        'total' => $total,
                    ],
                    'lines' => $lines,
                ],
                'expires_at' => now()->addMinutes(15),
            ]);

            foreach ($lines as $line) {
                if ($line['allow_backorder']) {
                    continue;
                }

                $product = $products->get($line['product_id']);
                $product->increment('reserved_stock', $line['quantity']);
                $product->inventoryMovements()->create([
                    'user_id' => $user?->id,
                    'type' => 'reserve',
                    'quantity' => $line['quantity'],
                    'reference' => 'PAYOS-'.$orderCode,
                    'note' => 'Giữ hàng trong 15 phút chờ xác nhận thanh toán PayOS.',
                ]);
            }

            $coupon?->increment('used_count');

            return $intent;
        });
    }

    /** @param array<string, mixed> $paymentData
     * @return array{order: Order, created: bool}|null
     */
    public function fulfillPayOSIntent(array $paymentData): ?array
    {
        return DB::transaction(function () use ($paymentData): ?array {
            $intent = PaymentIntent::query()
                ->where('order_code', (int) ($paymentData['orderCode'] ?? 0))
                ->lockForUpdate()
                ->first();

            if (! $intent) {
                return null;
            }

            if ($intent->status === 'paid') {
                $order = $intent->order;

                return $order ? ['order' => $order, 'created' => false] : null;
            }

            if (
                ! in_array($intent->status, ['pending', 'expired'], true)
                || $intent->amount !== (int) ($paymentData['amount'] ?? 0)
                || ($intent->payment_link_id && ($paymentData['paymentLinkId'] ?? null) !== $intent->payment_link_id)
            ) {
                return null;
            }

            $payload = $intent->payload;
            $lines = $payload['lines'] ?? [];
            $products = $this->lockedProductsFor(array_column($lines, 'product_id'));

            foreach ($lines as $line) {
                $product = $products->get($line['product_id']);
                $hasReservation = $intent->status === 'pending' && $product?->reserved_stock >= $line['quantity'];
                $hasStock = $intent->status === 'expired'
                    ? $product?->availableStock() >= $line['quantity']
                    : false;

                if (! $product || ! $product->is_active || (! $line['allow_backorder'] && ! $hasReservation && ! $hasStock)) {
                    return null;
                }
            }

            $checkout = $payload['checkout'];
            $pricing = $payload['pricing'];
            $orderNumber = 'LK'.now()->format('ymd').Str::upper(Str::random(8));
            $order = Order::query()->create([
                'user_id' => $intent->user_id,
                'guest_access_token' => $intent->user_id ? null : Str::random(64),
                'coupon_id' => $pricing['coupon_id'] ?? null,
                'order_number' => $orderNumber,
                'status' => 'pending_confirmation',
                'payment_method' => 'bank_transfer',
                'payment_status' => 'paid',
                'refund_status' => 'not_required',
                'customer_name' => $checkout['customer_name'],
                'customer_email' => $checkout['customer_email'] ?? null,
                'customer_phone' => $checkout['customer_phone'],
                'shipping_address' => $checkout['shipping_address'],
                'shipping_city' => $checkout['shipping_city'] ?? null,
                'company_name' => $checkout['company_name'] ?? null,
                'tax_number' => $checkout['tax_number'] ?? null,
                'subtotal' => $pricing['subtotal'],
                'discount_total' => $pricing['discount_total'],
                'shipping_total' => $pricing['shipping_total'],
                'vat_total' => $pricing['vat_total'],
                'total' => $pricing['total'],
                'customer_note' => $checkout['customer_note'] ?? null,
                'stock_deducted_at' => now(),
            ]);
            $this->auditLogs->record('order.created', $order, null, ['status' => $order->status, 'total' => $order->total], $intent->user);

            foreach ($lines as $line) {
                $product = $products->get($line['product_id']);
                $order->items()->create([
                    'product_id' => $product->id,
                    'sku' => $line['sku'],
                    'product_name' => $line['name'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => $line['line_total'],
                ]);

                if (! $line['allow_backorder']) {
                    if ($intent->status === 'pending' && $product->reserved_stock >= $line['quantity']) {
                        $product->decrement('reserved_stock', $line['quantity']);
                    }

                    $product->decrement('stock', $line['quantity']);
                    $product->inventoryMovements()->create([
                        'user_id' => $intent->user_id,
                        'type' => 'out',
                        'quantity' => -$line['quantity'],
                        'reference' => $order->order_number,
                        'note' => 'Xuất kho cho đơn đã thanh toán qua PayOS.',
                    ]);
                }
            }

            $this->removePaidQuantitiesFromCart($intent, $lines);

            if ($intent->status === 'expired' && ($pricing['coupon_id'] ?? null)) {
                Coupon::query()->whereKey($pricing['coupon_id'])->increment('used_count');
            }

            $this->auditLogs->record('order.payment_received', $order, ['status' => 'pending_payment'], ['status' => 'pending_confirmation', 'payment_status' => 'paid'], $intent->user);
            $intent->update([
                'status' => 'paid',
                'order_id' => $order->id,
                'reference' => $paymentData['reference'] ?? null,
                'paid_at' => now(),
            ]);

            return ['order' => $order->load('items'), 'created' => true];
        });
    }

    public function cancelPayOSIntent(PaymentIntent $intent): void
    {
        DB::transaction(function () use ($intent): void {
            $intent = PaymentIntent::query()->lockForUpdate()->findOrFail($intent->id);

            if (! in_array($intent->status, ['creating', 'pending'], true)) {
                return;
            }

            $this->releasePayOSReservations($intent);
            $intent->update(['status' => 'cancelled']);
        });
    }

    public function prepareMockPayment(PaymentIntent $intent, string $checkoutUrl): void
    {
        DB::transaction(function () use ($intent, $checkoutUrl): void {
            $lockedIntent = PaymentIntent::query()->lockForUpdate()->findOrFail($intent->id);

            if ($lockedIntent->status === 'pending' && str_starts_with((string) $lockedIntent->payment_link_id, 'MOCK-')) {
                $lockedIntent->update(['checkout_url' => $checkoutUrl]);

                return;
            }

            if ($lockedIntent->status !== 'creating') {
                throw ValidationException::withMessages(['payment_method' => 'Không thể mở mô phỏng thanh toán cho liên kết này. Hãy tạo lại đơn thanh toán.']);
            }

            if ($lockedIntent->expires_at?->isPast()) {
                $this->releasePayOSReservations($lockedIntent);
                $lockedIntent->update(['status' => 'expired']);

                return;
            }

            $lockedIntent->update([
                'status' => 'pending',
                'payment_link_id' => 'MOCK-'.$lockedIntent->order_code,
                'checkout_url' => $checkoutUrl,
                'qr_code' => null,
            ]);
        });
    }

    /** @return array{order: Order, created: bool}|null */
    public function fulfillMockPayment(PaymentIntent $intent): ?array
    {
        $intent = PaymentIntent::query()->findOrFail($intent->id);

        if (! str_starts_with((string) $intent->payment_link_id, 'MOCK-')) {
            return null;
        }

        if ($intent->status === 'paid') {
            return $intent->order ? ['order' => $intent->order, 'created' => false] : null;
        }

        if ($intent->status !== 'pending') {
            return null;
        }

        if ($intent->expires_at?->isPast()) {
            $this->cancelPayOSIntent($intent);

            return null;
        }

        return $this->fulfillPayOSIntent([
            'orderCode' => $intent->order_code,
            'amount' => $intent->amount,
            'paymentLinkId' => $intent->payment_link_id,
            'reference' => 'DEMO-'.Str::upper(Str::random(10)),
        ]);
    }

    public function cancelMockPayment(PaymentIntent $intent): void
    {
        $intent = PaymentIntent::query()->findOrFail($intent->id);

        if (! str_starts_with((string) $intent->payment_link_id, 'MOCK-')) {
            throw ValidationException::withMessages(['payment_method' => 'Chỉ có thể hủy liên kết thanh toán mô phỏng tại đây.']);
        }

        $this->cancelPayOSIntent($intent);
    }

    public function releaseExpiredPayOSIntents(): int
    {
        $expiredIntents = PaymentIntent::query()
            ->where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->pluck('id');

        foreach ($expiredIntents as $intentId) {
            DB::transaction(function () use ($intentId): void {
                $intent = PaymentIntent::query()->lockForUpdate()->find($intentId);

                if (! $intent || $intent->status !== 'pending' || $intent->expires_at->isFuture()) {
                    return;
                }

                $this->releasePayOSReservations($intent);
                $intent->update(['status' => 'expired']);
            });
        }

        return $expiredIntents->count();
    }

    public function confirm(Order $order, ?User $staff = null): void
    {
        $paymentWindowExpired = false;

        DB::transaction(function () use ($order, $staff, &$paymentWindowExpired): void {
            $order = Order::query()->with('items')->lockForUpdate()->findOrFail($order->id);

            if (! in_array($order->status, ['pending_confirmation', 'pending_payment'], true)) {
                throw ValidationException::withMessages(['status' => 'Đơn hàng không ở trạng thái chờ xác nhận.']);
            }

            if ($order->status === 'pending_payment' && $order->reserved_until?->isPast()) {
                $this->releaseReservations($order, $staff);
                $previousStatus = $order->status;
                $order->update([
                    'status' => 'cancelled',
                    'shipment_status' => 'cancelled',
                    'cancellation_reason' => 'Quá hạn thanh toán',
                    'cancelled_at' => now(),
                    'reserved_until' => null,
                ]);
                $this->auditLogs->record('order.payment_expired', $order, ['status' => $previousStatus], ['status' => 'cancelled', 'cancellation_reason' => 'Quá hạn thanh toán'], $staff);
                $paymentWindowExpired = true;

                return;
            }

            if ($order->payment_method !== 'cod' && $order->payment_status !== 'paid') {
                throw ValidationException::withMessages(['status' => 'Chỉ xác nhận đơn thanh toán online sau khi cổng thanh toán báo đã thu tiền.']);
            }

            if (! $order->stock_deducted_at) {
                $products = $this->lockedProductsFor($order->items->pluck('product_id')->filter()->all());

                foreach ($order->items as $item) {
                    $product = $products->get($item->product_id);

                    if (! $product) {
                        continue;
                    }

                    if ($order->status === 'pending_payment' && $product->reserved_stock >= $item->quantity) {
                        $product->decrement('reserved_stock', $item->quantity);
                    } elseif (! $product->allow_backorder && $product->availableStock() < $item->quantity) {
                        throw ValidationException::withMessages(['status' => "{$product->name} không còn đủ hàng để xác nhận."]);
                    }

                    if (! $product->allow_backorder) {
                        $product->decrement('stock', $item->quantity);
                        $product->inventoryMovements()->create([
                            'user_id' => $staff?->id,
                            'type' => 'out',
                            'quantity' => -$item->quantity,
                            'reference' => $order->order_number,
                            'note' => 'Xuất kho khi xác nhận đơn hàng.',
                        ]);
                    }
                }
            }

            $order->update([
                'status' => 'preparing',
                'payment_status' => $order->payment_method === 'cod' ? 'pending' : $order->payment_status,
                'confirmed_at' => now(),
                'reserved_until' => null,
                'stock_deducted_at' => $order->stock_deducted_at ?? now(),
            ]);
            $this->auditLogs->record('order.confirmed', $order, ['status' => 'pending_confirmation'], ['status' => 'preparing', 'confirmed_at' => $order->confirmed_at], $staff);
        });

        if ($paymentWindowExpired) {
            $this->notifyCustomer($order->fresh(), 'Đơn hàng đã hết hạn thanh toán', 'Đơn hàng đã tự hủy do quá thời gian thanh toán.');
            throw ValidationException::withMessages(['status' => 'Thời gian thanh toán đã hết; đơn hàng đã được hủy.']);
        }

        $this->notifyCustomer($order->fresh(), 'Đơn hàng đã được xác nhận', 'Cửa hàng đã xác nhận đơn và đang chuẩn bị hàng.');
    }

    public function cancel(Order $order, string $reason, ?User $actor = null): void
    {
        DB::transaction(function () use ($order, $reason, $actor): void {
            $order = Order::query()->with('items')->lockForUpdate()->findOrFail($order->id);

            if (! in_array($order->status, ['pending_confirmation', 'pending_payment'], true)) {
                throw ValidationException::withMessages(['status' => 'Chỉ có thể hủy đơn đang chờ xác nhận hoặc thanh toán.']);
            }

            if ($order->status === 'pending_payment') {
                $this->releaseReservations($order, $actor);
            }

            if ($order->stock_deducted_at) {
                $products = $this->lockedProductsFor($order->items->pluck('product_id')->filter()->all());

                foreach ($order->items as $item) {
                    $product = $products->get($item->product_id);

                    if (! $product || $product->allow_backorder) {
                        continue;
                    }

                    $product->increment('stock', $item->quantity);
                    $product->inventoryMovements()->create([
                        'user_id' => $actor?->id,
                        'type' => 'in',
                        'quantity' => $item->quantity,
                        'reference' => $order->order_number,
                        'note' => 'Nhập lại kho do hủy đơn trước khi người bán xác nhận.',
                    ]);
                }
            }

            $oldStatus = $order->status;
            $refundStatus = $order->payment_status === 'paid' ? 'pending' : 'not_required';
            $order->update([
                'status' => 'cancelled',
                'shipment_status' => 'cancelled',
                'cancellation_reason' => trim($reason),
                'cancelled_by' => $actor?->id,
                'cancelled_at' => now(),
                'reserved_until' => null,
                'refund_status' => $refundStatus,
            ]);
            $this->auditLogs->record('order.cancelled', $order, ['status' => $oldStatus], [
                'status' => 'cancelled',
                'cancellation_reason' => trim($reason),
                'cancelled_by' => $actor?->id,
                'refund_status' => $refundStatus,
            ], $actor);
        });

        $cancelledOrder = $order->fresh();
        $this->notifyCustomer($cancelledOrder, 'Đơn hàng đã hủy', 'Lý do: '.$cancelledOrder->cancellation_reason);
    }

    /** @param array<string, mixed> $shipmentData */
    public function recordShipment(Order $order, array $shipmentData, ?User $staff = null, string $source = 'admin'): void
    {
        DB::transaction(function () use ($order, $shipmentData, $staff, $source): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if (! in_array($lockedOrder->status, ['preparing', 'confirmed'], true)) {
                throw ValidationException::withMessages(['shipment_number' => 'Chỉ tạo vận đơn khi người bán đang chuẩn bị hàng.']);
            }

            $oldValues = $lockedOrder->only(['status', 'shipment_status', 'shipment_number']);
            $lockedOrder->update([
                'status' => 'awaiting_pickup',
                'shipment_status' => 'awaiting_pickup',
                'shipment_service' => $shipmentData['shipment_service'] ?? null,
                'shipment_number' => $shipmentData['shipment_number'],
                'shipment_tracking_url' => $shipmentData['shipment_tracking_url'] ?? null,
                'estimated_delivery_at' => $shipmentData['estimated_delivery_at'] ?? null,
                'shipment_updated_at' => now(),
            ]);
            $this->recordShipmentEvent($lockedOrder, 'awaiting_pickup', $source, 'manual-'.Str::uuid(), 'Đã tạo vận đơn; chờ Viettel Post nhận kiện.', now(), null);
            $this->auditLogs->record('order.shipment_created', $lockedOrder, $oldValues, $lockedOrder->only(['status', 'shipment_status', 'shipment_number', 'shipment_service', 'estimated_delivery_at']), $staff);
        });

        $updatedOrder = $order->fresh();
        $shipmentMessage = $source === 'simulation'
            ? 'Mã vận đơn Viettel Post mô phỏng của bạn là '.$updatedOrder->shipment_number.'.'
            : 'Mã vận đơn Viettel Post của bạn là '.$updatedOrder->shipment_number.'.';
        $this->notifyCustomer($updatedOrder, 'Đơn hàng có mã vận đơn', $shipmentMessage);
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    public function transitionShipment(
        Order $order,
        string $nextStatus,
        string $source = 'admin',
        ?string $providerEventId = null,
        ?string $description = null,
        ?DateTimeInterface $occurredAt = null,
        ?array $payload = null,
        ?User $staff = null,
    ): bool {
        $processed = DB::transaction(function () use ($order, $nextStatus, $source, $providerEventId, $description, $occurredAt, $payload, $staff): bool {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($providerEventId && OrderShipmentEvent::query()->where('provider_event_id', $providerEventId)->exists()) {
                return false;
            }

            $transitions = [
                'awaiting_pickup' => ['handed_over', 'in_transit', 'out_for_delivery', 'delivery_failed', 'returning', 'completed'],
                'handed_over' => ['in_transit', 'out_for_delivery', 'delivery_failed', 'returning', 'completed'],
                'in_transit' => ['out_for_delivery', 'delivery_failed', 'returning', 'completed'],
                'out_for_delivery' => ['in_transit', 'delivery_failed', 'returning', 'completed'],
                'delivery_failed' => ['in_transit', 'out_for_delivery', 'returning', 'returned'],
                'returning' => ['returned'],
            ];

            if (! in_array($nextStatus, $transitions[$lockedOrder->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' => 'Trạng thái vận chuyển mới không hợp lệ với hành trình hiện tại.']);
            }

            $oldValues = $lockedOrder->only(['status', 'shipment_status']);
            $shipmentStatus = $nextStatus === 'completed' ? 'delivered' : $nextStatus;
            $attributes = [
                'status' => $nextStatus,
                'shipment_status' => $shipmentStatus,
                'shipment_updated_at' => now(),
            ];

            if (in_array($nextStatus, ['handed_over', 'in_transit', 'out_for_delivery', 'delivery_failed', 'returning', 'returned', 'completed'], true) && ! $lockedOrder->shipment_handed_over_at) {
                $attributes['shipment_handed_over_at'] = $occurredAt ?? now();
            }

            if ($nextStatus === 'completed') {
                $attributes['delivered_at'] = $occurredAt ?? now();
            }

            $lockedOrder->update($attributes);
            $eventId = $providerEventId ?? 'manual-'.Str::uuid();
            $this->recordShipmentEvent($lockedOrder, $shipmentStatus, $source, $eventId, $description, $occurredAt, $payload);
            $this->auditLogs->record('order.shipment_status_changed', $lockedOrder, $oldValues, $lockedOrder->only(['status', 'shipment_status']), $staff);

            return true;
        });

        if ($processed) {
            $updatedOrder = $order->fresh();
            $labels = [
                'handed_over' => 'Bàn giao đơn vị vận chuyển', 'in_transit' => 'Đang trung chuyển',
                'out_for_delivery' => 'Đang giao đến người nhận', 'delivery_failed' => 'Giao không thành công',
                'returning' => 'Đang hoàn hàng', 'returned' => 'Đã hoàn hàng', 'completed' => 'Đã hoàn tất giao hàng',
            ];
            $this->notifyCustomer($updatedOrder, 'Cập nhật đơn hàng', 'Trạng thái mới: '.($labels[$nextStatus] ?? $nextStatus).'.');
        }

        return $processed;
    }

    public function updateCodStatus(Order $order, string $nextStatus, ?User $staff = null): void
    {
        DB::transaction(function () use ($order, $nextStatus, $staff): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->payment_method !== 'cod') {
                throw ValidationException::withMessages(['cod_status' => 'Đơn này không sử dụng thanh toán COD.']);
            }

            $allowedStatuses = [
                'pending_collection' => ['collected'],
                'collected' => ['pending_reconciliation'],
                'pending_reconciliation' => ['reconciled'],
            ];

            if (! in_array($nextStatus, $allowedStatuses[$lockedOrder->cod_status] ?? [], true)) {
                throw ValidationException::withMessages(['cod_status' => 'Trạng thái COD mới không hợp lệ.']);
            }

            if ($nextStatus === 'collected' && $lockedOrder->status !== 'completed') {
                throw ValidationException::withMessages(['cod_status' => 'Chỉ ghi nhận tiền thu hộ sau khi Viettel Post xác nhận giao thành công.']);
            }

            $oldStatus = $lockedOrder->cod_status;
            $attributes = ['cod_status' => $nextStatus];

            if ($nextStatus === 'collected') {
                $attributes['cod_collected_at'] = now();
                $attributes['payment_status'] = 'paid';
            }

            if ($nextStatus === 'reconciled') {
                $attributes['cod_reconciled_at'] = now();
            }

            $lockedOrder->update($attributes);
            $this->auditLogs->record('order.cod_status_changed', $lockedOrder, ['cod_status' => $oldStatus], ['cod_status' => $nextStatus], $staff);
        });
    }

    public function reviewReturnRequest(
        ReturnRequest $returnRequest,
        string $status,
        ?string $resolution,
        ?string $staffNote,
        ?User $staff = null,
    ): void {
        DB::transaction(function () use ($returnRequest, $status, $resolution, $staffNote, $staff): void {
            $requestSnapshot = ReturnRequest::query()->findOrFail($returnRequest->id);
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($requestSnapshot->order_id);
            $lockedReturnRequest = ReturnRequest::query()->lockForUpdate()->findOrFail($returnRequest->id);

            if ($lockedReturnRequest->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Yêu cầu đổi trả này đã được xử lý.']);
            }

            if ($status === 'accepted' && $resolution === 'refund') {
                if ($lockedOrder->payment_status !== 'paid' || (float) $lockedOrder->total <= 0) {
                    throw ValidationException::withMessages(['resolution' => 'Chỉ có thể duyệt hoàn tiền cho đơn đã thanh toán và có giá trị lớn hơn 0.']);
                }

                if ($lockedOrder->refund_status !== 'not_required') {
                    throw ValidationException::withMessages(['resolution' => 'Đơn hàng đã có một yêu cầu hoàn tiền khác.']);
                }
            }

            $oldValues = $lockedReturnRequest->only(['status', 'resolution', 'staff_note']);
            $lockedReturnRequest->update([
                'status' => $status,
                'resolution' => $status === 'accepted' ? $resolution : null,
                'staff_note' => $staffNote,
            ]);

            $this->auditLogs->record(
                'return_request.updated',
                $lockedReturnRequest,
                $oldValues,
                $lockedReturnRequest->only(['status', 'resolution', 'staff_note']),
                $staff,
            );
        });
    }

    public function receiveReturnRequest(ReturnRequest $returnRequest, ?User $staff = null): void
    {
        DB::transaction(function () use ($returnRequest, $staff): void {
            $requestSnapshot = ReturnRequest::query()->findOrFail($returnRequest->id);
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($requestSnapshot->order_id);
            $lockedReturnRequest = ReturnRequest::query()->lockForUpdate()->findOrFail($returnRequest->id);

            if ($lockedReturnRequest->status !== 'accepted' || $lockedReturnRequest->resolution !== 'refund') {
                throw ValidationException::withMessages(['return' => 'Chỉ xác nhận đã nhận hàng cho yêu cầu hoàn tiền đã được duyệt.']);
            }

            if ($lockedOrder->payment_status !== 'paid' || (float) $lockedOrder->total <= 0) {
                throw ValidationException::withMessages(['return' => 'Đơn hàng chưa được thanh toán hoặc không có số tiền cần hoàn.']);
            }

            if ($lockedOrder->refund_status !== 'not_required') {
                throw ValidationException::withMessages(['return' => 'Đơn hàng đã có một yêu cầu hoàn tiền khác.']);
            }

            $lockedOrder->update(['refund_status' => 'pending']);
            $this->auditLogs->record(
                'order.refund_pending',
                $lockedOrder,
                ['refund_status' => 'not_required'],
                ['refund_status' => 'pending', 'refund_amount' => (float) $lockedOrder->total],
                $staff,
            );

            $oldValues = $lockedReturnRequest->only(['status']);
            $lockedReturnRequest->update(['status' => 'received']);
            $this->auditLogs->record(
                'return_request.updated',
                $lockedReturnRequest,
                $oldValues,
                $lockedReturnRequest->only(['status']),
                $staff,
            );
        });
    }

    public function markRefunded(Order $order, ?string $reference, ?User $staff = null): string
    {
        $isSimulation = config('services.payment.driver') === 'mock';

        if ($isSimulation) {
            $reference = 'SIM-REFUND-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6));
        } elseif (trim((string) $reference) === '') {
            throw ValidationException::withMessages(['refund_reference' => 'Cần mã giao dịch ngân hàng để ghi nhận hoàn tiền.']);
        }

        $reference = trim((string) $reference);

        return DB::transaction(function () use ($order, $reference, $staff): string {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $hasApprovedRefundRequest = $lockedOrder->returnRequests()
                ->where('status', 'received')
                ->where('resolution', 'refund')
                ->exists();
            $isCancelledOrderRefund = $lockedOrder->status === 'cancelled';

            if (
                $lockedOrder->refund_status !== 'pending'
                || $lockedOrder->payment_status !== 'paid'
                || (! $isCancelledOrderRefund && ! $hasApprovedRefundRequest)
            ) {
                throw ValidationException::withMessages(['refund_reference' => 'Đơn này không có khoản hoàn tiền đang chờ xử lý.']);
            }

            $oldValues = $lockedOrder->only(['refund_status', 'payment_status', 'refund_reference', 'refunded_at']);
            $lockedOrder->update([
                'refund_status' => 'refunded',
                'refund_reference' => $reference,
                'refunded_at' => now(),
                'payment_status' => 'refunded',
            ]);

            $this->auditLogs->record(
                'order.refunded',
                $lockedOrder,
                $oldValues,
                $lockedOrder->only(['refund_status', 'payment_status', 'refund_reference', 'refunded_at']),
                $staff,
            );

            return $reference;
        });
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function recordShipmentEvent(Order $order, string $status, string $source, string $providerEventId, ?string $description, ?DateTimeInterface $occurredAt, ?array $payload): void
    {
        OrderShipmentEvent::query()->create([
            'order_id' => $order->id,
            'provider_event_id' => $providerEventId,
            'source' => $source,
            'status' => $status,
            'description' => $description,
            'occurred_at' => $occurredAt,
            'payload' => $payload,
        ]);
    }

    private function notifyCustomer(?Order $order, string $headline, string $message): void
    {
        if (! $order?->customer_email) {
            return;
        }

        try {
            Mail::to($order->customer_email)->send(new OrderStatusUpdated($order, $headline, $message));
        } catch (\Throwable $exception) {
            Log::warning('Order status notification could not be sent.', [
                'order_id' => $order->id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    public function releaseExpiredReservations(): int
    {
        $expiredOrders = Order::query()
            ->where('status', 'pending_payment')
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '<=', now())
            ->with('items')
            ->get();

        foreach ($expiredOrders as $order) {
            $wasCancelled = DB::transaction(function () use ($order): bool {
                $lockedOrder = Order::query()->with('items')->lockForUpdate()->find($order->id);

                if (! $lockedOrder || $lockedOrder->status !== 'pending_payment' || $lockedOrder->reserved_until?->isFuture()) {
                    return false;
                }

                $this->releaseReservations($lockedOrder, null);
                $oldStatus = $lockedOrder->status;
                $lockedOrder->update([
                    'status' => 'cancelled',
                    'shipment_status' => 'cancelled',
                    'cancellation_reason' => 'Quá hạn thanh toán',
                    'cancelled_at' => now(),
                    'reserved_until' => null,
                    'refund_status' => $lockedOrder->payment_status === 'paid' ? 'pending' : 'not_required',
                ]);
                $this->auditLogs->record('order.payment_expired', $lockedOrder, ['status' => $oldStatus], ['status' => 'cancelled', 'cancellation_reason' => 'Quá hạn thanh toán'], null);

                return true;
            });

            if ($wasCancelled) {
                $this->notifyCustomer($order->fresh(), 'Đơn hàng đã hết hạn thanh toán', 'Đơn hàng đã tự hủy do quá thời gian thanh toán.');
            }
        }

        return $expiredOrders->count();
    }

    private function releaseReservations(Order $order, ?User $actor): void
    {
        $products = $this->lockedProductsFor($order->items->pluck('product_id')->filter()->all());

        foreach ($order->items as $item) {
            $product = $products->get($item->product_id);

            if (! $product || $product->reserved_stock < $item->quantity) {
                continue;
            }

            $product->decrement('reserved_stock', $item->quantity);
            $product->inventoryMovements()->create([
                'user_id' => $actor?->id,
                'type' => 'release',
                'quantity' => -$item->quantity,
                'reference' => $order->order_number,
                'note' => 'Nhả hàng đã giữ cho đơn bị hủy.',
            ]);
        }
    }

    private function releasePayOSReservations(PaymentIntent $intent): void
    {
        $lines = $intent->payload['lines'] ?? [];
        $products = $this->lockedProductsFor(array_column($lines, 'product_id'));

        foreach ($lines as $line) {
            $product = $products->get($line['product_id']);

            if (! $product || $line['allow_backorder'] || $product->reserved_stock < $line['quantity']) {
                continue;
            }

            $product->decrement('reserved_stock', $line['quantity']);
            $product->inventoryMovements()->create([
                'user_id' => $intent->user_id,
                'type' => 'release',
                'quantity' => -$line['quantity'],
                'reference' => 'PAYOS-'.$intent->order_code,
                'note' => 'Giải phóng hàng giữ chỗ cho liên kết PayOS đã đóng.',
            ]);
        }

        $couponId = $intent->payload['pricing']['coupon_id'] ?? null;

        if ($couponId) {
            $coupon = Coupon::query()->lockForUpdate()->find($couponId);

            if ($coupon && $coupon->used_count > 0) {
                $coupon->decrement('used_count');
            }
        }
    }

    /** @param array<int, array{product_id: int, quantity: int}> $lines */
    private function removePaidQuantitiesFromCart(PaymentIntent $intent, array $lines): void
    {
        $cart = $intent->cart;

        if (! $cart) {
            return;
        }

        foreach ($lines as $line) {
            $cartItem = $cart->items()->where('product_id', $line['product_id'])->first();

            if (! $cartItem) {
                continue;
            }

            $remainingQuantity = (int) $cartItem->quantity - $line['quantity'];

            if ($remainingQuantity > 0) {
                $cartItem->update(['quantity' => $remainingQuantity]);
            } else {
                $cartItem->delete();
            }
        }
    }

    /** @param array<int, int> $productIds
     * @return Collection<int, Product>
     */
    private function lockedProductsFor(array $productIds): Collection
    {
        return Product::query()
            ->whereKey($productIds)
            ->with('priceTiers')
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }
}
