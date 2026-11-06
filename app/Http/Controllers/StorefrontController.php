<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\StockAlert;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function home(): View
    {
        return view('storefront.home', [
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'featuredProducts' => Product::query()->where('is_active', true)->with(['category', 'priceTiers'])->latest()->take(8)->get(),
            'heroProducts' => $this->heroProducts(),
        ]);
    }

    public function index(Request $request): View
    {
        return view('storefront.index', $this->listingData($request));
    }

    public function category(Request $request, Category $category): View
    {
        abort_unless($category->is_active, 404);

        return view('storefront.index', $this->listingData($request, $category));
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'priceTiers']);
        $priceGroup = auth()->user()?->account_type === 'business' && auth()->user()->business_approved
            ? auth()->user()->price_group
            : 'retail';
        $visibleTiers = $product->priceTiers->where('price_group', $priceGroup)->values();

        if ($visibleTiers->isEmpty() && $priceGroup !== 'retail') {
            $visibleTiers = $product->priceTiers->where('price_group', 'retail')->values();
        }

        $product->setRelation('priceTiers', $visibleTiers);

        return view('storefront.show', [
            'product' => $product,
            'displayPrice' => $product->priceFor((int) $product->moq, $priceGroup),
            'relatedProducts' => Product::query()
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('is_active', true)
                ->with(['category', 'priceTiers'])
                ->take(4)
                ->get(),
            'reviews' => $product->reviews()->where('is_approved', true)->with('user')->latest()->take(8)->get(),
        ]);
    }

    public function suggest(Request $request): JsonResponse
    {
        $term = trim($request->string('q')->toString());

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $products = Product::query()
            ->where('is_active', true)
            ->where(function (Builder $query) use ($term): void {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('mpn', 'like', "%{$term}%");
            })
            ->orderByRaw('CASE WHEN sku = ? THEN 0 WHEN mpn = ? THEN 1 ELSE 2 END', [$term, $term])
            ->take(8)
            ->get(['id', 'name', 'slug', 'sku', 'mpn', 'price', 'stock']);

        return response()->json($products->map(fn (Product $product): array => [
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => number_format((float) $product->price, 0, ',', '.').'₫',
            'url' => route('products.show', $product),
            'in_stock' => $product->stock - $product->reserved_stock > 0,
        ]));
    }

    public function sitemap(): Response
    {
        return response()
            ->view('sitemap', [
                'products' => Product::query()->where('is_active', true)->get(['slug', 'updated_at']),
                'categories' => Category::query()->where('is_active', true)->get(['slug', 'updated_at']),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function review(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $hasPurchased = $request->user()->orders()
            ->where('status', 'completed')
            ->whereHas('items', fn (Builder $query): Builder => $query->where('product_id', $product->id))
            ->exists();

        if (! $hasPurchased) {
            return back()->withErrors(['review' => 'Bạn cần hoàn tất đơn mua sản phẩm trước khi đánh giá.']);
        }

        Review::query()->create($data + [
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'is_approved' => false,
        ]);

        return back()->with('success', 'Đánh giá đã được gửi và đang chờ duyệt.');
    }

    public function restock(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);

        if ($product->availableStock() > 0) {
            return back()->with('success', 'Sản phẩm đang có hàng.');
        }

        StockAlert::query()->updateOrCreate(
            ['product_id' => $product->id, 'email' => mb_strtolower($data['email'])],
            ['notified_at' => null],
        );

        return back()->with('success', 'Đã đăng ký nhận email khi sản phẩm có hàng trở lại.');
    }

    /** @return array<string, mixed> */
    private function listingData(Request $request, ?Category $category = null): array
    {
        $query = Product::query()->where('is_active', true)->with(['category', 'priceTiers']);

        if ($category) {
            $categoryIds = $category->children()->pluck('id')->prepend($category->id);
            $query->whereIn('category_id', $categoryIds);
        }

        $term = trim($request->string('q')->toString());

        if ($term !== '') {
            $query->where(function (Builder $builder) use ($term): void {
                $builder->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('mpn', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%");
            });
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->string('brand')->toString());
        }

        if ($request->boolean('in_stock')) {
            $query->whereColumn('stock', '>', 'reserved_stock');
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->integer('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->integer('max_price'));
        }

        foreach ((array) $request->input('spec', []) as $key => $value) {
            if (is_string($key) && preg_match('/^[\pL\pN_-]{1,50}$/u', $key) === 1 && is_scalar($value) && $value !== '') {
                $query->where("specifications->$key", (string) $value);
            }
        }

        match ($request->string('sort')->toString()) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        $products = $query->paginate(16)->withQueryString();
        $filters = Product::query()->where('is_active', true)
            ->when($category, fn (Builder $builder) => $builder->where('category_id', $category->id))
            ->whereNotNull('brand')->distinct()->orderBy('brand')->pluck('brand');
        $specFilters = Product::query()
            ->where('is_active', true)
            ->when($category, fn (Builder $builder) => $builder->where('category_id', $category->id))
            ->orderBy('id')
            ->take(500)
            ->get(['specifications'])
            ->flatMap(fn (Product $product) => collect((array) $product->specifications)
                ->map(fn ($value, $name): array => ['name' => (string) $name, 'value' => (string) $value]))
            ->groupBy('name')
            ->map(fn ($values): array => $values->pluck('value')->filter()->unique()->take(12)->values()->all())
            ->take(6)
            ->all();

        return [
            'products' => $products,
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'brands' => $filters,
            'specFilters' => $specFilters,
            'category' => $category,
            'term' => $term,
            'heroProducts' => $this->heroProducts(),
        ];
    }

    /** @return EloquentCollection<int, Product> */
    private function heroProducts(): EloquentCollection
    {
        $skus = ['BOARD-ARDUINO-UNO-R3', 'BOARD-ESP32-DEVKIT', 'MOD-DHT22'];

        return Product::query()
            ->where('is_active', true)
            ->whereIn('sku', $skus)
            ->orderByRaw('CASE sku WHEN ? THEN 0 WHEN ? THEN 1 WHEN ? THEN 2 ELSE 3 END', $skus)
            ->get(['slug', 'sku', 'name', 'image_url']);
    }
}
