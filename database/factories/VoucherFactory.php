<?php

namespace Database\Factories;

use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Voucher>
 */
class VoucherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => Str::upper(fake()->unique()->bothify('MV-####??')),
            'discount_type' => 'percent',
            'discount_value' => 10,
            'max_discount' => 100000,
            'minimum_order' => 100000,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'usage_limit' => 100,
            'used_count' => 0,
            'is_active' => true,
        ];
    }
}
