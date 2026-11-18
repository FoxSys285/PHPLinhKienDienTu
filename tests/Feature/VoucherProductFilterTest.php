<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VoucherProductFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_all_link_includes_the_current_voucher(): void
    {
        $voucher = Coupon::factory()->create(['code' => 'SENSOR8']);

        $this->get(route('promotions.show', $voucher->slug))->assertOk()
            ->assertSee(route('products.index', ['voucher' => 'SENSOR8']), false);
    }

    public function test_listing_and_filter_options_only_include_voucher_categories(): void
    {
        $category = Category::factory()->create();
        $voucher = Coupon::factory()->create(['code' => 'SENSOR8', 'category_slugs' => [$category->slug]]);
        $eligible = Product::factory()->create([
            'category_id' => $category->id, 'name' => 'Eligible Sensor', 'brand' => 'SensorBrand',
            'specifications' => ['Interface' => 'EligibleInterface'],
        ]);
        Product::factory()->create([
            'name' => 'Unrelated Board', 'brand' => 'OtherBrand', 'specifications' => ['Interface' => 'UnrelatedInterface'],
        ]);
        Product::factory()->create(['category_id' => $category->id, 'name' => 'Hidden Sensor', 'is_active' => false]);

        $response = $this->get(route('products.index', ['voucher' => ' sensor8 ']));

        $response->assertOk()->assertSee('SENSOR8')->assertSee('Eligible Sensor')
            ->assertDontSee('Unrelated Board')->assertDontSee('Hidden Sensor');
        $this->assertSame([$eligible->id], $response->viewData('products')->getCollection()->modelKeys());
        $this->assertSame(['SensorBrand'], $response->viewData('brands')->all());
        $this->assertSame(['Interface' => ['EligibleInterface']], $response->viewData('specFilters'));
        $this->assertSame($voucher->id, $response->viewData('voucher')->id);
    }

    public function test_storewide_voucher_includes_all_active_product_categories(): void
    {
        Coupon::factory()->create(['code' => 'SAVE30', 'category_slugs' => []]);
        $first = Product::factory()->create();
        $second = Product::factory()->create();

        $response = $this->get(route('products.index', ['voucher' => 'SAVE30']));

        $response->assertOk();
        $this->assertSame(2, $response->viewData('products')->total());
        $this->assertEqualsCanonicalizing([$first->id, $second->id], $response->viewData('products')->getCollection()->modelKeys());
    }

    public function test_voucher_filter_combines_with_search_brand_price_stock_and_specification(): void
    {
        $category = Category::factory()->create();
        Coupon::factory()->create(['code' => 'SENSOR8', 'category_slugs' => [$category->slug]]);
        $eligible = Product::factory()->create([
            'category_id' => $category->id, 'name' => 'Precision Sensor', 'brand' => 'LabBrand',
            'price' => 150000, 'stock' => 10, 'reserved_stock' => 1, 'specifications' => ['Interface' => 'I2C'],
        ]);
        Product::factory()->create([
            'category_id' => $category->id, 'name' => 'Expensive Sensor', 'brand' => 'LabBrand',
            'price' => 250000, 'stock' => 10, 'specifications' => ['Interface' => 'I2C'],
        ]);
        Product::factory()->create([
            'category_id' => $category->id, 'name' => 'Reserved Sensor', 'brand' => 'LabBrand',
            'price' => 150000, 'stock' => 5, 'reserved_stock' => 5, 'specifications' => ['Interface' => 'I2C'],
        ]);
        Product::factory()->create([
            'category_id' => $category->id, 'name' => 'Analog Sensor', 'brand' => 'LabBrand',
            'price' => 150000, 'stock' => 10, 'specifications' => ['Interface' => 'Analog'],
        ]);
        Product::factory()->create([
            'name' => 'Other Sensor', 'brand' => 'LabBrand',
            'price' => 150000, 'stock' => 10, 'specifications' => ['Interface' => 'I2C'],
        ]);

        $response = $this->get(route('products.index', [
            'voucher' => 'SENSOR8', 'q' => 'Sensor', 'brand' => 'LabBrand', 'min_price' => 100000,
            'max_price' => 200000, 'in_stock' => 1, 'spec' => ['Interface' => 'I2C'], 'sort' => 'price_asc',
        ]));

        $response->assertOk();
        $this->assertSame([$eligible->id], $response->viewData('products')->getCollection()->modelKeys());
    }

    public function test_pagination_and_sorting_keep_the_selected_voucher(): void
    {
        $category = Category::factory()->create();
        Coupon::factory()->create(['code' => 'SENSOR8', 'category_slugs' => [$category->slug]]);
        Product::factory()->count(18)->create(['category_id' => $category->id, 'price' => 150000]);
        Product::factory()->create(['price' => 10000]);

        $firstPage = $this->get(route('products.index', ['voucher' => 'SENSOR8', 'sort' => 'price_asc']));
        $firstPage->assertOk();
        $products = $firstPage->viewData('products');
        $this->assertSame(18, $products->total());
        $this->assertSame(16, $products->count());
        parse_str((string) parse_url($products->url(2), PHP_URL_QUERY), $query);
        $this->assertSame('SENSOR8', $query['voucher']);
        $this->assertSame('price_asc', $query['sort']);

        $secondPage = $this->get($products->url(2));
        $secondPage->assertOk();
        $this->assertSame(2, $secondPage->viewData('products')->count());
        $this->assertSame([], array_intersect($products->getCollection()->modelKeys(), $secondPage->viewData('products')->getCollection()->modelKeys()));
    }

    public function test_category_route_intersects_with_voucher_scope(): void
    {
        $included = Category::factory()->create();
        $excluded = Category::factory()->create();
        Coupon::factory()->create(['code' => 'SENSOR8', 'category_slugs' => [$included->slug]]);
        Product::factory()->create(['category_id' => $included->id]);
        Product::factory()->create(['category_id' => $excluded->id]);

        $response = $this->get(route('categories.show', ['category' => $excluded, 'voucher' => 'SENSOR8']));

        $response->assertOk();
        $this->assertSame(0, $response->viewData('products')->total());
    }

    #[DataProvider('unavailableVoucherStates')]
    public function test_unavailable_voucher_cannot_open_an_unfiltered_catalog(array $attributes): void
    {
        $this->freezeTime();
        Coupon::factory()->create(['code' => 'UNAVAILABLE', ...$attributes]);
        Product::factory()->create();

        $this->get(route('products.index', ['voucher' => 'UNAVAILABLE']))->assertNotFound();
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function unavailableVoucherStates(): array
    {
        return [
            'paused' => [['is_active' => false]],
            'expired' => [['expires_at' => '2000-01-01']],
            'scheduled' => [['starts_at' => '2099-01-01']],
            'exhausted' => [['usage_limit' => 1, 'used_count' => 1]],
        ];
    }

    public function test_unknown_voucher_is_not_silently_ignored(): void
    {
        $this->get(route('products.index', ['voucher' => 'UNKNOWN']))->assertNotFound();
    }

    public function test_malformed_voucher_is_rejected_as_invalid_input(): void
    {
        $this->getJson(route('products.index', ['voucher' => ['SENSOR8']]))
            ->assertUnprocessable()->assertJsonValidationErrors('voucher');
    }

    public function test_removing_voucher_preserves_other_filters_and_resets_pagination(): void
    {
        Coupon::factory()->create(['code' => 'SAVE30', 'category_slugs' => []]);

        $this->get(route('products.index', ['voucher' => 'SAVE30', 'q' => 'Sensor', 'sort' => 'name', 'page' => 2]))
            ->assertOk()
            ->assertSee(route('products.index', ['q' => 'Sensor', 'sort' => 'name']), true);
    }
}
