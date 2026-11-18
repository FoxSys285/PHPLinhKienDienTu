<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DemoCommerceSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $customerNames = [
            'Nguyễn Minh Anh',
            'Trần Quốc Bảo',
            'Lê Hoàng Nam',
            'Phạm Thu Hà',
            'Võ Đức Huy',
            'Đặng Ngọc Linh',
            'Bùi Thành Đạt',
            'Phan Mỹ Duyên',
            'Hồ Gia Khang',
            'Đỗ Khánh Vy',
            'Mai Anh Tuấn',
            'Cao Thanh Trúc',
        ];

        $customers = collect($customerNames)->mapWithKeys(function (string $name, int $index): array {
            $number = $index + 1;
            $email = sprintf('demo.customer.%02d@example.test', $number);

            $customer = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => 'Demo123!',
                    'phone' => sprintf('090%07d', $number),
                    'role' => 'customer',
                    'email_verified_at' => now(),
                ],
            );

            return [$email => $customer];
        });

        $products = Product::query()->where('is_active', true)->orderBy('id')->get();

        if ($products->count() < 2) {
            throw new RuntimeException('Cần ít nhất hai sản phẩm đang kinh doanh để tạo dữ liệu đơn hàng mẫu.');
        }

        $statuses = [
            'pending_confirmation', 'in_transit', 'completed', 'completed',
            'preparing', 'pending_payment', 'completed', 'cancelled',
            'completed', 'delivery_failed', 'completed', 'completed',
            'preparing', 'in_transit', 'completed', 'pending_confirmation',
            'completed', 'completed', 'preparing', 'completed',
            'pending_payment', 'completed', 'in_transit', 'completed',
        ];
        $completedOrders = [];

        foreach ($statuses as $index => $status) {
            $orderNumber = sprintf('DEMO-26-%04d', $index + 1);
            $customer = $customers->values()[$index % $customers->count()];
            $firstProductIndex = ($index * 3) % $products->count();
            $placedAt = now()->subDays(($index * 3 + 1) % 60)->subHours($index % 12);
            $paymentMethod = ($index + 1) % 2 === 0 ? 'cod' : 'bank_transfer';
            $isCompleted = $status === 'completed';
            $hasLeftWarehouse = in_array($status, ['preparing', 'in_transit', 'completed', 'delivery_failed'], true);
            $isCod = $paymentMethod === 'cod';
            $lines = [];

            for ($lineIndex = 0; $lineIndex < min(8, $products->count()); $lineIndex++) {
                $product = $products[($firstProductIndex + $lineIndex) % $products->count()];
                $lines[] = [$product, (($index + $lineIndex) % 3) + 1];
            }

            $subtotal = collect($lines)->sum(fn (array $line): float => (float) $line[0]->price * $line[1]);
            $shipmentStatus = match ($status) {
                'in_transit' => 'in_transit',
                'completed' => 'delivered',
                'delivery_failed' => 'delivery_failed',
                'cancelled' => 'cancelled',
                default => 'not_created',
            };

            $order = Order::query()->updateOrCreate(
                ['order_number' => $orderNumber],
                [
                    'user_id' => $customer->id,
                    'status' => $status,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $isCompleted ? 'paid' : 'pending',
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'customer_phone' => $customer->phone,
                    'shipping_address' => sprintf('%d Đường Demo, Phường 1', $index + 1),
                    'shipping_city' => $index % 2 === 0 ? 'Hồ Chí Minh' : 'Hà Nội',
                    'subtotal' => $subtotal,
                    'discount_total' => 0,
                    'shipping_total' => 30000,
                    'vat_total' => 0,
                    'total' => $subtotal + 30000,
                    'customer_note' => 'Dữ liệu đơn hàng minh họa.',
                    'confirmed_at' => $hasLeftWarehouse ? $placedAt : null,
                    'stock_deducted_at' => $hasLeftWarehouse ? $placedAt : null,
                    'shipment_status' => $shipmentStatus,
                    'shipment_service' => in_array($status, ['in_transit', 'completed', 'delivery_failed'], true) ? 'Viettel Post' : null,
                    'shipment_number' => in_array($status, ['in_transit', 'completed', 'delivery_failed'], true) ? 'DEMO-SHIP-'.sprintf('%04d', $index + 1) : null,
                    'shipment_handed_over_at' => in_array($status, ['in_transit', 'completed', 'delivery_failed'], true) ? $placedAt->copy()->addDay() : null,
                    'estimated_delivery_at' => in_array($status, ['in_transit', 'completed', 'delivery_failed'], true) ? $placedAt->copy()->addDays(4) : null,
                    'delivered_at' => $isCompleted ? $placedAt->copy()->addDays(2) : null,
                    'shipment_updated_at' => in_array($status, ['in_transit', 'completed', 'delivery_failed'], true) ? $placedAt->copy()->addDays(2) : null,
                    'cod_status' => $isCod ? ($isCompleted ? 'collected' : ($status === 'cancelled' ? 'cancelled' : 'pending_collection')) : 'not_applicable',
                    'cod_collected_at' => $isCod && $isCompleted ? $placedAt->copy()->addDays(2) : null,
                    'refund_status' => 'not_required',
                    'cancelled_at' => $status === 'cancelled' ? $placedAt->copy()->addHours(2) : null,
                    'cancellation_reason' => $status === 'cancelled' ? 'Khách yêu cầu hủy đơn (dữ liệu mẫu).' : null,
                ],
            );
            $order->forceFill(['created_at' => $placedAt, 'updated_at' => $placedAt])->saveQuietly();

            foreach ($lines as [$product, $quantity]) {
                $unitPrice = (float) $product->price;
                $order->items()->updateOrCreate(
                    ['sku' => $product->sku],
                    [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'line_total' => $unitPrice * $quantity,
                    ],
                );
            }

            if ($isCompleted) {
                $completedOrders[] = [$customer, $lines, $placedAt];
            }
        }

        $reviewRatings = [5, 4.5, 4, 5, 3.5, 4.5, 3, 5, 4, 4.5, 2.5, 5, 1, 1.5, 2];
        $reviewExperiences = [
            'Đúng mã linh kiện, lắp vào chạy ổn định và đóng gói rất cẩn thận.',
            'Thông số đúng như mô tả, chân linh kiện còn mới và sạch.',
            'Giao hàng nhanh, linh kiện hoạt động tốt trong mạch thử nghiệm.',
            'Đã dùng cho dự án cá nhân, chất lượng ổn định và dễ lắp.',
            'Sản phẩm dùng được, bao bì hơi móp nhưng linh kiện không bị ảnh hưởng.',
            'Đóng gói chống tĩnh điện tốt, đúng số lượng đã đặt.',
            'Linh kiện hoạt động bình thường, thời gian giao hàng hơi lâu.',
            'Mã hàng và giá trị linh kiện chính xác, sẽ tiếp tục mua.',
            'Chất lượng phù hợp với giá, thông tin sản phẩm dễ đối chiếu.',
            'Hàng đúng mô tả, shop đóng gói kỹ và gửi đủ số lượng.',
            'Linh kiện vẫn hoạt động nhưng ngoại hình chưa đẹp như mong đợi.',
            'Đã kiểm tra nhiều chiếc, thông số ổn định và không có lỗi.',
            'Linh kiện nhận được không hoạt động như mong đợi, cần cửa hàng hỗ trợ thêm.',
            'Mạch hoạt động không ổn định dù đã kiểm tra lại cách lắp đặt.',
            'Chất lượng chưa đạt kỳ vọng, nên kiểm tra kỹ lô hàng trước khi gửi.',
        ];
        $reviewIndex = 0;

        foreach ($completedOrders as [$customer, $lines, $placedAt]) {
            foreach ($lines as [$product]) {
                $review = Review::query()->firstOrNew([
                    'product_id' => $product->id,
                    'user_id' => $customer->id,
                ]);
                $review->fill([
                    'rating' => $reviewRatings[$reviewIndex % count($reviewRatings)],
                    'body' => $reviewExperiences[$reviewIndex % count($reviewExperiences)].' Dữ liệu đánh giá minh họa.',
                    'is_approved' => true,
                ]);
                $review->save();
                $review->forceFill([
                    'created_at' => $placedAt->copy()->addDays(3),
                    'updated_at' => $placedAt->copy()->addDays(3),
                ])->saveQuietly();
                $reviewIndex++;
            }
        }
    }
}
