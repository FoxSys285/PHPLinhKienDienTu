<x-mail::message>
# {{ $product->name }} đã có hàng

Sản phẩm **{{ $product->sku }}** hiện đã có tồn kho khả dụng. Bạn có thể xem lại thông số và tình trạng hàng trước khi đặt.

<x-mail::button :url="route('products.show', $product)">
Xem sản phẩm
</x-mail::button>

Cảm ơn bạn,<br>
{{ config('app.name') }}
</x-mail::message>
