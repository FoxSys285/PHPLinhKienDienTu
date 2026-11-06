<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ViettelPostWebhookController extends Controller
{
    public function __invoke(Request $request, string $secret, OrderService $orders): JsonResponse
    {
        $configuredSecret = (string) config('services.viettelpost.webhook_secret');

        abort_unless(
            config('services.viettelpost.driver') === 'live'
                && config('services.viettelpost.webhook_enabled')
                && $configuredSecret !== ''
                && hash_equals($configuredSecret, $secret),
            404,
        );

        $payload = $request->all();
        $fields = config('services.viettelpost.webhook_fields');
        $trackingNumber = data_get($payload, $fields['tracking_number']);
        $orderNumber = data_get($payload, $fields['order_number']);
        $rawStatus = data_get($payload, $fields['status']);

        if (! is_string($rawStatus) && ! is_numeric($rawStatus)) {
            return response()->json(['success' => false, 'message' => 'Missing shipment status.'], 422);
        }

        $order = Order::query()
            ->when($trackingNumber, fn ($query) => $query->where('shipment_number', (string) $trackingNumber))
            ->when(! $trackingNumber && $orderNumber, fn ($query) => $query->where('order_number', (string) $orderNumber))
            ->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        $normalizedStatus = $this->normalizeStatus((string) $rawStatus);

        if (! $normalizedStatus) {
            Log::warning('Unmapped Viettel Post shipment status.', [
                'order_number' => $order->order_number,
                'shipment_number' => $order->shipment_number,
                'provider_status' => (string) $rawStatus,
            ]);

            return response()->json(['success' => false, 'message' => 'Shipment status is not mapped.'], 422);
        }

        $eventId = data_get($payload, $fields['event_id']);
        $eventId = is_scalar($eventId) && (string) $eventId !== ''
            ? 'vtp-'.Str::limit((string) $eventId, 90, '')
            : 'vtp-'.hash('sha256', (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $description = data_get($payload, $fields['description']);
        $description = is_string($description) ? Str::limit($description, 255) : null;
        $occurredAtValue = data_get($payload, $fields['occurred_at']);
        $occurredAt = null;

        if (is_string($occurredAtValue) && $occurredAtValue !== '') {
            try {
                $occurredAt = CarbonImmutable::parse($occurredAtValue);
            } catch (\Throwable) {
                $occurredAt = null;
            }
        }

        try {
            $processed = $orders->transitionShipment(
                order: $order,
                nextStatus: $normalizedStatus,
                source: 'viettelpost',
                providerEventId: $eventId,
                description: $description,
                occurredAt: $occurredAt,
                payload: [
                    'provider_status' => (string) $rawStatus,
                    'tracking_number' => $order->shipment_number,
                    'event_id' => $eventId,
                ],
            );
        } catch (ValidationException $exception) {
            Log::warning('Viettel Post event was ignored because it does not follow the current shipment state.', [
                'order_number' => $order->order_number,
                'shipment_number' => $order->shipment_number,
                'provider_status' => (string) $rawStatus,
                'reason' => $exception->getMessage(),
            ]);

            return response()->json(['success' => true, 'ignored' => true]);
        }

        return response()->json(['success' => true, 'duplicate' => ! $processed]);
    }

    private function normalizeStatus(string $status): ?string
    {
        $key = Str::slug($status, '_');
        $configuredMap = config('services.viettelpost.status_map', []);
        $statusMap = [
            'accepted' => 'handed_over',
            'picked_up' => 'handed_over',
            'received' => 'handed_over',
            'handed_over' => 'handed_over',
            'ban_giao_don_vi_van_chuyen' => 'handed_over',
            'da_ban_giao_don_vi_van_chuyen' => 'handed_over',
            'in_transit' => 'in_transit',
            'transporting' => 'in_transit',
            'dang_trung_chuyen' => 'in_transit',
            'out_for_delivery' => 'out_for_delivery',
            'delivering' => 'out_for_delivery',
            'dang_giao_den_nguoi_nhan' => 'out_for_delivery',
            'delivery_failed' => 'delivery_failed',
            'failed' => 'delivery_failed',
            'giao_khong_thanh_cong' => 'delivery_failed',
            'returning' => 'returning',
            'return_to_sender' => 'returning',
            'dang_hoan_hang' => 'returning',
            'returned' => 'returned',
            'returned_to_sender' => 'returned',
            'da_hoan_hang' => 'returned',
            'delivered' => 'completed',
            'completed' => 'completed',
            'success' => 'completed',
            'da_giao_thanh_cong' => 'completed',
            'giao_thanh_cong' => 'completed',
        ];
        $statusMap = array_merge($statusMap, is_array($configuredMap) ? $configuredMap : []);
        $mappedStatus = $statusMap[$key] ?? null;

        return in_array($mappedStatus, ['handed_over', 'in_transit', 'out_for_delivery', 'delivery_failed', 'returning', 'returned', 'completed'], true)
            ? $mappedStatus
            : null;
    }
}
