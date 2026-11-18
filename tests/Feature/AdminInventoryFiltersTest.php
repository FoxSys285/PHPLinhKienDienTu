<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInventoryFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_products_by_category_stock_and_listing_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create(['name' => 'Bán dẫn']);
        $otherCategory = Category::factory()->create(['name' => 'Board']);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Low stock transistor',
            'stock' => 8,
            'reserved_stock' => 3,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Inactive transistor',
            'stock' => 8,
            'reserved_stock' => 3,
            'minimum_stock' => 5,
            'is_active' => false,
        ]);
        Product::factory()->create([
            'category_id' => $otherCategory->id,
            'name' => 'Other category transistor',
            'stock' => 8,
            'reserved_stock' => 3,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.inventory.index', [
            'category_id' => $category->id,
            'stock_status' => 'low',
            'product_status' => 'active',
        ]));

        $response->assertOk()
            ->assertSee('Low stock transistor');

        $products = $response->viewData('products');
        $this->assertSame(
            ['Low stock transistor'],
            $products->getCollection()->pluck('name')->all(),
        );
    }

    public function test_admin_can_filter_inventory_movements_by_product_type_and_date_range(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['name' => 'Filtered regulator']);
        $otherProduct = Product::factory()->create(['name' => 'Unrelated capacitor']);
        $matchingMovement = $product->inventoryMovements()->create([
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 12,
            'reference' => 'DEMO-RECEIPT-1',
            'lot_number' => 'LOT-ACME-1',
            'note' => 'Supplier Acme',
        ]);
        $oldMovement = $product->inventoryMovements()->create([
            'user_id' => $admin->id,
            'type' => 'in',
            'quantity' => 4,
            'reference' => 'OLD-RECEIPT',
        ]);
        $otherMovement = $otherProduct->inventoryMovements()->create([
            'user_id' => $admin->id,
            'type' => 'out',
            'quantity' => -2,
            'reference' => 'OTHER-ORDER',
        ]);
        $oldMovement->forceFill(['created_at' => now()->subDays(10)])->saveQuietly();
        $otherMovement->forceFill(['created_at' => now()->subDays(2)])->saveQuietly();

        $response = $this->actingAs($admin)->get(route('admin.inventory.index', [
            'activity_q' => 'Filtered regulator',
            'movement_type' => 'in',
            'date_from' => now()->subDays(1)->toDateString(),
            'date_to' => now()->toDateString(),
        ]));

        $response->assertOk()
            ->assertSee('DEMO-RECEIPT-1')
            ->assertSee('Filtered regulator');

        $movements = $response->viewData('movements');
        $this->assertSame(
            [$matchingMovement->id],
            $movements->getCollection()->modelKeys(),
        );
    }

    public function test_inventory_filters_reject_an_end_date_before_the_start_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.inventory.index', [
            'date_from' => '2026-11-18',
            'date_to' => '2026-11-17',
        ]));

        $response->assertRedirect()
            ->assertSessionHasErrors('date_to');
    }

    public function test_sales_staff_cannot_view_inventory_filters(): void
    {
        $salesUser = User::factory()->create(['role' => 'sales']);

        $this->actingAs($salesUser)->get(route('admin.inventory.index'))->assertForbidden();
    }
}
