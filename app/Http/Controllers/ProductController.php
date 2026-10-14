<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(): View
    {
        $query = trim((string) request('q', ''));
        $requestedCategory = trim((string) request('category', ''));
        $category = $requestedCategory === ''
            ? null
            : Category::query()->where('is_active', true)->where('slug', $requestedCategory)->first();
        $sort = (string) request('sort', 'featured');

        $productsQuery = Product::query()
            ->where('is_active', true)
            ->with('category')
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews as approved_reviews_avg_rating', 'rating');

        if ($query !== '') {
            $productsQuery->where(function ($productsQuery) use ($query): void {
                $productsQuery
                    ->where('name', 'like', '%'.$query.'%')
                    ->orWhere('sku', 'like', '%'.$query.'%')
                    ->orWhere('description', 'like', '%'.$query.'%');
            });
        }

        if ($category !== null) {
            $productsQuery->where('category_id', $category->id);
        }

        $totalProducts = (clone $productsQuery)->count();

        match ($sort) {
            'price-asc' => $productsQuery->orderBy('price'),
            'price-desc' => $productsQuery->orderByDesc('price'),
            'name' => $productsQuery->orderBy('name'),
            'newest' => $productsQuery->orderByDesc('created_at'),
            default => $productsQuery->orderByDesc('is_featured')->orderByDesc('created_at'),
        };

        $products = $productsQuery->get();
        $productCategories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $productCategories->prepend('Tất cả');

        return view('layouts.pages.user.danhsachsanpham', [
            'products' => $products,
            'totalProducts' => $totalProducts,
            'productCategories' => $productCategories,
            'query' => $query,
            'selectedCategory' => $category?->name ?? 'Tất cả',
            'selectedCategorySlug' => $category?->slug ?? '',
            'sort' => $sort,
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load('category');
        $product->loadCount('approvedReviews');
        $product->loadAvg('approvedReviews as approved_reviews_avg_rating', 'rating');

        $reviewCount = (int) $product->approved_reviews_count;
        $ratingCounts = $product->approvedReviews()
            ->selectRaw('rating, COUNT(*) as rating_count')
            ->groupBy('rating')
            ->pluck('rating_count', 'rating');
        $reviews = $product->approvedReviews()
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($review): array {
                $name = $review->customer_name;

                return [
                    'rating' => $review->rating,
                    'customer_name' => $name,
                    'initials' => Str::upper(Str::substr($name, 0, 2)),
                    'created_at' => $review->created_at?->format('d/m/Y'),
                    'content' => $review->content,
                ];
            });
        $ratingDistribution = collect(range(5, 1))
            ->map(function (int $stars) use ($ratingCounts, $reviewCount): array {
                $count = (int) $ratingCounts->get($stars, 0);

                return [
                    'stars' => $stars,
                    'count' => $count,
                    'percent' => $reviewCount > 0 ? (int) round($count / $reviewCount * 100) : 0,
                ];
            });
        $relatedProducts = Product::query()
            ->where('is_active', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with('category')
            ->withAvg('approvedReviews as approved_reviews_avg_rating', 'rating')
            ->limit(4)
            ->get();

        return view('layouts.pages.user.chitietsanpham', [
            'product' => $product,
            'features' => $product->features ?? [],
            'specifications' => $product->specifications ?? [],
            'galleryImages' => $product->gallery_images ?? [],
            'reviews' => $reviews,
            'reviewCount' => $reviewCount,
            'ratingDistribution' => $ratingDistribution,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
