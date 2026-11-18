<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportProductImagesToCloudinaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.cloudinary' => [
            'cloud_name' => 'test-cloud',
            'api_key' => 'test-key',
            'api_secret' => 'test-secret',
            'folder' => 'linhkien/products',
        ]]);
        Http::preventStrayRequests();
    }

    public function test_shared_images_are_uploaded_once_and_original_urls_are_backed_up(): void
    {
        $products = Product::factory()->count(2)->create(['image_url' => 'https://example.com/original.jpg']);
        Storage::fake('local');
        Http::fake(['https://api.cloudinary.com/v1_1/test-cloud/image/upload' => Http::response([
            'secure_url' => 'https://res.cloudinary.com/test-cloud/image/upload/imported.jpg',
        ])]);

        $this->artisan('products:cloudinary-images')->assertSuccessful();

        foreach ($products as $product) {
            $this->assertDatabaseHas('products', ['id' => $product->id, 'image_url' => 'https://res.cloudinary.com/test-cloud/image/upload/imported.jpg']);
        }
        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request): bool => $request['file'] === 'https://example.com/original.jpg'
            && $request['overwrite'] === 'false'
            && str_starts_with($request['public_id'], 'import-')
            && ! isset($request['api_secret']));
        $backups = Storage::disk('local')->files('cloudinary-imports');
        $this->assertCount(1, $backups);
        $originals = json_decode(Storage::disk('local')->get($backups[0]), true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(2, $originals);
        $this->assertSame('https://example.com/original.jpg', $originals[0]['image_url']);
    }

    public function test_images_already_in_our_cloud_are_skipped(): void
    {
        $product = Product::factory()->create(['image_url' => 'https://res.cloudinary.com/test-cloud/image/upload/existing.jpg']);
        Storage::fake('local');
        Http::fake(['https://api.cloudinary.com/v1_1/test-cloud/image/upload' => Http::response()]);

        $this->artisan('products:cloudinary-images')->assertSuccessful();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'image_url' => $product->image_url]);
        Http::assertNothingSent();
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_dry_run_does_not_upload_images_or_change_products(): void
    {
        $product = Product::factory()->create(['image_url' => 'https://example.com/original.jpg']);
        Storage::fake('local');
        Http::fake(['https://api.cloudinary.com/v1_1/test-cloud/image/upload' => Http::response()]);

        $this->artisan('products:cloudinary-images', ['--dry-run' => true])->assertSuccessful();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'image_url' => $product->image_url]);
        Http::assertNothingSent();
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_failed_images_keep_their_original_url_and_other_images_continue(): void
    {
        $failed = Product::factory()->create(['image_url' => 'https://example.com/broken.jpg']);
        $successful = Product::factory()->create(['image_url' => 'https://res.cloudinary.com/another-cloud/image/upload/source.jpg']);
        Storage::fake('local');
        Http::fake(['https://api.cloudinary.com/v1_1/test-cloud/image/upload' => Http::sequence()
            ->push(['error' => ['message' => 'Could not fetch']], 400)
            ->push(['secure_url' => 'https://res.cloudinary.com/test-cloud/image/upload/imported.jpg'])]);

        $this->artisan('products:cloudinary-images')->assertFailed();

        $this->assertDatabaseHas('products', ['id' => $failed->id, 'image_url' => $failed->image_url]);
        $this->assertDatabaseHas('products', ['id' => $successful->id, 'image_url' => 'https://res.cloudinary.com/test-cloud/image/upload/imported.jpg']);
        Http::assertSentCount(2);
    }

    public function test_concurrent_image_changes_are_preserved(): void
    {
        $product = Product::factory()->create(['image_url' => 'https://example.com/original.jpg']);
        Storage::fake('local');
        Http::fake(['https://api.cloudinary.com/v1_1/test-cloud/image/upload' => function () use ($product) {
            $product->update(['image_url' => 'https://example.com/newer.jpg']);

            return Http::response(['secure_url' => 'https://res.cloudinary.com/test-cloud/image/upload/imported.jpg']);
        }]);

        $this->artisan('products:cloudinary-images')->assertSuccessful();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'image_url' => 'https://example.com/newer.jpg']);
        Http::assertSentCount(1);
    }
}
