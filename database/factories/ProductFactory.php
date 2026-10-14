<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'sku' => strtoupper(fake()->unique()->bothify('MV-####??')),
            'description' => fake()->paragraph(),
            'features' => ['Phù hợp cho các dự án DIY', 'Được kiểm tra trước khi giao'],
            'specifications' => [['name' => 'Bảo hành', 'value' => '12 tháng']],
            'gallery_images' => [],
            'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1000&q=85',
            'price' => fake()->numberBetween(20, 500) * 1000,
            'old_price' => null,
            'stock' => fake()->numberBetween(1, 50),
            'sold_count' => fake()->numberBetween(0, 250),
            'badge' => null,
            'is_featured' => false,
            'is_active' => true,
        ];
    }
}
