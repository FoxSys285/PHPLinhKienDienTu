<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $validated = $request->validate([
            'name' => [$request->user() ? 'nullable' : 'required', 'string', 'max:120'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'content' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $user = $request->user();
        $reviewData = [
            'customer_name' => $user?->name ?? $validated['name'],
            'rating' => $validated['rating'],
            'content' => $validated['content'],
            'is_approved' => false,
        ];

        if ($user) {
            $product->reviews()->updateOrCreate(
                ['user_id' => $user->id],
                $reviewData,
            );
        } else {
            $product->reviews()->create($reviewData);
        }

        return redirect()
            ->to(route('products.show', $product).'#reviews')
            ->with('review_status', 'Cảm ơn bạn đã gửi đánh giá. Đánh giá sẽ hiển thị sau khi được duyệt.');
    }
}
