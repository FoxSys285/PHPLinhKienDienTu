<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:pending,approved'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
        ]);

        $reviews = Review::query()
            ->with(['product:id,name,slug', 'user:id,name'])
            ->when($filters['q'] ?? null, function ($query, string $term): void {
                $query->where(function ($query) use ($term): void {
                    $query->where('customer_name', 'like', '%'.$term.'%')
                        ->orWhere('content', 'like', '%'.$term.'%')
                        ->orWhereHas('product', fn ($productQuery) => $productQuery->where('name', 'like', '%'.$term.'%'));
                });
            })
            ->when(isset($filters['status']), fn ($query) => $query->where('is_approved', $filters['status'] === 'approved'))
            ->when($filters['rating'] ?? null, fn ($query, int $rating) => $query->where('rating', $rating))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('layouts.pages.admin.quanlydanhgia', [
            'reviews' => $reviews,
            'filters' => $filters,
        ]);
    }

    public function approve(Review $review): RedirectResponse
    {
        $review->update(['is_approved' => true]);

        return back()->with('status', 'Đã duyệt đánh giá.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('status', 'Đã xóa đánh giá.');
    }
}
