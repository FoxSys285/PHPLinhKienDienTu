<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categories = collect([
            ['name' => 'Điện trở', 'slug' => 'dien-tro', 'sort_order' => 1],
            ['name' => 'Tụ điện', 'slug' => 'tu-dien', 'sort_order' => 2],
            ['name' => 'Bán dẫn & IC', 'slug' => 'ban-dan-ic', 'sort_order' => 3],
            ['name' => 'Vi điều khiển', 'slug' => 'vi-dieu-khien', 'sort_order' => 4],
            ['name' => 'Module & cảm biến', 'slug' => 'module-cam-bien', 'sort_order' => 5],
            ['name' => 'Board phát triển', 'slug' => 'board-phat-trien', 'sort_order' => 6],
            ['name' => 'Dụng cụ & phụ kiện', 'slug' => 'dung-cu-phu-kien', 'sort_order' => 7],
        ])->mapWithKeys(function (array $data): array {
            $category = Category::query()->updateOrCreate(['slug' => $data['slug']], $data + ['is_active' => true]);

            return [$data['slug'] => $category];
        });

        $products = [
            ['category' => 'dien-tro', 'sku' => 'R-10K-025W', 'mpn' => 'CFR-25JB-52-10K', 'name' => 'Điện trở carbon 10KΩ 1/4W', 'brand' => 'Yageo', 'price' => 500, 'stock' => 2400, 'specifications' => ['Giá trị' => '10 kΩ', 'Dung sai' => '±5%', 'Công suất' => '0.25 W', 'Gói' => 'Axial'], 'is_rohs' => true],
            ['category' => 'dien-tro', 'sku' => 'R-330R-0603', 'mpn' => 'RC0603FR-07330RL', 'name' => 'Điện trở dán 330Ω 0603', 'brand' => 'Yageo', 'price' => 350, 'stock' => 9500, 'specifications' => ['Giá trị' => '330 Ω', 'Dung sai' => '±1%', 'Công suất' => '0.1 W', 'Gói' => '0603'], 'is_rohs' => true],
            ['category' => 'tu-dien', 'sku' => 'C-100U-25V', 'mpn' => 'EEU-FR1E101', 'name' => 'Tụ điện phân 100µF 25V', 'brand' => 'Panasonic', 'price' => 1800, 'stock' => 620, 'specifications' => ['Điện dung' => '100 µF', 'Điện áp' => '25 V', 'Loại' => 'Điện phân'], 'is_rohs' => true],
            ['category' => 'tu-dien', 'sku' => 'C-100N-50V-0805', 'mpn' => 'CC0805KRX7R9BB104', 'name' => 'Tụ gốm đa lớp 100nF 50V 0805', 'brand' => 'Yageo', 'price' => 650, 'stock' => 4800, 'specifications' => ['Điện dung' => '100 nF', 'Điện áp' => '50 V', 'Gói' => '0805', 'Điện môi' => 'X7R'], 'is_rohs' => true],
            ['category' => 'ban-dan-ic', 'sku' => 'IC-NE555P', 'mpn' => 'NE555P', 'name' => 'IC tạo dao động NE555 DIP-8', 'brand' => 'Texas Instruments', 'price' => 6500, 'stock' => 145, 'specifications' => ['Loại' => 'Timer', 'Số chân' => '8', 'Gói' => 'DIP'], 'is_rohs' => true],
            ['category' => 'ban-dan-ic', 'sku' => 'MOS-IRLZ44N', 'mpn' => 'IRLZ44NPBF', 'name' => 'MOSFET kênh N IRLZ44N', 'brand' => 'Infineon', 'price' => 14500, 'stock' => 88, 'specifications' => ['VDS' => '55 V', 'ID' => '47 A', 'Gói' => 'TO-220'], 'is_rohs' => true, 'is_esd_sensitive' => true],
            ['category' => 'vi-dieu-khien', 'sku' => 'MCU-ATMEGA328P-PU', 'mpn' => 'ATMEGA328P-PU', 'name' => 'Vi điều khiển ATmega328P DIP-28', 'brand' => 'Microchip', 'price' => 42000, 'stock' => 38, 'specifications' => ['Kiến trúc' => 'AVR 8-bit', 'Flash' => '32 KB', 'Gói' => 'DIP-28'], 'is_rohs' => true, 'is_esd_sensitive' => true],
            ['category' => 'module-cam-bien', 'sku' => 'MOD-DHT22', 'mpn' => 'AM2302', 'name' => 'Cảm biến nhiệt độ, độ ẩm DHT22', 'brand' => 'Aosong', 'price' => 58000, 'stock' => 42, 'specifications' => ['Nhiệt độ' => '-40 đến 80°C', 'Độ ẩm' => '0–100% RH', 'Giao tiếp' => 'Digital'], 'is_rohs' => true],
            ['category' => 'module-cam-bien', 'sku' => 'MOD-ESP32-WROOM', 'mpn' => 'ESP32-WROOM-32E', 'name' => 'Module Wi-Fi Bluetooth ESP32-WROOM-32E', 'brand' => 'Espressif', 'price' => 89000, 'stock' => 73, 'specifications' => ['Wi-Fi' => '2.4 GHz', 'Bluetooth' => 'BLE 4.2', 'MCU' => 'Dual-core 240 MHz'], 'is_rohs' => true, 'is_esd_sensitive' => true],
            ['category' => 'board-phat-trien', 'sku' => 'BOARD-ARDUINO-UNO-R3', 'mpn' => 'A000066', 'name' => 'Board phát triển Arduino Uno R3', 'brand' => 'Arduino', 'price' => 185000, 'stock' => 56, 'specifications' => ['MCU' => 'ATmega328P', 'Điện áp hoạt động' => '5 V', 'Digital I/O' => '14'], 'is_rohs' => true],
            ['category' => 'board-phat-trien', 'sku' => 'BOARD-ESP32-DEVKIT', 'mpn' => 'ESP32-DEVKITC-32E', 'name' => 'Board ESP32 DevKitC V4', 'brand' => 'Espressif', 'price' => 165000, 'stock' => 31, 'specifications' => ['MCU' => 'ESP32-WROOM-32E', 'Wi-Fi' => '2.4 GHz', 'USB' => 'Micro USB'], 'is_rohs' => true, 'is_esd_sensitive' => true],
            ['category' => 'dung-cu-phu-kien', 'sku' => 'TOOL-ESD-TWEEZER', 'mpn' => '6-SA-ESD', 'name' => 'Nhíp chống tĩnh điện ESD-15', 'brand' => 'Proskit', 'price' => 39000, 'stock' => 120, 'specifications' => ['Vật liệu' => 'Thép không gỉ', 'Chiều dài' => '120 mm', 'Chống tĩnh điện' => 'Có'], 'is_rohs' => false],
        ];

        foreach ($products as $data) {
            $category = $categories->get($data['category']);
            unset($data['category']);
            $product = Product::query()->updateOrCreate(
                ['sku' => $data['sku']],
                $data + [
                    'category_id' => $category->id,
                    'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
                    'description' => 'Linh kiện chính hãng, được kiểm tra trước khi đóng gói. Thông số chi tiết được cung cấp theo tài liệu của nhà sản xuất.',
                    'minimum_stock' => 10,
                    'moq' => 1,
                    'pack_size' => 1,
                    'reserved_stock' => 0,
                    'is_esd_sensitive' => $data['is_esd_sensitive'] ?? false,
                    'allow_backorder' => false,
                    'is_active' => true,
                ],
            );

            foreach ([[10, 99, 0.95], [100, 999, 0.88], [1000, null, 0.8]] as [$minimum, $maximum, $multiplier]) {
                PriceTier::query()->updateOrCreate(
                    ['product_id' => $product->id, 'price_group' => 'retail', 'minimum_quantity' => $minimum],
                    ['maximum_quantity' => $maximum, 'unit_price' => round((float) $product->price * $multiplier, 2)],
                );
            }
        }

        Coupon::query()->updateOrCreate(
            ['code' => 'MAKER10'],
            ['type' => 'percentage', 'discount_value' => 10, 'minimum_purchase' => 100000, 'usage_limit' => 500, 'used_count' => 0, 'is_active' => true],
        );

        if (app()->environment('local')) {
            User::query()->updateOrCreate(
                ['email' => env('STORE_ADMIN_EMAIL', 'admin@linhkien.local')],
                [
                    'name' => 'Quản trị cửa hàng',
                    'password' => env('STORE_ADMIN_PASSWORD', 'ChangeMe123!'),
                    'role' => 'admin',
                    'account_type' => 'business',
                    'business_approved' => true,
                    'price_group' => 'wholesale',
                ],
            );
        }

        $this->call(AdditionalProductsSeeder::class);
        $this->call(ProductImageSeeder::class);
    }
}
