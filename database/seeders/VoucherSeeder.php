<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        Voucher::query()->firstOrCreate(
            ['code' => 'MACHVIET10'],
            [
                'discount_type' => 'percent',
                'discount_value' => 10,
                'max_discount' => 100000,
                'minimum_order' => 100000,
                'starts_at' => now()->startOfDay(),
                'ends_at' => now()->addMonths(2)->endOfDay(),
                'usage_limit' => 500,
                'is_active' => true,
            ],
        );
    }
}
