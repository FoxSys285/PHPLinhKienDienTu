<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['category_id', 'sku', 'mpn', 'name', 'slug', 'brand', 'description', 'specifications', 'image_url', 'datasheet_url', 'price', 'compare_at_price', 'stock', 'reserved_stock', 'minimum_stock', 'moq', 'pack_size', 'is_rohs', 'is_esd_sensitive', 'allow_backorder', 'backorder_eta', 'is_active'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'specifications' => 'array',
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'is_rohs' => 'boolean',
            'is_esd_sensitive' => 'boolean',
            'allow_backorder' => 'boolean',
            'is_active' => 'boolean',
            'backorder_eta' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(PriceTier::class)->orderBy('minimum_quantity');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function availableStock(): int
    {
        return max(0, $this->stock - $this->reserved_stock);
    }

    public function priceFor(int $quantity, string $priceGroup = 'retail'): float
    {
        $tier = $this->priceTiers
            ->where('price_group', $priceGroup)
            ->where('minimum_quantity', '<=', $quantity)
            ->filter(fn (PriceTier $priceTier): bool => $priceTier->maximum_quantity === null || $priceTier->maximum_quantity >= $quantity)
            ->sortByDesc('minimum_quantity')
            ->first();

        if (! $tier && $priceGroup !== 'retail') {
            $tier = $this->priceTiers
                ->where('price_group', 'retail')
                ->where('minimum_quantity', '<=', $quantity)
                ->filter(fn (PriceTier $priceTier): bool => $priceTier->maximum_quantity === null || $priceTier->maximum_quantity >= $quantity)
                ->sortByDesc('minimum_quantity')
                ->first();
        }

        return (float) ($tier?->unit_price ?? $this->price);
    }
}
