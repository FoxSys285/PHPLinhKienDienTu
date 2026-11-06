<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderShipmentEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderShipmentEvent>
 */
class OrderShipmentEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'provider_event_id' => fake()->unique()->uuid(),
            'source' => 'manual',
            'status' => 'in_transit',
            'description' => 'Kiện hàng đang trung chuyển.',
            'occurred_at' => now(),
            'payload' => ['tracking_number' => fake()->bothify('VTP########')],
        ];
    }
}
