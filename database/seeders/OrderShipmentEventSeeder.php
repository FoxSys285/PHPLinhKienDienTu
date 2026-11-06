<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderShipmentEvent;
use Illuminate\Database\Seeder;

class OrderShipmentEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $order = Order::query()->whereNotNull('shipment_number')->first();

        if (! $order || $order->shipmentEvents()->exists()) {
            return;
        }

        OrderShipmentEvent::factory()->create([
            'order_id' => $order->id,
            'provider_event_id' => 'local-demo-'.$order->id,
            'status' => $order->shipment_status,
            'description' => 'Sự kiện minh họa cho môi trường local.',
        ]);
    }
}
