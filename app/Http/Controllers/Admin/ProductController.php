<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\PriceTier;
use App\Models\Product;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('category')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $query->where(fn ($builder) => $builder->where('name', 'like', $term)->orWhere('sku', 'like', $term)->orWhere('mpn', 'like', $term));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', ['products' => $products]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product,
            'categories' => $this->categories(),
            'retailTiers' => collect(),
            'wholesalePrice' => null,
            'educationPrice' => null,
        ]);
    }

    public function store(Request $request, AuditLogService $auditLogs): RedirectResponse
    {
        $data = $this->validatedData($request);
        $tiers = $data['tiers'] ?? [];
        $groupPrices = [
            'wholesale' => $data['wholesale_price'] ?? null,
            'education' => $data['education_price'] ?? null,
        ];
        unset($data['tiers']);
        unset($data['wholesale_price'], $data['education_price']);
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(4));
        $data['specifications'] = $this->parseSpecifications((string) ($data['specifications_text'] ?? ''));
        unset($data['specifications_text']);

        $product = Product::query()->create($data);
        $this->savePriceTiers($product, $tiers);
        $this->saveGroupPrices($product, $groupPrices);
        if ($product->stock > 0) {
            InventoryMovement::query()->create([
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'type' => 'opening',
                'quantity' => $product->stock,
                'reference' => 'OPEN-'.$product->sku,
                'note' => 'Tồn kho ban đầu khi tạo sản phẩm.',
            ]);
        }
        $auditLogs->record('product.created', $product, null, ['sku' => $product->sku, 'price' => $product->price, 'stock' => $product->stock], $request->user());

        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được tạo.');
    }

    public function edit(Product $product): View
    {
        $product->load('priceTiers');
        $priceTiers = $product->priceTiers;

        return view('admin.products.form', [
            'product' => $product,
            'categories' => $this->categories(),
            'retailTiers' => $priceTiers->where('price_group', 'retail')->values(),
            'wholesalePrice' => $priceTiers->firstWhere('price_group', 'wholesale')?->unit_price,
            'educationPrice' => $priceTiers->firstWhere('price_group', 'education')?->unit_price,
        ]);
    }

    public function update(Request $request, Product $product, AuditLogService $auditLogs): RedirectResponse
    {
        $oldValues = $product->only(['sku', 'price', 'stock', 'is_active']);
        $data = $this->validatedData($request, $product);
        $tiers = $data['tiers'] ?? [];
        $groupPrices = [
            'wholesale' => $data['wholesale_price'] ?? null,
            'education' => $data['education_price'] ?? null,
        ];
        unset($data['tiers']);
        unset($data['wholesale_price'], $data['education_price']);
        $data['slug'] = $product->name === $data['name']
            ? $product->slug
            : Str::slug($data['name']).'-'.Str::lower(Str::random(4));
        $data['specifications'] = $this->parseSpecifications((string) ($data['specifications_text'] ?? ''));
        unset($data['specifications_text']);

        $product->update($data);
        $stockDifference = (int) $product->stock - (int) $oldValues['stock'];
        if ($stockDifference !== 0) {
            InventoryMovement::query()->create([
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'type' => 'adjustment',
                'quantity' => $stockDifference,
                'reference' => 'ADJ-'.now()->format('ymd-His'),
                'note' => 'Điều chỉnh tồn kho từ màn hình sản phẩm.',
            ]);
        }
        $product->priceTiers()->delete();
        $this->savePriceTiers($product, $tiers);
        $this->saveGroupPrices($product, $groupPrices);
        $auditLogs->record('product.updated', $product, $oldValues, $product->only(['sku', 'price', 'stock', 'is_active']), $request->user());

        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được cập nhật.');
    }

    public function destroy(Product $product, Request $request, AuditLogService $auditLogs): RedirectResponse
    {
        $product->update(['is_active' => false]);
        $auditLogs->record('product.hidden', $product, ['is_active' => true], ['is_active' => false], $request->user());

        return back()->with('success', 'Sản phẩm đã được ẩn khỏi cửa hàng.');
    }

    /** @return array<string, mixed> */
    private function validatedData(Request $request, ?Product $product = null): array
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:80', Rule::unique('products', 'sku')->ignore($product)],
            'mpn' => ['nullable', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:10000'],
            'specifications_text' => ['nullable', 'string', 'max:5000'],
            'image_url' => ['nullable', 'string', 'url:http,https', 'max:2048'],
            'datasheet_url' => ['nullable', 'string', 'url:http,https', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'moq' => ['required', 'integer', 'min:1'],
            'pack_size' => ['required', 'integer', 'min:1'],
            'is_rohs' => ['nullable', 'boolean'],
            'is_esd_sensitive' => ['nullable', 'boolean'],
            'allow_backorder' => ['nullable', 'boolean'],
            'backorder_eta' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
            'tiers' => ['nullable', 'array', 'max:5'],
            'tiers.*.minimum_quantity' => ['nullable', 'integer', 'min:2', 'distinct:strict'],
            'tiers.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'education_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validated['moq'] % $validated['pack_size'] !== 0) {
            throw ValidationException::withMessages([
                'moq' => 'MOQ phải là bội số của quy cách đóng gói.',
            ]);
        }

        if ($product && (int) $validated['stock'] < $product->reserved_stock) {
            throw ValidationException::withMessages([
                'stock' => 'Tồn kho không thể thấp hơn số lượng đang giữ cho đơn hàng.',
            ]);
        }

        return $validated + [
            'reserved_stock' => $product?->reserved_stock ?? 0,
            'is_rohs' => $request->boolean('is_rohs'),
            'is_esd_sensitive' => $request->boolean('is_esd_sensitive'),
            'allow_backorder' => $request->boolean('allow_backorder'),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /** @param array<int, array{minimum_quantity?: int|string|null, unit_price?: int|float|string|null}> $tiers */
    private function savePriceTiers(Product $product, array $tiers): void
    {
        $validTiers = collect($tiers)
            ->filter(fn (array $tier): bool => ! empty($tier['minimum_quantity']) && isset($tier['unit_price']) && $tier['unit_price'] !== '')
            ->sortBy(fn (array $tier): int => (int) $tier['minimum_quantity'])
            ->values();

        foreach ($validTiers as $index => $tier) {
            $minimum = (int) $tier['minimum_quantity'];
            $nextMinimum = isset($validTiers[$index + 1]) ? (int) $validTiers[$index + 1]['minimum_quantity'] : null;

            PriceTier::query()->create([
                'product_id' => $product->id,
                'price_group' => 'retail',
                'minimum_quantity' => $minimum,
                'maximum_quantity' => $nextMinimum !== null ? $nextMinimum - 1 : null,
                'unit_price' => (float) $tier['unit_price'],
            ]);
        }
    }

    /** @param array<string, int|float|string|null> $prices */
    private function saveGroupPrices(Product $product, array $prices): void
    {
        foreach ($prices as $priceGroup => $price) {
            if ($price === null || $price === '') {
                continue;
            }

            PriceTier::query()->create([
                'product_id' => $product->id,
                'price_group' => $priceGroup,
                'minimum_quantity' => 1,
                'maximum_quantity' => null,
                'unit_price' => (float) $price,
            ]);
        }
    }

    /** @return array<string, string> */
    private function parseSpecifications(string $text): array
    {
        $specifications = [];

        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            [$key, $value] = array_pad(explode(':', $line, 2), 2, '');
            $key = trim($key);
            $value = trim($value);

            if ($key !== '' && $value !== '') {
                $specifications[$key] = $value;
            }
        }

        return $specifications;
    }

    /** @return Collection<int, Category> */
    private function categories(): Collection
    {
        return Category::query()->where('is_active', true)->orderBy('sort_order')->get();
    }
}
