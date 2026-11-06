<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_intents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cart_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('order_code')->unique();
            $table->string('public_token', 64)->unique();
            $table->string('status', 30)->default('creating')->index();
            $table->unsignedBigInteger('amount');
            $table->string('description', 9);
            $table->string('payment_link_id')->nullable()->unique();
            $table->text('checkout_url')->nullable();
            $table->text('qr_code')->nullable();
            $table->json('payload');
            $table->string('reference')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['cart_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_intents');
    }
};
