<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserAddress>
 */
class UserAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['Nhà riêng', 'Văn phòng']),
            'recipient_name' => fake()->name(),
            'recipient_phone' => fake()->numerify('09########'),
            'province_code' => '79',
            'province_name' => 'Hồ Chí Minh',
            'ward_code' => '26740',
            'ward_name' => 'Phường Sài Gòn',
            'address_line' => fake()->streetAddress(),
            'note' => fake()->optional()->sentence(),
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes): array => ['is_default' => true]);
    }
}
