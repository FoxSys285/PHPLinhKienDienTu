<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('promotions') as $slug => $data) {
            $coupon = Coupon::query()->firstOrNew(['code' => $data['code']]);

            if ($coupon->exists && $coupon->slug !== null) {
                continue;
            }

            $coupon->fill([
                ...$data,
                'slug' => $slug,
                'is_active' => true,
                'is_automatic' => true,
                'starts_at' => now()->startOfDay(),
                'expires_at' => now()->addMonths(3)->endOfDay(),
            ])->save();
        }
    }
}
