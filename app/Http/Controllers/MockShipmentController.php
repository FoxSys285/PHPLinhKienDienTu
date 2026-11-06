<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class MockShipmentController extends Controller
{
    public function show(string $shipmentNumber): View
    {
        abort_unless(config('services.viettelpost.driver') === 'mock', 404);

        $order = Order::query()
            ->where('shipment_number', $shipmentNumber)
            ->where('shipment_service', 'Viettel Post · Mô phỏng')
            ->firstOrFail();

        return view('shipping.mock-tracking', ['order' => $order->load('shipmentEvents')]);
    }
}
