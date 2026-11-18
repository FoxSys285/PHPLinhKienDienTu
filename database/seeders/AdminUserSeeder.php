<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        User::query()->updateOrCreate(
            ['email' => env('STORE_ADMIN_EMAIL', 'admin@linhkien.local')],
            [
                'name' => 'Quản trị cửa hàng',
                'password' => env('STORE_ADMIN_PASSWORD', 'ChangeMe123!'),
                'role' => 'admin',
                'account_type' => 'business',
                'business_approved' => true,
                'price_group' => 'wholesale',
            ],
        );
    }
}
