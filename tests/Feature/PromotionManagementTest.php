<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Database\Seeders\PromotionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PromotionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_manage_promotions(): void
    {
        $this->get(route('admin.promotions.index'))->assertRedirect(route('login'));
        $this->post(route('admin.promotions.store'), $this->payload())->assertRedirect(route('login'));
        $this->assertDatabaseCount('coupons', 0);
    }

    public function test_sales_staff_cannot_change_or_view_promotions(): void
    {
        $staff = User::factory()->create(['role' => 'sales']);
        $promotion = Coupon::factory()->create();

        $this->actingAs($staff)->get(route('admin.promotions.index'))->assertForbidden();
        $this->post(route('admin.promotions.store'), $this->payload())->assertForbidden();
        $this->put(route('admin.promotions.update', $promotion), $this->payload())->assertForbidden();
        $this->delete(route('admin.promotions.destroy', $promotion))->assertForbidden();
        $this->assertTrue($promotion->fresh()->is_active);
    }

    public function test_admin_can_create_a_promotion_with_an_uploaded_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.promotions.store'), [
            ...$this->payload(),
            'image' => UploadedFile::fake()->image('offer.jpg', 768, 512),
            'used_count' => 999,
        ]);

        $response->assertRedirect(route('admin.promotions.index'))->assertSessionHasNoErrors();
        $promotion = Coupon::query()->where('code', 'NEW10')->firstOrFail();
        $this->assertSame('new-offer', $promotion->slug);
        $this->assertSame(0, $promotion->used_count);
        Storage::disk('public')->assertExists($promotion->image_path);
        $this->assertDatabaseHas('activity_logs', ['action' => 'promotion.created', 'subject_id' => $promotion->id]);
        $this->get(route('promotions.show', $promotion->slug))->assertOk()->assertSee('NEW10');
    }

    public function test_admin_can_edit_conditions_and_pause_without_deleting_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $promotion = Coupon::factory()->create(['used_count' => 3]);

        $this->actingAs($admin)->put(route('admin.promotions.update', $promotion), [
            ...$this->payload(), 'discount_value' => 15, 'used_count' => 0,
        ])->assertRedirect(route('admin.promotions.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('coupons', ['id' => $promotion->id, 'discount_value' => 15, 'used_count' => 3]);

        $this->delete(route('admin.promotions.destroy', $promotion))->assertRedirect(route('admin.promotions.index'));
        $this->assertFalse($promotion->fresh()->is_active);
        $this->get(route('promotions.show', 'new-offer'))->assertNotFound();
    }

    public function test_admin_pages_render_and_filter_programs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $active = Coupon::factory()->create(['title' => 'Visible active program']);
        Coupon::factory()->create(['title' => 'Paused hidden program', 'is_active' => false]);

        $this->actingAs($admin)->get(route('admin.promotions.index', ['status' => 'active', 'q' => 'Visible']))
            ->assertOk()->assertSee('Visible active program')->assertDontSee('Paused hidden program');
        $this->get(route('admin.promotions.create'))->assertOk()->assertSee('discount_value', false);
        $this->get(route('admin.promotions.edit', $active))->assertOk()->assertSee($active->code);
    }

    public function test_invalid_discount_dates_categories_and_image_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.promotions.store'), [
            ...$this->payload(),
            'discount_value' => 101,
            'starts_at' => '2026-11-20 12:00',
            'expires_at' => '2026-11-19 12:00',
            'category_slugs' => ['does-not-exist'],
            'image' => UploadedFile::fake()->create('script.svg', 1, 'image/svg+xml'),
        ])->assertSessionHasErrors(['discount_value', 'expires_at', 'category_slugs.0', 'image']);

        $this->assertDatabaseCount('coupons', 0);
    }

    public function test_malformed_text_fields_are_validation_errors(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->postJson(route('admin.promotions.store'), [
            ...$this->payload(), 'title' => ['invalid'], 'code' => ['invalid'], 'slug' => '',
        ])->assertUnprocessable()->assertJsonValidationErrors(['title', 'code', 'slug']);

        $this->assertDatabaseCount('coupons', 0);
    }

    public function test_duplicate_codes_and_slugs_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Coupon::factory()->create(['code' => 'NEW10', 'slug' => 'new-offer']);

        $this->actingAs($admin)->post(route('admin.promotions.store'), $this->payload())
            ->assertSessionHasErrors(['code', 'slug']);
        $this->assertDatabaseCount('coupons', 1);
    }

    public function test_public_list_only_shows_available_programs_and_escapes_content(): void
    {
        $this->freezeTime();
        Coupon::factory()->create(['title' => '<script>alert(1)</script>', 'code' => 'SAFE10']);
        Coupon::factory()->create(['code' => 'PAUSED', 'is_active' => false]);
        Coupon::factory()->create(['code' => 'EXPIRED', 'expires_at' => now()->subMinute()]);
        Coupon::factory()->create(['code' => 'SCHEDULED', 'starts_at' => now()->addDay()]);
        Coupon::factory()->create(['code' => 'EXHAUSTED', 'usage_limit' => 1, 'used_count' => 1]);

        $this->get(route('pages.promotions'))->assertOk()
            ->assertSee('SAFE10')->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('PAUSED')->assertDontSee('EXPIRED')->assertDontSee('SCHEDULED')->assertDontSee('EXHAUSTED');
    }

    public function test_seeder_creates_fifteen_programs_and_keeps_admin_changes_on_rerun(): void
    {
        $this->seed(PromotionSeeder::class);
        $promotion = Coupon::query()->where('code', 'MAKER10')->firstOrFail();
        $promotion->update(['title' => 'Edited by admin', 'is_active' => false, 'used_count' => 7]);

        $this->seed(PromotionSeeder::class);

        $this->assertDatabaseCount('coupons', 15);
        $this->assertDatabaseHas('coupons', ['id' => $promotion->id, 'title' => 'Edited by admin', 'is_active' => false, 'used_count' => 7]);
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'title' => 'New offer', 'slug' => 'new-offer', 'code' => 'new10',
            'eyebrow' => 'Maker', 'description' => 'A promotion for electronic components.',
            'type' => 'percentage', 'discount_value' => 10, 'minimum_purchase' => 100000,
            'theme' => 'blue', 'sort_order' => 1, 'is_active' => 1, 'is_automatic' => 1,
            'image_preset' => 'maker',
        ];
    }
}
