<?php

namespace App\Services;

use App\Models\PaymentIntent;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayOSService
{
    public function createPaymentLink(PaymentIntent $intent): string
    {
        $clientId = (string) config('services.payos.client_id');
        $apiKey = (string) config('services.payos.api_key');
        $checksumKey = (string) config('services.payos.checksum_key');

        if ($clientId === '' || $apiKey === '' || $checksumKey === '') {
            throw new RuntimeException('PayOS chưa được cấu hình đầy đủ.');
        }

        $returnUrl = route('checkout.payos.return', ['token' => $intent->public_token]);
        $cancelUrl = $returnUrl.'?cancelled=1';
        $signedData = [
            'amount' => $intent->amount,
            'cancelUrl' => $cancelUrl,
            'description' => $intent->description,
            'orderCode' => $intent->order_code,
            'returnUrl' => $returnUrl,
        ];
        ksort($signedData);
        $signatureData = implode('&', array_map(
            static fn (string $key, int|string $value): string => $key.'='.$value,
            array_keys($signedData),
            array_values($signedData),
        ));

        $requestData = [
            ...$signedData,
            'signature' => hash_hmac('sha256', $signatureData, $checksumKey),
            'expiredAt' => $intent->expires_at->timestamp,
            'items' => array_map(static fn (array $line): array => [
                'name' => mb_substr($line['name'], 0, 100),
                'quantity' => $line['quantity'],
                'price' => (int) round($line['unit_price']),
            ], $intent->payload['lines']),
            'buyerName' => $intent->payload['checkout']['customer_name'],
            'buyerEmail' => $intent->payload['checkout']['customer_email'] ?? null,
            'buyerPhone' => $intent->payload['checkout']['customer_phone'],
        ];

        try {
            $response = Http::withHeaders([
                'x-client-id' => $clientId,
                'x-api-key' => $apiKey,
            ])->acceptJson()->timeout(15)->post('https://api-merchant.payos.vn/v2/payment-requests', $requestData);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Không thể kết nối PayOS. Vui lòng thử lại sau.', previous: $exception);
        }

        $paymentData = $response->json('data');

        if (! $response->successful() || $response->json('code') !== '00' || ! is_array($paymentData) || empty($paymentData['checkoutUrl'])) {
            throw new RuntimeException('PayOS không tạo được liên kết thanh toán.');
        }

        $intent->update([
            'status' => 'pending',
            'payment_link_id' => $paymentData['paymentLinkId'] ?? null,
            'checkout_url' => $paymentData['checkoutUrl'],
            'qr_code' => $paymentData['qrCode'] ?? null,
        ]);

        return $paymentData['checkoutUrl'];
    }

    /** @param array<string, mixed> $payload */
    public function verifyWebhook(array $payload): bool
    {
        $data = $payload['data'] ?? null;
        $signature = $payload['signature'] ?? null;
        $checksumKey = (string) config('services.payos.checksum_key');

        if (! is_array($data) || ! is_string($signature) || $checksumKey === '') {
            return false;
        }

        ksort($data);
        $signatureParts = [];

        foreach ($data as $key => $value) {
            if ($value === null || $value === 'null' || $value === 'undefined') {
                $value = '';
            } elseif (is_array($value)) {
                $value = json_encode(array_map(static function (mixed $item): mixed {
                    if (! is_array($item)) {
                        return $item;
                    }

                    ksort($item);

                    return $item;
                }, $value), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            } elseif (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            $signatureParts[] = $key.'='.$value;
        }

        $expectedSignature = hash_hmac('sha256', implode('&', $signatureParts), $checksumKey);

        return hash_equals(strtolower($expectedSignature), strtolower($signature));
    }
}
