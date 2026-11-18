<?php

namespace App\Models;

use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['code', 'type', 'discount_value', 'minimum_purchase', 'maximum_discount', 'usage_limit', 'used_count', 'starts_at', 'expires_at', 'is_active', 'slug', 'title', 'eyebrow', 'description', 'terms', 'image_path', 'theme', 'category_slugs', 'is_automatic', 'sort_order'])]
class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory;

    public const IMAGE_PRESETS = [
        'maker' => 'Ưu đãi Maker',
        'passives' => 'Điện trở & tụ điện',
        'sensors' => 'Module & cảm biến',
        'boards' => 'Board & vi điều khiển',
        'tools' => 'Dụng cụ & bàn thực hành',
        'chips' => 'Bán dẫn & IC',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'minimum_purchase' => 'decimal:2',
            'maximum_discount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
            'is_automatic' => 'boolean',
            'category_slugs' => 'array',
        ];
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn (Builder $query): Builder => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $query): Builder => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->where(fn (Builder $query): Builder => $query->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'));
    }

    public function scopePromoted(Builder $query): Builder
    {
        return $query->whereNotNull('slug')->whereNotNull('title');
    }

    public function isAvailable(): bool
    {
        return $this->is_active && ! $this->starts_at?->isFuture() && ! $this->expires_at?->isPast()
            && ($this->usage_limit === null || $this->used_count < $this->usage_limit);
    }

    public function discountFor(float $subtotal): float
    {
        if (! $this->isAvailable() || $subtotal < (float) $this->minimum_purchase) {
            return 0;
        }

        $discount = $this->type === 'percentage'
            ? $subtotal * min(100, max(0, (float) $this->discount_value)) / 100
            : max(0, (float) $this->discount_value);

        return round(min($subtotal, $discount, (float) ($this->maximum_discount ?? $subtotal)), 2);
    }

    public function getDiscountLabelAttribute(): string
    {
        return $this->type === 'percentage'
            ? rtrim(rtrim(number_format((float) $this->discount_value, 2, '.', ''), '0'), '.').'%'
            : number_format((float) $this->discount_value, 0, ',', '.').'₫';
    }

    public function getImageUrlAttribute(): string
    {
        $path = $this->image_path ?: 'images/promotions/maker.png';

        return str_starts_with($path, 'promotions/')
            ? Storage::disk('public')->url($path)
            : asset($path);
    }

    public function getStatusLabelAttribute(): string
    {
        return match (true) {
            ! $this->is_active => 'Tạm dừng',
            $this->starts_at?->isFuture() === true => 'Sắp diễn ra',
            $this->expires_at?->isPast() === true => 'Đã hết hạn',
            $this->usage_limit !== null && $this->used_count >= $this->usage_limit => 'Hết lượt',
            default => 'Đang áp dụng',
        };
    }
}
