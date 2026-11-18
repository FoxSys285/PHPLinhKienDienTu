<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DemoCommerceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoCommerceSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_seeder_creates_repeatable_customer_orders_and_reviews(): void
    {
        $this->app['env'] = 'local';
        Product::factory()->count(3)->create();

        app(DemoCommerceSeeder::class)->run();
        $initialCounts = $this->demoCounts();

        app(DemoCommerceSeeder::class)->run();
        $repeatedCounts = $this->demoCounts();

        $this->assertSame(12, $repeatedCounts['users']);
        $this->assertSame(24, $repeatedCounts['orders']);
        $this->assertGreaterThan(0, $repeatedCounts['reviews']);
        $this->assertSame($initialCounts, $repeatedCounts);
        $this->assertDatabaseHas('users', [
            'email' => 'demo.customer.01@example.test',
            'role' => 'customer',
        ]);
        $this->assertDatabaseHas('orders', ['order_number' => 'DEMO-26-0001']);
        $this->assertDatabaseHas('reviews', ['is_approved' => true]);
    }

    public function test_seeder_does_not_create_demo_data_outside_local_environment(): void
    {
        $this->app['env'] = 'production';
        Product::factory()->count(2)->create();

        app(DemoCommerceSeeder::class)->run();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('reviews', 0);
    }

    /** @return array{users: int, orders: int, reviews: int} */
    private function demoCounts(): array
    {
        return [
            'users' => User::query()->where('email', 'like', 'demo.customer.%@example.test')->count(),
            'orders' => Order::query()->where('order_number', 'like', 'DEMO-26-%')->count(),
            'reviews' => Review::query()->where('body', 'like', '%Dữ liệu đánh giá minh họa%')->count(),
        ];
    }
}
