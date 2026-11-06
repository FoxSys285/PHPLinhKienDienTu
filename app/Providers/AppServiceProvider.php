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
        View::composer('layouts.store', function (ViewInstance $view): void {
            $request = request();
            $cartQuery = Cart::query();

            if ($request->user()) {
                $cartQuery->where('user_id', $request->user()->id);
            } elseif (is_string($token = $request->cookie('cart_token'))) {
                $cartQuery->where('token', $token);
            } else {
                $cartQuery->whereRaw('1 = 0');
            }

            $view->with('navCartCount', (int) $cartQuery->withSum('items', 'quantity')->value('items_sum_quantity'))
                ->with('navCategories', Category::query()->where('is_active', true)->orderBy('sort_order')->take(7)->get());
        });
    }
}
