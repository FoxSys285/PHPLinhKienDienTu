<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PaymentIntent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomaticPromotionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['store.standard_shipping_fee' => 30000, 'store.free_shipping_threshold' => 500000, 'store.vat_rate' => 0, 'services.payment.driver' => 'mock']);
    }

    public function test_checkout_shows_best_offer_and_cod_saves_the_same_total(): void
    {
        $user = User::factory()->create();
        $cart = $this->cart($user, 200000);
        $best = Coupon::factory()->create(['code' => 'BEST20', 'title' => 'Best offer', 'discount_value' => 20]);
        Coupon::factory()->create(['discount_value' => 10]);

        $this->actingAs($user)->get(route('checkout.create'))->assertOk()
            ->assertSee('BEST20')->assertSee('Best offer')->assertSee('190.000');

        $this->post(route('checkout.store'), [...$this->checkoutData(), 'discount_total' => 199999, 'total' => 1])
            ->assertRedirect()->assertSessionHasNoErrors();

        $order = Order::query()->firstOrFail();
        $this->assertSame($best->id, $order->coupon_id);
        $this->assertSame('BEST20', $order->coupon_code);
        $this->assertSame('40000.00', $order->discount_total);
        $this->assertSame('190000.00', $order->total);
        $this->assertSame(1, $best->fresh()->used_count);
        $this->assertSame(0, $cart->items()->count());
    }

    public function test_discount_only_uses_eligible_categories_and_respects_the_cap(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $cart = $this->cart($user, 200000, $category);
        $other = Product::factory()->create(['price' => 100000, 'stock' => 10]);
        $cart->items()->create(['product_id' => $other->id, 'quantity' => 1]);
        Coupon::factory()->create(['code' => 'CATEGORY20', 'discount_value' => 20, 'maximum_discount' => 25000, 'category_slugs' => [$category->slug]]);

        $this->actingAs($user)->postJson(route('checkout.promotion'))->assertOk()
            ->assertJsonPath('html', fn (string $html): bool => str_contains($html, 'CATEGORY20') && str_contains($html, '25.000') && str_contains($html, '305.000'));
    }

    public function test_unrelated_items_do_not_satisfy_category_minimum(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $cart = $this->cart($user, 50000, $category);
        $other = Product::factory()->create(['price' => 200000, 'stock' => 10]);
        $cart->items()->create(['product_id' => $other->id, 'quantity' => 1]);
        Coupon::factory()->create(['code' => 'CATEGORY10', 'minimum_purchase' => 100000, 'category_slugs' => [$category->slug]]);

        $this->actingAs($user)->postJson(route('checkout.promotion'))->assertOk()
            ->assertJsonPath('html', fn (string $html): bool => ! str_contains($html, 'CATEGORY10') && str_contains($html, '280.000'));
    }

    public function test_inactive_future_expired_and_exhausted_offers_are_not_applied(): void
    {
        $this->freezeTime();
        $user = User::factory()->create();
        $this->cart($user, 200000);
        Coupon::factory()->create(['discount_value' => 90, 'is_active' => false]);
        Coupon::factory()->create(['discount_value' => 90, 'starts_at' => now()->addMinute()]);
        Coupon::factory()->create(['discount_value' => 90, 'expires_at' => now()->subMinute()]);
        Coupon::factory()->create(['discount_value' => 90, 'usage_limit' => 1, 'used_count' => 1]);

        $this->actingAs($user)->post(route('checkout.store'), $this->checkoutData())->assertSessionHasNoErrors()->assertRedirect();

        $order = Order::query()->firstOrFail();
        $this->assertNull($order->coupon_id);
        $this->assertSame('0.00', $order->discount_total);
        $this->assertSame('230000.00', $order->total);
    }

    public function test_fixed_discount_cannot_exceed_eligible_product_value(): void
    {
        $user = User::factory()->create();
        $this->cart($user, 50000);
        Coupon::factory()->create(['code' => 'FIXED', 'type' => 'fixed', 'discount_value' => 100000, 'minimum_purchase' => 0]);

        $this->actingAs($user)->post(route('checkout.store'), $this->checkoutData())->assertSessionHasNoErrors();

        $order = Order::query()->firstOrFail();
        $this->assertSame('50000.00', $order->discount_total);
        $this->assertSame('30000.00', $order->total);
    }

    public function test_selection_compares_shipping_and_vat_instead_of_only_discount(): void
    {
        config(['store.vat_rate' => 10]);
        $user = User::factory()->create();
        $this->cart($user, 510000);
        Coupon::factory()->create(['code' => 'TEN', 'type' => 'fixed', 'discount_value' => 10000]);
        Coupon::factory()->create(['code' => 'TWENTY', 'type' => 'fixed', 'discount_value' => 20000]);

        $this->actingAs($user)->post(route('checkout.store'), $this->checkoutData())->assertSessionHasNoErrors();

        $order = Order::query()->firstOrFail();
        $this->assertSame('TEN', $order->coupon_code);
        $this->assertSame('50000.00', $order->vat_total);
        $this->assertSame('0.00', $order->shipping_total);
        $this->assertSame('550000.00', $order->total);
    }

    public function test_invalid_manual_code_cannot_create_an_order_or_use_a_coupon(): void
    {
        $user = User::factory()->create();
        $cart = $this->cart($user, 200000);
        $coupon = Coupon::factory()->create();

        $this->actingAs($user)->postJson(route('checkout.promotion'), ['coupon_code' => 'UNKNOWN'])->assertUnprocessable()->assertJsonValidationErrors('coupon_code');
        $this->post(route('checkout.store'), [...$this->checkoutData(), 'coupon_code' => 'UNKNOWN'])->assertSessionHasErrors('coupon_code');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(0, $coupon->fresh()->used_count);
        $this->assertSame(1, $cart->items()->count());
    }

    public function test_manual_code_is_compared_with_automatic_offers_without_stacking(): void
    {
        $user = User::factory()->create();
        $this->cart($user, 200000);
        Coupon::factory()->create(['code' => 'AUTO20', 'discount_value' => 20]);
        Coupon::factory()->create(['code' => 'MANUAL10', 'discount_value' => 10, 'is_automatic' => false]);

        $this->actingAs($user)->post(route('checkout.store'), [...$this->checkoutData(), 'coupon_code' => ' manual10 '])->assertSessionHasNoErrors();

        $order = Order::query()->firstOrFail();
        $this->assertSame('AUTO20', $order->coupon_code);
        $this->assertSame('40000.00', $order->discount_total);
    }

    public function test_manual_only_offer_is_applied_when_a_valid_code_is_entered(): void
    {
        $user = User::factory()->create();
        $this->cart($user, 200000);
        Coupon::factory()->create(['code' => 'PRIVATE15', 'discount_value' => 15, 'is_automatic' => false]);

        $this->actingAs($user)->postJson(route('checkout.promotion'))->assertOk()
            ->assertJsonPath('html', fn (string $html): bool => ! str_contains($html, 'PRIVATE15'));
        $this->postJson(route('checkout.promotion'), ['coupon_code' => 'private15'])->assertOk()
            ->assertJsonPath('html', fn (string $html): bool => str_contains($html, 'PRIVATE15') && str_contains($html, '200.000'));
        $this->post(route('checkout.store'), [...$this->checkoutData(), 'coupon_code' => 'private15'])->assertSessionHasNoErrors();

        $order = Order::query()->firstOrFail();
        $this->assertSame('PRIVATE15', $order->coupon_code);
        $this->assertSame('30000.00', $order->discount_total);
        $this->assertSame('200000.00', $order->total);
    }

    public function test_expired_online_payment_releases_the_reserved_coupon_use(): void
    {
        $this->freezeTime();
        $user = User::factory()->create();
        $this->cart($user, 200000);
        $coupon = Coupon::factory()->create(['usage_limit' => 1]);

        $this->actingAs($user)->post(route('checkout.store'), [...$this->checkoutData(), 'payment_method' => 'bank_transfer'])->assertSessionHasNoErrors();
        $intent = PaymentIntent::query()->firstOrFail();
        $this->travel(16)->minutes();
        $this->get(route('checkout.mock-payment.show', $intent->public_token))->assertRedirect();

        $this->assertSame(0, $coupon->fresh()->used_count);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_last_use_is_rechecked_after_preview(): void
    {
        $user = User::factory()->create();
        $this->cart($user, 200000);
        $coupon = Coupon::factory()->create(['code' => 'LASTUSE', 'usage_limit' => 1]);
        $this->actingAs($user)->get(route('checkout.create'))->assertOk()->assertSee('LASTUSE');
        $coupon->update(['used_count' => 1]);

        $this->post(route('checkout.store'), $this->checkoutData())->assertSessionHasNoErrors();

        $this->assertNull(Order::query()->firstOrFail()->coupon_id);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_online_payment_reserves_once_and_preserves_promotion_snapshot(): void
    {
        $user = User::factory()->create();
        $this->cart($user, 200000);
        $coupon = Coupon::factory()->create(['code' => 'ONLINE20', 'title' => 'Original promotion', 'discount_value' => 20]);

        $this->actingAs($user)->post(route('checkout.store'), [...$this->checkoutData(), 'payment_method' => 'bank_transfer'])->assertSessionHasNoErrors()->assertRedirect();
        $intent = PaymentIntent::query()->firstOrFail();
        $this->assertSame(190000, $intent->amount);
        $this->assertSame('ONLINE20', $intent->payload['pricing']['coupon_code']);
        $this->assertSame(1, $coupon->fresh()->used_count);

        $this->post(route('checkout.store'), [...$this->checkoutData(), 'payment_method' => 'bank_transfer'])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('payment_intents', 1);
        $this->assertSame(1, $coupon->fresh()->used_count);
        $coupon->update(['title' => 'Edited later', 'is_active' => false]);

        $this->post(route('checkout.mock-payment.complete', $intent->public_token), ['outcome' => 'success'])->assertRedirect();
        $order = Order::query()->firstOrFail();
        $this->assertSame('Original promotion', $order->coupon_title);
        $this->assertSame('190000.00', $order->total);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_cancelled_payment_returns_coupon_use(): void
    {
        $user = User::factory()->create();
        $this->cart($user, 200000);
        $coupon = Coupon::factory()->create(['usage_limit' => 1]);

        $this->actingAs($user)->post(route('checkout.store'), [...$this->checkoutData(), 'payment_method' => 'bank_transfer'])->assertSessionHasNoErrors();
        $intent = PaymentIntent::query()->firstOrFail();
        $this->post(route('checkout.mock-payment.complete', $intent->public_token), ['outcome' => 'failed'])->assertRedirect();

        $this->assertSame(0, $coupon->fresh()->used_count);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_guest_checkout_also_automatically_applies_eligible_offer(): void
    {
        $product = Product::factory()->create(['price' => 200000, 'stock' => 10]);
        Coupon::factory()->create(['code' => 'GUEST10']);

        $this->post(route('cart.store', $product), ['quantity' => 1])->assertRedirect();
        $cart = Cart::query()->firstOrFail();
        $this->withCookie('cart_token', $cart->token)->get(route('checkout.create'))->assertOk()->assertSee('GUEST10');
    }

    private function cart(User $user, float $price, ?Category $category = null): Cart
    {
        $product = Product::factory()->create(['price' => $price, 'stock' => 10, ...($category ? ['category_id' => $category->id] : [])]);
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

        return $cart;
    }

    /** @return array<string, mixed> */
    private function checkoutData(): array
    {
        return [
            'customer_name' => 'Test Buyer', 'customer_phone' => '0900000000',
            'shipping_address' => '12 Test Street', 'shipping_province_code' => '79',
            'shipping_city' => 'Ho Chi Minh', 'shipping_ward_code' => '00001', 'shipping_ward_name' => 'Test Ward',
            'payment_method' => 'cod', 'privacy_accepted' => 1,
        ];
    }
}
