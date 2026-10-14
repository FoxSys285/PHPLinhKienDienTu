<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.user', function (ViewInstance $view): void {
            $categories = Category::query()
                ->where('is_active', true)
                ->withCount(['products as product_count' => fn ($query) => $query->where('is_active', true)])
                ->orderBy('name')
                ->get();

            $cartCount = Cart::query()
                ->where('session_id', request()->session()->getId())
                ->withSum('items', 'quantity')
                ->value('items_sum_quantity') ?? 0;

            $view->with([
                'categories' => $categories,
                'cartCount' => (int) $cartCount,
            ]);
        });
    }
}
