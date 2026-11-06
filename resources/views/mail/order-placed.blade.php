<x-mail::message>
# Cảm ơn {{ $order->customer_name }}

Chúng tôi đã nhận đơn hàng **{{ $order->order_number }}**. Nhân viên sẽ xác nhận thông tin trước khi chuyển đơn sang bước chuẩn bị.

**Tổng đơn hàng:** {{ number_format((float) $order->total, 0, ',', '.') }}₫  
**Phương thức thanh toán:** {{ $order->payment_method === 'cod' ? 'Thanh toán khi nhận hàng' : 'Chuyển khoản ngân hàng' }}

@if($order->user_id)
<x-mail::button :url="route('account.orders.show', $order)">
Theo dõi đơn hàng
</x-mail::button>
@elseif($order->guest_access_token)
<x-mail::button :url="route('checkout.guest-order.show', ['order' => $order, 'token' => $order->guest_access_token])">
Theo dõi hoặc hủy đơn hàng
</x-mail::button>
@endif

Cảm ơn bạn,<br>
{{ config('app.name') }}
</x-mail::message>
