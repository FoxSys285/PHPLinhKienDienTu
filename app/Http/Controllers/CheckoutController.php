<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Models\PaymentIntent;
use App\Services\OrderService;
use App\Services\PayOSService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(Request $request, CartController $cartController): Response|RedirectResponse
    {
        $cart = $cartController->currentCart($request);
        $cart->load('items.product.priceTiers');

        if ($cart->items->isEmpty()) {
            $pendingToken = $request->session()->get('payos_pending_intent_token');

            if (is_string($pendingToken) && PaymentIntent::query()->where('public_token', $pendingToken)->exists()) {
                return redirect()->route('checkout.payos.return', ['token' => $pendingToken]);
            }

            return redirect()->route('cart.index')->with('error', 'Giỏ hàng đang trống.');
        }

        $priceGroup = $request->user()?->account_type === 'business' && $request->user()->business_approved
            ? $request->user()->price_group
            : 'retail';
        $subtotal = $cart->items->sum(fn ($item): float => $item->product->priceFor((int) $item->quantity, $priceGroup) * $item->quantity);
        $addresses = $request->user()?->addresses()->orderByDesc('is_default')->latest()->get() ?? collect();

        return response()->view('checkout.create', [
            'cart' => $cart,
            'subtotal' => $subtotal,
            'shippingFee' => $subtotal >= (float) config('store.free_shipping_threshold') ? 0 : (float) config('store.standard_shipping_fee'),
            'user' => $request->user(),
            'addresses' => $addresses,
            'paymentDriver' => config('services.payment.driver'),
        ])->withCookie($cartController->cartCookie($cart));
    }

    public function store(Request $request, CartController $cartController, OrderService $orders, PayOSService $payOS): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'shipping_province_code' => ['required', 'string', 'max:10'],
            'shipping_city' => ['required', 'string', 'max:120'],
            'shipping_ward_code' => ['required', 'string', 'max:10'],
            'shipping_ward_name' => ['required', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:30'],
            'payment_method' => ['required', Rule::in(['cod', 'bank_transfer'])],
            'coupon_code' => ['nullable', 'string', 'max:60'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'privacy_accepted' => ['accepted'],
        ]);

        $data['shipping_address'] = implode(', ', array_filter([
            trim($data['shipping_address']),
            $data['shipping_ward_name'],
        ]));

        $cart = $cartController->currentCart($request);

        if ($data['payment_method'] === 'bank_transfer') {
            $paymentDriver = (string) config('services.payment.driver');

            if (! in_array($paymentDriver, ['mock', 'payos'], true)) {
                throw ValidationException::withMessages(['payment_method' => 'Cấu hình phương thức thanh toán không hợp lệ.']);
            }

            $intent = $orders->createPayOSIntent($cart, $data, $request->user());
            $intentUsesMock = str_starts_with((string) $intent->payment_link_id, 'MOCK-');

            if ($intent->status === 'pending' && $intent->checkout_url && $paymentDriver === 'mock' && ! $intentUsesMock) {
                throw ValidationException::withMessages(['payment_method' => 'Giỏ hàng đang có liên kết PayOS thật còn hiệu lực. Hãy hoàn tất hoặc chờ liên kết hết hạn trước khi chuyển sang mô phỏng.']);
            }

            if ($intent->status === 'pending' && $intentUsesMock && $paymentDriver === 'payos') {
                $orders->cancelMockPayment($intent);
                $intent = $orders->createPayOSIntent($cart, $data, $request->user());
                $intentUsesMock = false;
            }

            if ($paymentDriver === 'mock') {
                $mockPaymentUrl = route('checkout.mock-payment.show', ['token' => $intent->public_token]);
                $orders->prepareMockPayment($intent, $mockPaymentUrl);
                $request->session()->put('payos_pending_intent_token', $intent->public_token);

                return redirect()->route('checkout.mock-payment.show', ['token' => $intent->public_token])->withCookie($cartController->cartCookie($cart));
            }

            if ($intent->status === 'pending' && $intent->checkout_url) {
                $request->session()->put('payos_pending_intent_token', $intent->public_token);

                return redirect()->away($intent->checkout_url)->withCookie($cartController->cartCookie($cart));
            }

            try {
                $checkoutUrl = $payOS->createPaymentLink($intent);
            } catch (\Throwable $exception) {
                $orders->cancelPayOSIntent($intent);
                Log::error('PayOS payment link could not be created.', [
                    'payment_intent_id' => $intent->id,
                    'exception' => $exception->getMessage(),
                ]);

                throw ValidationException::withMessages([
                    'payment_method' => 'Chưa thể tạo thanh toán PayOS. Vui lòng kiểm tra cấu hình hoặc thử lại sau.',
                ]);
            }

            $request->session()->put('payos_pending_intent_token', $intent->public_token);

            return redirect()->away($checkoutUrl)->withCookie($cartController->cartCookie($cart));
        }

        $order = $orders->placeOrder($cart, $data, $request->user());

        if ($order->customer_email) {
            try {
                Mail::to($order->customer_email)->send(new OrderPlaced($order));
            } catch (\Throwable $exception) {
                Log::warning('Order confirmation email could not be sent.', [
                    'order_id' => $order->id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        if (! $request->user()) {
            $request->session()->put('last_guest_order', $order->order_number);
        }

        return redirect()
            ->route('checkout.success', $order)
            ->with('success', 'Đặt hàng thành công. Mã đơn của bạn là '.$order->order_number.'.')
            ->withCookie($cartController->cartCookie($cart));
    }

    public function success(Request $request, Order $order): View
    {
        $isOwner = $request->user()?->id === $order->user_id;
        $isGuestOrder = $order->user_id === null && $request->session()->get('last_guest_order') === $order->order_number;
        $isPayOSOrder = is_string($request->query('payment_token'))
            && PaymentIntent::query()
                ->where('public_token', $request->query('payment_token'))
                ->where('order_id', $order->id)
                ->exists();

        abort_unless($isOwner || $isGuestOrder || $isPayOSOrder, 404);

        return view('checkout.success', ['order' => $order->load('items', 'shipmentEvents', 'paymentIntent')]);
    }

    public function showMockPayment(string $token, OrderService $orders): View|RedirectResponse
    {
        abort_unless(config('services.payment.driver') === 'mock', 404);

        $intent = PaymentIntent::query()->where('public_token', $token)->firstOrFail();

        if (str_starts_with((string) $intent->payment_link_id, 'MOCK-') && $intent->status === 'paid' && $intent->order) {
            return redirect()->route('checkout.success', ['order' => $intent->order, 'payment_token' => $intent->public_token]);
        }

        if ($intent->status === 'pending' && str_starts_with((string) $intent->payment_link_id, 'MOCK-') && $intent->expires_at?->isPast()) {
            $orders->cancelMockPayment($intent);

            return redirect()->route('checkout.payos.return', ['token' => $token, 'cancelled' => 1]);
        }

        if ($intent->status !== 'pending' || ! str_starts_with((string) $intent->payment_link_id, 'MOCK-')) {
            return redirect()->route('checkout.payos.return', ['token' => $token, 'cancelled' => $intent->status === 'cancelled']);
        }

        return view('checkout.mock-payment', ['intent' => $intent]);
    }

    public function completeMockPayment(Request $request, string $token, OrderService $orders): RedirectResponse
    {
        abort_unless(config('services.payment.driver') === 'mock', 404);

        $data = $request->validate(['outcome' => ['required', Rule::in(['success', 'failed'])]]);
        $intent = PaymentIntent::query()->where('public_token', $token)->firstOrFail();

        if (! str_starts_with((string) $intent->payment_link_id, 'MOCK-')) {
            abort(404);
        }

        if ($intent->status === 'paid' && $intent->order) {
            return redirect()->route('checkout.success', ['order' => $intent->order, 'payment_token' => $intent->public_token]);
        }

        if ($intent->status !== 'pending' || $intent->expires_at?->isPast()) {
            $orders->cancelMockPayment($intent);

            return redirect()->route('checkout.payos.return', ['token' => $token, 'cancelled' => $intent->status !== 'expired']);
        }

        if ($data['outcome'] === 'failed') {
            $orders->cancelMockPayment($intent);
            $intent->refresh();

            if ($intent->status === 'paid' && $intent->order) {
                return redirect()->route('checkout.success', ['order' => $intent->order, 'payment_token' => $intent->public_token]);
            }

            return redirect()
                ->route('checkout.payos.return', ['token' => $token, 'cancelled' => 1])
                ->with('error', 'Đã mô phỏng giao dịch thất bại. Không có khoản tiền thật nào được thu.');
        }

        $result = $orders->fulfillMockPayment($intent);

        if (! $result) {
            return redirect()->route('checkout.payos.return', ['token' => $token])->withErrors(['payment_method' => 'Không thể hoàn tất giao dịch mô phỏng. Liên kết có thể đã hết hạn.']);
        }

        $order = $result['order'];

        if ($result['created'] && $order->customer_email) {
            try {
                Mail::to($order->customer_email)->send(new OrderPlaced($order));
            } catch (\Throwable $exception) {
                Log::warning('Mock payment order confirmation email could not be sent.', [
                    'order_id' => $order->id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        return redirect()->route('checkout.success', ['order' => $order, 'payment_token' => $intent->public_token]);
    }

    public function showGuestOrder(Order $order, string $token): View
    {
        abort_unless($order->user_id === null && $order->guest_access_token && hash_equals($order->guest_access_token, $token), 404);

        return view('account.order', [
            'order' => $order->load('items', 'shipmentEvents', 'paymentIntent'),
            'guestAccessToken' => $token,
        ]);
    }

    public function cancelGuestOrder(Request $request, Order $order, string $token, OrderService $orders): RedirectResponse
    {
        abort_unless($order->user_id === null && $order->guest_access_token && hash_equals($order->guest_access_token, $token), 404);
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:1000']]);
        $orders->cancel($order, $data['reason']);

        return redirect()->route('checkout.guest-order.show', ['order' => $order, 'token' => $token])->with('success', 'Đơn hàng đã được hủy và lý do đã được lưu.');
    }

    public function payosReturn(Request $request, string $token): View|RedirectResponse
    {
        $intent = PaymentIntent::query()->where('public_token', $token)->firstOrFail();

        if ($intent->status === 'paid' && $intent->order) {
            $request->session()->forget('payos_pending_intent_token');

            return redirect()->route('checkout.success', [
                'order' => $intent->order,
                'payment_token' => $intent->public_token,
            ]);
        }

        return view('checkout.payos-status', [
            'intent' => $intent,
            'wasCancelled' => $request->boolean('cancelled'),
        ]);
    }

    public function payosStatus(string $token): JsonResponse
    {
        $intent = PaymentIntent::query()->where('public_token', $token)->firstOrFail();
        $redirectUrl = $intent->status === 'paid' && $intent->order
            ? route('checkout.success', ['order' => $intent->order, 'payment_token' => $intent->public_token])
            : null;

        return response()->json([
            'status' => $intent->status,
            'redirect_url' => $redirectUrl,
        ]);
    }
}
