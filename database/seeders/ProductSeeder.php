<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Product::query()->exists()) {
            return;
        }

        $categories = collect([
            ['name' => 'Bo mạch', 'slug' => 'bo-mach', 'icon' => '▦'],
            ['name' => 'Cảm biến', 'slug' => 'cam-bien', 'icon' => '⌕'],
            ['name' => 'Nguồn', 'slug' => 'nguon', 'icon' => 'ϟ'],
            ['name' => 'Module', 'slug' => 'module', 'icon' => '⌘'],
            ['name' => 'Dụng cụ', 'slug' => 'dung-cu', 'icon' => '⚒'],
        ])->mapWithKeys(function (array $category): array {
            $model = Category::query()->firstOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name'], 'icon' => $category['icon'], 'is_active' => true],
            );

            return [$category['slug'] => $model];
        });

        $image = 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1000&q=85';

        $products = [
            [
                'category' => 'module', 'name' => 'Bo mạch Arduino Uno R3', 'slug' => 'arduino-uno-r3', 'sku' => 'MV-ARD-UNO-R3',
                'description' => 'Bo mạch phát triển dễ sử dụng cho các dự án học tập, tự động hóa và chế tạo thiết bị thông minh.',
                'features' => ['Tương thích hệ sinh thái Arduino', 'Cổng USB Type-B tiện kết nối', 'Phù hợp cho người mới bắt đầu'],
                'specifications' => [['name' => 'Vi điều khiển', 'value' => 'ATmega328P'], ['name' => 'Điện áp hoạt động', 'value' => '5V'], ['name' => 'Kết nối', 'value' => 'USB Type-B']],
                'price' => 145000, 'old_price' => 169000, 'stock' => 24, 'sold_count' => 128, 'badge' => 'Bán chạy', 'is_featured' => true,
            ],
            [
                'category' => 'module', 'name' => 'Bo mạch ESP32 DevKit V1', 'slug' => 'esp32-devkit-v1', 'sku' => 'MV-ESP-32-V1',
                'description' => 'Module Wi-Fi và Bluetooth cho thiết bị IoT, nhà thông minh và các dự án kết nối không dây.',
                'features' => ['Wi-Fi 2.4 GHz và Bluetooth', 'Nhiều chân GPIO', 'Hỗ trợ Arduino IDE'],
                'specifications' => [['name' => 'Vi điều khiển', 'value' => 'ESP32-WROOM-32'], ['name' => 'Điện áp logic', 'value' => '3.3V'], ['name' => 'Kết nối', 'value' => 'Wi-Fi, Bluetooth']],
                'price' => 125000, 'old_price' => 149000, 'stock' => 31, 'sold_count' => 96, 'badge' => 'Nổi bật', 'is_featured' => true,
            ],
            [
                'category' => 'bo-mach', 'name' => 'Mạch Arduino Nano CH340', 'slug' => 'arduino-nano-ch340', 'sku' => 'MV-ARD-NANO',
                'description' => 'Phiên bản Arduino nhỏ gọn cho mô hình robot, thiết bị đeo và các mạch cần tiết kiệm không gian.',
                'features' => ['Kích thước nhỏ gọn', 'Giao tiếp USB mini', 'Dễ nạp chương trình'],
                'specifications' => [['name' => 'Vi điều khiển', 'value' => 'ATmega328P'], ['name' => 'Điện áp hoạt động', 'value' => '5V'], ['name' => 'Kích thước', 'value' => '18 × 45 mm']],
                'price' => 79000, 'old_price' => null, 'stock' => 42, 'sold_count' => 73, 'badge' => null, 'is_featured' => true,
            ],
            [
                'category' => 'cam-bien', 'name' => 'Cảm biến nhiệt độ độ ẩm DHT22', 'slug' => 'cam-bien-dht22', 'sku' => 'MV-SEN-DHT22',
                'description' => 'Cảm biến đo nhiệt độ và độ ẩm kỹ thuật số cho trạm thời tiết và hệ thống giám sát môi trường.',
                'features' => ['Đo nhiệt độ và độ ẩm', 'Tín hiệu đầu ra kỹ thuật số', 'Dễ tích hợp với Arduino'],
                'specifications' => [['name' => 'Nhiệt độ đo', 'value' => '-40 đến 80°C'], ['name' => 'Độ ẩm đo', 'value' => '0 đến 100% RH'], ['name' => 'Điện áp', 'value' => '3.3–5V']],
                'price' => 65000, 'old_price' => 75000, 'stock' => 37, 'sold_count' => 52, 'badge' => 'Giá tốt', 'is_featured' => true,
            ],
            [
                'category' => 'cam-bien', 'name' => 'Cảm biến siêu âm HC-SR04', 'slug' => 'cam-bien-sieu-am-hc-sr04', 'sku' => 'MV-SEN-HCSR04',
                'description' => 'Cảm biến đo khoảng cách phổ biến cho robot tránh vật cản và các mô hình tự động.',
                'features' => ['Khoảng cách đo đến 4 m', 'Giao tiếp Trigger và Echo', 'Hoạt động ổn định'],
                'specifications' => [['name' => 'Khoảng cách đo', 'value' => '2–400 cm'], ['name' => 'Điện áp', 'value' => '5V'], ['name' => 'Tần số', 'value' => '40 kHz']],
                'price' => 25000, 'old_price' => null, 'stock' => 58, 'sold_count' => 214, 'badge' => 'Bán chạy', 'is_featured' => true,
            ],
            [
                'category' => 'nguon', 'name' => 'Nguồn tổ ong 5V 10A', 'slug' => 'nguon-to-ong-5v-10a', 'sku' => 'MV-PWR-5V10A',
                'description' => 'Bộ nguồn chuyển mạch 5V công suất cao cho dải LED, camera và các hệ thống điện tử.',
                'features' => ['Điện áp đầu ra ổn định', 'Có khe tản nhiệt', 'Bảo vệ quá tải cơ bản'],
                'specifications' => [['name' => 'Điện áp đầu ra', 'value' => '5V DC'], ['name' => 'Dòng tối đa', 'value' => '10A'], ['name' => 'Công suất', 'value' => '50W']],
                'price' => 115000, 'old_price' => 135000, 'stock' => 16, 'sold_count' => 31, 'badge' => null, 'is_featured' => false,
            ],
            [
                'category' => 'nguon', 'name' => 'Mạch giảm áp LM2596', 'slug' => 'mach-giam-ap-lm2596', 'sku' => 'MV-PWR-LM2596',
                'description' => 'Module hạ áp DC-DC có thể điều chỉnh điện áp đầu ra cho nhiều nhu cầu cấp nguồn.',
                'features' => ['Điện áp đầu ra điều chỉnh được', 'Có đồng hồ hiển thị', 'Tích hợp biến trở tinh chỉnh'],
                'specifications' => [['name' => 'Điện áp đầu vào', 'value' => '4–40V DC'], ['name' => 'Điện áp đầu ra', 'value' => '1.25–37V DC'], ['name' => 'Dòng tối đa', 'value' => '3A']],
                'price' => 18000, 'old_price' => null, 'stock' => 64, 'sold_count' => 146, 'badge' => null, 'is_featured' => false,
            ],
            [
                'category' => 'bo-mach', 'name' => 'Bo mạch Raspberry Pi Pico', 'slug' => 'raspberry-pi-pico', 'sku' => 'MV-RPI-PICO',
                'description' => 'Bo mạch vi điều khiển nhỏ gọn dùng chip RP2040, phù hợp cho học lập trình và chế tạo thiết bị.',
                'features' => ['Vi điều khiển RP2040 lõi kép', 'Hỗ trợ MicroPython', '26 chân GPIO đa dụng'],
                'specifications' => [['name' => 'Vi điều khiển', 'value' => 'RP2040'], ['name' => 'Bộ nhớ', 'value' => '264 KB SRAM'], ['name' => 'Kết nối', 'value' => 'Micro USB']],
                'price' => 135000, 'old_price' => null, 'stock' => 19, 'sold_count' => 28, 'badge' => 'Mới', 'is_featured' => true,
            ],
            [
                'category' => 'dung-cu', 'name' => 'Bộ dụng cụ hàn điện tử mini', 'slug' => 'bo-dung-cu-han-mini', 'sku' => 'MV-TOOL-SOLDER',
                'description' => 'Bộ dụng cụ cơ bản hỗ trợ hàn nối linh kiện và hoàn thiện các dự án điện tử tại nhà.',
                'features' => ['Phù hợp sửa chữa cơ bản', 'Dễ mang theo', 'Gồm phụ kiện cần thiết'],
                'specifications' => [['name' => 'Ứng dụng', 'value' => 'Hàn linh kiện điện tử'], ['name' => 'Bộ sản phẩm', 'value' => 'Mỏ hàn và phụ kiện']],
                'price' => 289000, 'old_price' => 329000, 'stock' => 12, 'sold_count' => 19, 'badge' => null, 'is_featured' => false,
            ],
            [
                'category' => 'dung-cu', 'name' => 'Breadboard MB-102 830 lỗ', 'slug' => 'breadboard-mb-102', 'sku' => 'MV-TOOL-BB102',
                'description' => 'Bảng cắm thử giúp lắp ráp mạch nhanh mà không cần hàn, thích hợp cho học tập và thử nghiệm.',
                'features' => ['830 điểm cắm', 'Dùng lại nhiều lần', 'Có thanh nguồn hai bên'],
                'specifications' => [['name' => 'Số điểm cắm', 'value' => '830'], ['name' => 'Kích thước', 'value' => '165 × 55 mm']],
                'price' => 22000, 'old_price' => null, 'stock' => 45, 'sold_count' => 87, 'badge' => null, 'is_featured' => false,
            ],
        ];

        foreach ($products as $product) {
            $categorySlug = $product['category'];
            unset($product['category']);

            Product::query()->firstOrCreate(
                ['sku' => $product['sku']],
                [
                    ...$product,
                    'category_id' => $categories->get($categorySlug)->id,
                    'image' => $image,
                    'gallery_images' => [$image],
                    'is_active' => true,
                ],
            );
        }
    }
}
