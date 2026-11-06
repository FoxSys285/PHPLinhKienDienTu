<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'coupon_id', 'order_number', 'status', 'payment_method', 'payment_status', 'customer_name', 'customer_email', 'customer_phone', 'shipping_address', 'shipping_city', 'company_name', 'tax_number', 'subtotal', 'discount_total', 'shipping_total', 'vat_total', 'total', 'customer_note', 'reserved_until', 'confirmed_at', 'cancellation_reason', 'cancelled_by', 'cancelled_at', 'stock_deducted_at', 'shipment_status', 'shipment_service', 'shipment_number', 'shipment_tracking_url', 'estimated_delivery_at', 'shipment_handed_over_at', 'delivered_at', 'shipment_updated_at', 'cod_status', 'cod_collected_at', 'cod_reconciled_at', 'refund_status', 'refund_reference', 'refunded_at', 'guest_access_token'])]
class Order extends Model
{
    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'shipping_total' => 'decimal:2',
            'vat_total' => 'decimal:2',
            'total' => 'decimal:2',
            'reserved_until' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'stock_deducted_at' => 'datetime',
            'estimated_delivery_at' => 'datetime',
            'shipment_handed_over_at' => 'datetime',
            'delivered_at' => 'datetime',
            'shipment_updated_at' => 'datetime',
            'cod_collected_at' => 'datetime',
            'cod_reconciled_at' => 'datetime',
            'refunded_at' => 'datetime',
            'guest_access_token' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function returnRequests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function shipmentEvents(): HasMany
    {
        return $this->hasMany(OrderShipmentEvent::class)->latest('occurred_at')->latest('id');
    }

    public function paymentIntent(): HasOne
    {
        return $this->hasOne(PaymentIntent::class);
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
