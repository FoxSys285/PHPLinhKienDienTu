<x-mail::message>
# {{ $headline }}

Đơn hàng **{{ $order->order_number }}**: {{ $detail }}

<x-mail::button :url="$orderUrl">
Xem đơn hàng
</x-mail::button>

Trân trọng,<br>
{{ config('app.name') }}
</x-mail::message>
