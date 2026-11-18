<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class ProductImageUploadTest extends TestCase
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

    public function test_admin_can_choose_an_image_without_exposing_credentials(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.products.create'));

        $response->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="image"', false)
            ->assertSee('Cloudinary')
            ->assertDontSee('test-secret')
            ->assertDontSee('test-key');
    }

    public function test_admin_can_create_a_product_with_a_signed_cloudinary_upload(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payload = $this->productData();
        $image = UploadedFile::fake()->image('product.jpg');
        $this->travelTo(now()->setTimestamp(1700000000));
        Http::fake(['https://api.cloudinary.com/v1_1/test-cloud/image/upload' => Http::response([
            'secure_url' => 'https://res.cloudinary.com/test-cloud/image/upload/product.jpg',
        ])]);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            ...$payload,
            'image' => $image,
            'image_url' => 'https://example.com/old.jpg',
        ]);

        $response->assertRedirectToRoute('admin.products.index')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', [
            'sku' => $payload['sku'],
            'image_url' => 'https://res.cloudinary.com/test-cloud/image/upload/product.jpg',
        ]);
        Http::assertSent(function (Request $request) use ($image): bool {
            $parameters = collect($request->data())->pluck('contents', 'name')->all();

            return $request->method() === 'POST'
                && $request->url() === 'https://api.cloudinary.com/v1_1/test-cloud/image/upload'
                && $request->hasFile('file', $image->getContent())
                && $parameters['api_key'] === 'test-key'
                && $parameters['folder'] === 'linhkien/products'
                && $parameters['timestamp'] === '1700000000'
                && $parameters['signature'] === '91b79558ca01e863c4357332b673225a5e8b490dd32bb754cff5f346fcacd28a'
                && ! isset($parameters['api_secret']);
        });
    }

    public function test_admin_can_replace_a_product_image(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['image_url' => 'https://example.com/old.jpg']);
        Http::fake(['https://api.cloudinary.com/v1_1/test-cloud/image/upload' => Http::response([
            'secure_url' => 'https://res.cloudinary.com/test-cloud/image/upload/replacement.png',
        ])]);

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            ...$this->productData($product),
            'image' => UploadedFile::fake()->image('replacement.png'),
        ]);

        $response->assertRedirectToRoute('admin.products.index')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'image_url' => 'https://res.cloudinary.com/test-cloud/image/upload/replacement.png',
        ]);
        Http::assertSentCount(1);
    }

    public function test_saving_without_a_new_image_keeps_the_current_image(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['image_url' => 'https://example.com/current.jpg']);
        Http::fake(['https://api.cloudinary.com/v1_1/test-cloud/image/upload' => Http::response()]);

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product), $this->productData($product));

        $response->assertRedirectToRoute('admin.products.index')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'image_url' => 'https://example.com/current.jpg']);
        Http::assertNothingSent();
    }

    public function test_an_existing_cloudinary_url_can_be_used_without_an_upload(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payload = $this->productData();
        Http::fake(['https://api.cloudinary.com/v1_1/test-cloud/image/upload' => Http::response()]);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            ...$payload,
            'image_url' => 'https://res.cloudinary.com/test-cloud/image/upload/existing.jpg',
        ]);

        $response->assertRedirectToRoute('admin.products.index')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', [
            'sku' => $payload['sku'],
            'image_url' => 'https://res.cloudinary.com/test-cloud/image/upload/existing.jpg',
        ]);
        Http::assertNothingSent();
    }

    public function test_non_image_content_is_rejected_before_uploading(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payload = $this->productData();
        $file = UploadedFile::fake()->createWithContent('fake.jpg', '<?php echo "unsafe";');
        Http::fake(['https://api.cloudinary.com/v1_1/test-cloud/image/upload' => Http::response()]);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            ...$payload,
            'image' => new UploadedFile($file->getPathname(), 'fake.jpg', null, null, true),
        ]);

        $response->assertSessionHasErrors(['image' => 'Vui lòng chọn một tệp ảnh hợp lệ.']);
        $this->assertDatabaseMissing('products', ['sku' => $payload['sku']]);
        Http::assertNothingSent();
    }

    #[TestWith(['product.gif', 1, 'Ảnh sản phẩm phải có định dạng JPG, PNG hoặc WebP.'])]
    #[TestWith(['large.jpg', 5121, 'Ảnh sản phẩm không được lớn hơn 5 MB.'])]
    public function test_invalid_images_are_rejected(string $filename, int $size, string $message): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payload = $this->productData();
        Http::fake(['https://api.cloudinary.com/v1_1/test-cloud/image/upload' => Http::response()]);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            ...$payload,
            'image' => UploadedFile::fake()->image($filename)->size($size),
        ]);

        $response->assertSessionHasErrors(['image' => $message]);
        $this->assertDatabaseMissing('products', ['sku' => $payload['sku']]);
        Http::assertNothingSent();
    }

    #[TestWith([401, ['error' => ['message' => 'test-secret']]])]
    #[TestWith([500, []])]
    #[TestWith([200, []])]
    #[TestWith([200, ['secure_url' => 'http://example.com/insecure.jpg']])]
    public function test_cloudinary_errors_preserve_the_product_and_its_current_image(int $status, array $body): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['image_url' => 'https://example.com/current.jpg']);
        Http::fake(['https://api.cloudinary.com/v1_1/test-cloud/image/upload' => Http::response($body, $status)]);

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            ...$this->productData($product),
            'name' => 'This change must not be saved',
            'image' => UploadedFile::fake()->image('replacement.jpg'),
        ]);

        $response->assertSessionHasErrors(['image' => 'Không tải được ảnh lên Cloudinary. Vui lòng chọn lại ảnh và thử lại.']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => $product->name, 'image_url' => $product->image_url]);
        $this->assertDatabaseCount('activity_logs', 0);
        Http::assertSentCount(1);
    }

    public function test_connection_failure_does_not_create_a_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payload = $this->productData();
        Http::fake(['https://api.cloudinary.com/v1_1/test-cloud/image/upload' => Http::failedConnection()]);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            ...$payload,
            'image' => UploadedFile::fake()->image('product.jpg'),
        ]);

        $response->assertSessionHasErrors(['image' => 'Không thể kết nối Cloudinary. Vui lòng chọn lại ảnh và thử lại sau.']);
        $this->assertDatabaseMissing('products', ['sku' => $payload['sku']]);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_missing_configuration_prevents_uploading_and_creating_a_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payload = $this->productData();
        config(['services.cloudinary.api_secret' => '']);
        Http::fake(['https://api.cloudinary.com/v1_1/test-cloud/image/upload' => Http::response()]);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            ...$payload,
            'image' => UploadedFile::fake()->image('product.jpg'),
        ]);

        $response->assertSessionHasErrors(['image' => 'Cloudinary chưa được cấu hình đầy đủ. Vui lòng kiểm tra cấu hình máy chủ.']);
        $this->assertDatabaseMissing('products', ['sku' => $payload['sku']]);
        Http::assertNothingSent();
    }

    public function test_other_validation_errors_prevent_uploads(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payload = $this->productData();
        Http::fake(['https://api.cloudinary.com/v1_1/test-cloud/image/upload' => Http::response()]);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            ...$payload,
            'moq' => 3,
            'pack_size' => 2,
            'image' => UploadedFile::fake()->image('product.jpg'),
        ]);

        $response->assertSessionHasErrors(['moq' => 'MOQ phải là bội số của quy cách đóng gói.']);
        $this->assertDatabaseMissing('products', ['sku' => $payload['sku']]);
        Http::assertNothingSent();
    }

    public function test_guests_cannot_upload_product_images(): void
    {
        Http::fake(['https://api.cloudinary.com/v1_1/test-cloud/image/upload' => Http::response()]);

        $response = $this->post(route('admin.products.store'), ['image' => UploadedFile::fake()->image('product.jpg')]);

        $response->assertRedirectToRoute('login');
        $this->assertDatabaseCount('products', 0);
        Http::assertNothingSent();
    }

    public function test_customers_cannot_upload_product_images(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        Http::fake(['https://api.cloudinary.com/v1_1/test-cloud/image/upload' => Http::response()]);

        $response = $this->actingAs($customer)->post(route('admin.products.store'), ['image' => UploadedFile::fake()->image('product.jpg')]);

        $response->assertForbidden();
        $this->assertDatabaseCount('products', 0);
        Http::assertNothingSent();
    }

    /** @return array{category_id: int, sku: string, name: string, price: string|int, stock: int, minimum_stock: int, moq: int, pack_size: int, is_active: int} */
    private function productData(?Product $product = null): array
    {
        return [
            'category_id' => $product?->category_id ?? Category::factory()->create()->id,
            'sku' => $product?->sku ?? 'CLOUDINARY-TEST',
            'name' => $product?->name ?? 'Cloudinary Test Product',
            'price' => $product?->price ?? 25000,
            'stock' => $product?->stock ?? 0,
            'minimum_stock' => 5,
            'moq' => 1,
            'pack_size' => 1,
            'is_active' => 1,
        ];
    }
}
