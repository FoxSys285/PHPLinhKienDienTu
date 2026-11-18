<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\PromotionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotion_detail_shows_only_products_from_its_category(): void
    {
        $this->seed(PromotionSeeder::class);
        $sensorCategory = Category::factory()->create(['slug' => 'module-cam-bien', 'name' => 'Module cảm biến']);
        $otherCategory = Category::factory()->create(['slug' => 'other-category']);
        Product::factory()->create(['category_id' => $sensorCategory->id, 'name' => 'BH1750 Sensor Test']);
        Product::factory()->create(['category_id' => $otherCategory->id, 'name' => 'Unrelated Board Test']);

        $response = $this->get(route('promotions.show', 'sensor-lab'));

        $response->assertOk()
            ->assertSee('SENSOR8')
            ->assertSee('BH1750 Sensor Test')
            ->assertDontSee('Unrelated Board Test');
    }

    public function test_promotion_detail_displays_real_product_prices_and_fixed_discount_conditions(): void
    {
        $promotion = Coupon::factory()->create([
            'code' => 'SAVE30', 'type' => 'fixed', 'discount_value' => 30000,
            'minimum_purchase' => 350000, 'category_slugs' => [],
        ]);
        Product::factory()->create([
            'name' => 'Actual Price Board', 'price' => 190000, 'compare_at_price' => 220000, 'stock' => 10,
        ]);

        $this->get(route('promotions.show', $promotion->slug))
            ->assertOk()
            ->assertSee('Actual Price Board')
            ->assertSee('190.000₫')
            ->assertSee('220.000₫')
            ->assertDontSee('160.000₫')
            ->assertSee('350.000₫')
            ->assertSee('30.000₫')
            ->assertSee('Đến khi có thông báo mới')
            ->assertSee('Sao chép mã');
    }

    public function test_contact_form_saves_a_message_for_store_follow_up(): void
    {
        $response = $this->post(route('contact.store'), [
            'name' => 'Nguyễn An',
            'email' => 'an@example.test',
            'phone' => '0900000000',
            'subject' => 'Tư vấn sản phẩm',
            'message' => 'Tôi cần tư vấn một số linh kiện cho dự án.',
        ]);

        $response->assertRedirect(route('pages.contact'));
        $this->assertDatabaseHas('contact_messages', [
            'email' => 'an@example.test',
            'subject' => 'Tư vấn sản phẩm',
            'status' => 'new',
        ]);
    }

    public function test_admin_can_open_the_contact_message_inbox(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ContactMessage::query()->create([
            'name' => 'Nguyễn An',
            'email' => 'an@example.test',
            'subject' => 'Báo giá linh kiện',
            'message' => 'Tôi cần báo giá cho đơn hàng dự án.',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.contact-messages.index'));

        $response->assertOk()->assertSee('Báo giá linh kiện')->assertSee('an@example.test');
    }
}
