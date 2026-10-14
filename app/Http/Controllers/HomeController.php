<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredProducts = Product::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->with('category')
            ->withAvg('approvedReviews as approved_reviews_avg_rating', 'rating')
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products as product_count' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get();

        return view('layouts.pages.user.trangchu', [
            'featuredProducts' => $featuredProducts,
            'categories' => $categories,
        ]);
    }
}
