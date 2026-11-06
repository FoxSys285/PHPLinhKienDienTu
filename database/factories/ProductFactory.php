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
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'sku' => fake()->unique()->bothify('EL-####'),
            'mpn' => strtoupper(fake()->bothify('??-####')),
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.Str::lower(fake()->bothify('??##')),
            'brand' => fake()->company(),
            'description' => fake()->paragraph(),
            'specifications' => ['Thông số' => fake()->word()],
            'price' => fake()->randomFloat(2, 1000, 1500000),
            'stock' => fake()->numberBetween(0, 500),
            'reserved_stock' => 0,
            'minimum_stock' => 5,
            'moq' => 1,
            'pack_size' => 1,
            'is_rohs' => true,
            'is_esd_sensitive' => false,
            'allow_backorder' => false,
            'is_active' => true,
        ];
    }
}
