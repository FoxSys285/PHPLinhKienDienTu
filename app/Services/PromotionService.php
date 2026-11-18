<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class PromotionService
{
    /** @return array{subtotal: float, coupon: ?Coupon, discount: float, shipping: float, vat: float, total: float, eligible: array<int, array{coupon: Coupon, discount: float}>} */
    public function quote(Cart $cart, ?string $code = null): array
    {
        $cart->loadMissing('items.product.priceTiers', 'items.product.category');
        $lines = [];

        foreach ($cart->items as $item) {
            if ($item->product) {
                $lines[] = [
                    'category_slug' => $item->product->category?->slug,
                    'line_total' => round($item->product->priceFor((int) $item->quantity) * $item->quantity, 2),
                ];
            }
        }

        return $this->select($lines, $code);
    }

    /**
     * @param  array<int, array{category_slug: ?string, line_total: float}>  $lines
     * @return array{subtotal: float, coupon: ?Coupon, discount: float, shipping: float, vat: float, total: float, eligible: array<int, array{coupon: Coupon, discount: float}>}
     */
    public function select(array $lines, ?string $code = null, bool $lock = false): array
    {
        $code = strtoupper(trim($code ?? ''));
        $subtotal = (float) array_sum(array_column($lines, 'line_total'));
        $query = Coupon::query()->available()
            ->where(fn (Builder $query): Builder => $query->where('is_automatic', true)->when($code !== '', fn (Builder $query): Builder => $query->orWhere('code', $code)))
            ->orderBy('id');
        $coupons = ($lock ? $query->lockForUpdate() : $query)->get();
        $best = ['subtotal' => $subtotal, 'coupon' => null, 'discount' => 0.0, ...$this->totals($subtotal, 0)];
        $eligible = [];
        $manualIsValid = $code === '';

        foreach ($coupons as $coupon) {
            $eligibleSubtotal = (float) array_sum(array_map(
                fn (array $line): float => empty($coupon->category_slugs) || in_array($line['category_slug'], $coupon->category_slugs, true) ? $line['line_total'] : 0.0,
                $lines,
            ));
            $discount = $coupon->discountFor($eligibleSubtotal);

            if ($discount <= 0) {
                continue;
            }

            $manualIsValid = $manualIsValid || $coupon->code === $code;
            $eligible[] = ['coupon' => $coupon, 'discount' => $discount];
            $totals = $this->totals($subtotal, $discount);

            if ($totals['total'] < $best['total']) {
                $best = ['subtotal' => $subtotal, 'coupon' => $coupon, 'discount' => $discount, ...$totals];
            }
        }

        if (! $manualIsValid) {
            throw ValidationException::withMessages(['coupon_code' => 'Mã ưu đãi đã hết hạn, hết lượt hoặc giỏ hàng chưa đủ điều kiện áp dụng.']);
        }

        return [...$best, 'eligible' => $eligible];
    }

    /** @return array{shipping: float, vat: float, total: float} */
    private function totals(float $subtotal, float $discount): array
    {
        $net = max(0, $subtotal - $discount);
        $shipping = $net >= (float) config('store.free_shipping_threshold') ? 0.0 : (float) config('store.standard_shipping_fee');
        $vat = round($net * (float) config('store.vat_rate') / 100, 2);

        return ['shipping' => $shipping, 'vat' => $vat, 'total' => round($net + $shipping + $vat, 2)];
    }
}
