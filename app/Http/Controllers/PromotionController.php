<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        return view('pages.promotions', [
            'promotions' => Coupon::query()->promoted()->available()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $promotion = Coupon::query()->promoted()->available()->where('slug', $slug)->firstOrFail();
        $products = Product::query()
            ->where('is_active', true)
            ->when(! empty($promotion->category_slugs), fn (Builder $query): Builder => $query->whereHas('category', fn (Builder $query): Builder => $query->whereIn('slug', $promotion->category_slugs)))
            ->with(['category', 'priceTiers'])
            ->orderBy('name')
            ->take(12)
            ->get();

        return view('pages.promotion-detail', [
            'promotion' => $promotion,
            'slug' => $slug,
            'products' => $products,
            'promotionCategories' => Category::query()->whereIn('slug', $promotion->category_slugs ?? [])->get(),
        ]);
    }
}
