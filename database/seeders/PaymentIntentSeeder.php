<?php

namespace Database\Seeders;

use App\Models\PaymentIntent;
use Illuminate\Database\Seeder;

class PaymentIntentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentIntent::factory()->count(3)->create();
    }
}
