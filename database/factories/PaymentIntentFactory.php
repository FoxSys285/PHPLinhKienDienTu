<?php

namespace Database\Factories;

use App\Models\PaymentIntent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PaymentIntent>
 */
class PaymentIntentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'cart_id' => null,
            'order_id' => null,
            'order_code' => fake()->unique()->numberBetween(1000000000000, 9999999999999),
            'public_token' => Str::random(64),
            'status' => 'creating',
            'amount' => fake()->numberBetween(10000, 1000000),
            'description' => 'LK'.fake()->numerify('#######'),
            'payment_link_id' => null,
            'checkout_url' => null,
            'qr_code' => null,
            'payload' => [],
            'reference' => null,
            'expires_at' => now()->addMinutes(15),
            'paid_at' => null,
        ];
    }
}
