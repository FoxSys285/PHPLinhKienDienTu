<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false);
            $table->text('blocked_reason')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->foreignId('voucher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('voucher_code', 40)->nullable();
            $table->index(['status', 'payment_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'payment_status', 'created_at']);
            $table->dropConstrainedForeignId('voucher_id');
            $table->dropColumn(['discount_amount', 'voucher_code']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_blocked', 'blocked_reason']);
        });
    }
};
