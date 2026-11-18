<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table): void {
            $table->string('slug')->nullable()->unique();
            $table->string('title')->nullable();
            $table->string('eyebrow', 80)->nullable();
            $table->text('description')->nullable();
            $table->text('terms')->nullable();
            $table->string('image_path')->nullable();
            $table->string('theme', 20)->default('blue');
            $table->json('category_slugs')->nullable();
            $table->decimal('maximum_discount', 14, 2)->nullable();
            $table->boolean('is_automatic')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->string('coupon_code', 60)->nullable();
            $table->string('coupon_title')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'title', 'eyebrow', 'description', 'terms', 'image_path', 'theme', 'category_slugs', 'maximum_discount', 'is_automatic', 'sort_order']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['coupon_code', 'coupon_title']);
        });
    }
};
