<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class MockViettelPostService
{
    /** @return array{shipment_number: string, shipment_service: string, shipment_tracking_url: string, estimated_delivery_at: Carbon} */
    public function createShipment(Order $order): array
    {
        $shipmentNumber = 'VTP-DEMO-'.now()->format('ymd').'-'.Str::upper(Str::random(10));

        return [
            'shipment_number' => $shipmentNumber,
            'shipment_service' => 'Viettel Post · Mô phỏng',
            'shipment_tracking_url' => route('shipping.mock.show', ['shipmentNumber' => $shipmentNumber]),
            'estimated_delivery_at' => now()->addDays(3),
        ];
    }
}
