<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'type', 'discount_value', 'minimum_purchase', 'usage_limit', 'used_count', 'starts_at', 'expires_at', 'is_active'])]
class Coupon extends Model
{
    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'minimum_purchase' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function discountFor(float $subtotal): float
    {
        if (! $this->is_active || $subtotal < (float) $this->minimum_purchase) {
            return 0;
        }

        if ($this->starts_at?->isFuture() || $this->expires_at?->isPast()) {
            return 0;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return 0;
        }

        $discount = $this->type === 'percentage'
            ? $subtotal * min(100, (float) $this->discount_value) / 100
            : (float) $this->discount_value;

        return min($subtotal, $discount);
    }
}
