<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('stock_deducted_at')->nullable();
            $table->string('shipment_status', 32)->default('not_created')->index();
            $table->string('shipment_service', 100)->nullable();
            $table->string('shipment_number', 100)->nullable()->unique();
            $table->string('shipment_tracking_url', 500)->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('shipment_handed_over_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('shipment_updated_at')->nullable();
            $table->string('cod_status', 32)->default('not_applicable')->index();
            $table->timestamp('cod_collected_at')->nullable();
            $table->timestamp('cod_reconciled_at')->nullable();
            $table->string('refund_status', 32)->default('not_required')->index();
            $table->string('refund_reference', 120)->nullable();
            $table->timestamp('refunded_at')->nullable();
        });

        Schema::create('order_shipment_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider_event_id', 100)->unique();
            $table->string('source', 32)->default('manual');
            $table->string('status', 32);
            $table->string('description')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'created_at']);
        });

        DB::table('orders')->whereIn('status', ['confirmed', 'preparing', 'shipped', 'completed', 'failed', 'returned'])->update(['stock_deducted_at' => DB::raw('COALESCE(stock_deducted_at, updated_at)')]);
        DB::table('orders')->where('payment_method', 'cod')->update(['cod_status' => 'pending_collection']);
        DB::table('orders')->where('payment_method', 'cod')->where('status', 'completed')->update(['cod_status' => 'collected']);
        DB::table('orders')->whereIn('status', ['preparing', 'confirmed'])->update(['shipment_status' => 'not_created']);
        DB::table('orders')->whereIn('status', ['shipped', 'in_transit'])->update(['shipment_status' => 'in_transit']);
        DB::table('orders')->where('status', 'completed')->update(['shipment_status' => 'delivered']);
        DB::table('orders')->where('status', 'failed')->update(['shipment_status' => 'delivery_failed']);
        DB::table('orders')->where('status', 'returned')->update(['shipment_status' => 'returned']);
        DB::table('orders')->where('status', 'cancelled')->update(['shipment_status' => 'cancelled']);
        DB::table('orders')->where('status', 'confirmed')->update(['status' => 'preparing']);
        DB::table('orders')->where('status', 'shipped')->update(['status' => 'in_transit']);
        DB::table('orders')->where('status', 'failed')->update(['status' => 'delivery_failed']);
    }

    public function down(): void
    {
        Schema::dropIfExists('order_shipment_events');

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['cancelled_by']);
            $table->dropIndex(['shipment_status']);
            $table->dropUnique(['shipment_number']);
            $table->dropIndex(['cod_status']);
            $table->dropIndex(['refund_status']);
            $table->dropColumn([
                'cancellation_reason',
                'cancelled_by',
                'cancelled_at',
                'stock_deducted_at',
                'shipment_status',
                'shipment_service',
                'shipment_number',
                'shipment_tracking_url',
                'estimated_delivery_at',
                'shipment_handed_over_at',
                'delivered_at',
                'shipment_updated_at',
                'cod_status',
                'cod_collected_at',
                'cod_reconciled_at',
                'refund_status',
                'refund_reference',
                'refunded_at',
            ]);
        });
    }
};
