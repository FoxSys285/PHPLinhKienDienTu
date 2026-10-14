<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id',
    'name',
    'slug',
    'sku',
    'description',
    'features',
    'specifications',
    'gallery_images',
    'image',
    'price',
    'old_price',
    'stock',
    'sold_count',
    'badge',
    'is_featured',
    'is_active',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * Get the category that owns the product.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all reviews for the product.
     *
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get approved reviews for the product.
     *
     * @return HasMany<Review, $this>
     */
    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('is_approved', true);
    }

    /**
     * Format the product price for Vietnamese storefront pages.
     *
     * @return Attribute<string, never>
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::get(fn (): string => number_format((float) $this->price, 0, ',', '.').'₫');
    }

    /**
     * Format the old product price when a discount is set.
     *
     * @return Attribute<string, never>
     */
    protected function formattedOldPrice(): Attribute
    {
        return Attribute::get(fn (): string => $this->old_price > 0
            ? number_format((float) $this->old_price, 0, ',', '.').'₫'
            : '');
    }

    /**
     * Read the average review rating loaded by the storefront query.
     *
     * @return Attribute<float, never>
     */
    protected function rating(): Attribute
    {
        return Attribute::get(fn (): float => round((float) ($this->attributes['approved_reviews_avg_rating'] ?? 0), 1));
    }

    /**
     * Describe current stock for product detail pages.
     *
     * @return Attribute<string, never>
     */
    protected function stockLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->stock > 0 ? 'Còn hàng' : 'Tạm hết hàng');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'features' => 'array',
            'specifications' => 'array',
            'gallery_images' => 'array',
            'price' => 'decimal:2',
            'old_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
