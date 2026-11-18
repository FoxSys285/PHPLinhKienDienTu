<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('PROMO-????##')),
            'type' => 'percentage',
            'discount_value' => 10,
            'minimum_purchase' => 100000,
            'used_count' => 0,
            'is_active' => true,
            'is_automatic' => true,
            'slug' => fake()->unique()->slug(),
            'title' => fake()->sentence(4),
            'eyebrow' => 'Ưu đãi linh kiện',
            'description' => fake()->sentence(),
            'theme' => 'blue',
            'category_slugs' => [],
            'image_path' => 'images/promotions/maker.png',
        ];
    }
}
