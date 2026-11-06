<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlaced;
use App\Services\OrderService;
use App\Services\PayOSService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PayOSWebhookController extends Controller
{
    public function __invoke(Request $request, PayOSService $payOS, OrderService $orders): JsonResponse
    {
        abort_unless(config('services.payment.driver') === 'payos', 404);

        $payload = $request->all();

        if (! $payOS->verifyWebhook($payload)) {
            return response()->json(['success' => false, 'message' => 'Invalid signature.'], 400);
        }

        $paymentData = $payload['data'] ?? [];

        if (($payload['success'] ?? false) !== true || ($payload['code'] ?? null) !== '00' || ($paymentData['code'] ?? null) !== '00') {
            return response()->json(['success' => true]);
        }

        $result = $orders->fulfillPayOSIntent($paymentData);

        if (! $result) {
            Log::critical('Verified PayOS payment could not be matched to an order.', [
                'order_code' => $paymentData['orderCode'] ?? null,
                'amount' => $paymentData['amount'] ?? null,
                'payment_link_id' => $paymentData['paymentLinkId'] ?? null,
            ]);

            return response()->json(['success' => true]);
        }

        $order = $result['order'];

        if ($result['created'] && $order->customer_email) {
            try {
                Mail::to($order->customer_email)->send(new OrderPlaced($order));
            } catch (\Throwable $exception) {
                Log::warning('Order confirmation email could not be sent.', [
                    'order_id' => $order->id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        return response()->json(['success' => true]);
    }
}
