<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\PriceTier;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class AdditionalProductsSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['category' => 'dien-tro', 'sku' => 'RES-1K-0603', 'mpn' => 'RC0603FR-071KL', 'name' => 'Điện trở dán 1KΩ 0603', 'brand' => 'Yageo', 'price' => 400, 'stock' => 7200, 'specifications' => ['Giá trị' => '1 kΩ', 'Dung sai' => '±1%', 'Công suất' => '0.1 W', 'Gói' => '0603'], 'is_rohs' => true],
            ['category' => 'dien-tro', 'sku' => 'RES-4K7-025W', 'mpn' => 'CFR-25JB-52-4K7', 'name' => 'Điện trở carbon 4.7KΩ 1/4W', 'brand' => 'Yageo', 'price' => 500, 'stock' => 2800, 'specifications' => ['Giá trị' => '4.7 kΩ', 'Dung sai' => '±5%', 'Công suất' => '0.25 W', 'Gói' => 'Axial'], 'is_rohs' => true],
            ['category' => 'dien-tro', 'sku' => 'RES-10K-0805', 'mpn' => 'RC0805FR-0710KL', 'name' => 'Điện trở dán 10KΩ 0805', 'brand' => 'Yageo', 'price' => 450, 'stock' => 6400, 'specifications' => ['Giá trị' => '10 kΩ', 'Dung sai' => '±1%', 'Công suất' => '0.125 W', 'Gói' => '0805'], 'is_rohs' => true],
            ['category' => 'tu-dien', 'sku' => 'CAP-10U-25V-0805', 'mpn' => 'CL21A106KAYNNNE', 'name' => 'Tụ gốm đa lớp 10µF 25V 0805', 'brand' => 'Samsung Electro-Mechanics', 'price' => 1400, 'stock' => 1900, 'specifications' => ['Điện dung' => '10 µF', 'Điện áp' => '25 V', 'Điện môi' => 'X5R', 'Gói' => '0805'], 'is_rohs' => true],
            ['category' => 'tu-dien', 'sku' => 'CAP-1U-50V-0603', 'mpn' => 'CL10B105KB8NNNC', 'name' => 'Tụ gốm đa lớp 1µF 50V 0603', 'brand' => 'Samsung Electro-Mechanics', 'price' => 700, 'stock' => 3100, 'specifications' => ['Điện dung' => '1 µF', 'Điện áp' => '50 V', 'Điện môi' => 'X7R', 'Gói' => '0603'], 'is_rohs' => true],
            ['category' => 'tu-dien', 'sku' => 'CAP-470U-16V-RAD', 'mpn' => 'EEU-FR1C471', 'name' => 'Tụ điện phân 470µF 16V', 'brand' => 'Panasonic', 'price' => 3200, 'stock' => 540, 'specifications' => ['Điện dung' => '470 µF', 'Điện áp' => '16 V', 'Loại' => 'Điện phân', 'Kiểu chân' => 'Radial'], 'is_rohs' => true],
            ['category' => 'ban-dan-ic', 'sku' => 'IC-LM358N-DIP8', 'mpn' => 'LM358N/NOPB', 'name' => 'IC khuếch đại thuật toán LM358N DIP-8', 'brand' => 'Texas Instruments', 'price' => 6500, 'stock' => 180, 'specifications' => ['Loại' => 'Dual op-amp', 'Số kênh' => '2', 'Số chân' => '8', 'Gói' => 'DIP'], 'is_rohs' => true, 'is_esd_sensitive' => true],
            ['category' => 'ban-dan-ic', 'sku' => 'REG-LM7805-TO220', 'mpn' => 'L7805CV', 'name' => 'IC ổn áp tuyến tính 5V L7805CV', 'brand' => 'STMicroelectronics', 'price' => 4800, 'stock' => 260, 'specifications' => ['Điện áp đầu ra' => '5 V', 'Dòng đầu ra tối đa' => '1.5 A', 'Gói' => 'TO-220'], 'is_rohs' => true],
            ['category' => 'ban-dan-ic', 'sku' => 'DIODE-1N4007-DO41', 'mpn' => '1N4007-E3/54', 'name' => 'Diode chỉnh lưu 1N4007 DO-41', 'brand' => 'Vishay', 'price' => 800, 'stock' => 1500, 'specifications' => ['Điện áp ngược tối đa' => '1000 V', 'Dòng thuận' => '1 A', 'Gói' => 'DO-41'], 'is_rohs' => true],
            ['category' => 'vi-dieu-khien', 'sku' => 'MCU-STM32F103C8T6', 'mpn' => 'STM32F103C8T6', 'name' => 'Vi điều khiển STM32F103C8T6 LQFP-48', 'brand' => 'STMicroelectronics', 'price' => 38000, 'stock' => 64, 'specifications' => ['Kiến trúc' => 'ARM Cortex-M3', 'Xung nhịp tối đa' => '72 MHz', 'Flash' => '64 KB', 'Gói' => 'LQFP-48'], 'is_rohs' => true, 'is_esd_sensitive' => true],
            ['category' => 'vi-dieu-khien', 'sku' => 'MCU-ATTINY85-PU', 'mpn' => 'ATTINY85-20PU', 'name' => 'Vi điều khiển ATtiny85 DIP-8', 'brand' => 'Microchip', 'price' => 29000, 'stock' => 52, 'specifications' => ['Kiến trúc' => 'AVR 8-bit', 'Flash' => '8 KB', 'EEPROM' => '512 B', 'Gói' => 'DIP-8'], 'is_rohs' => true, 'is_esd_sensitive' => true],
            ['category' => 'vi-dieu-khien', 'sku' => 'MCU-ESP32-C3-MINI-1', 'mpn' => 'ESP32-C3-MINI-1-N4', 'name' => 'Module vi điều khiển ESP32-C3-MINI-1', 'brand' => 'Espressif', 'price' => 62000, 'stock' => 45, 'specifications' => ['Kiến trúc' => 'RISC-V 32-bit', 'Wi-Fi' => '2.4 GHz 802.11 b/g/n', 'Bluetooth' => 'Bluetooth LE 5', 'Flash' => '4 MB'], 'is_rohs' => true, 'is_esd_sensitive' => true],
            ['category' => 'module-cam-bien', 'sku' => 'MOD-BME280-I2C', 'mpn' => null, 'name' => 'Module cảm biến nhiệt độ, độ ẩm BME280', 'brand' => 'Waveshare', 'price' => 79000, 'stock' => 36, 'specifications' => ['Cảm biến' => 'Bosch BME280', 'Giao tiếp' => 'I²C / SPI', 'Đo áp suất' => 'Có', 'Điện áp hoạt động' => '3.3 V'], 'is_rohs' => true],
            ['category' => 'module-cam-bien', 'sku' => 'MOD-MPU6050-GY521', 'mpn' => 'GY-521', 'name' => 'Module gia tốc kế và con quay hồi chuyển MPU-6050', 'brand' => 'Generic', 'price' => 34000, 'stock' => 82, 'specifications' => ['Cảm biến' => 'MPU-6050', 'Trục đo' => '6 trục', 'Giao tiếp' => 'I²C', 'Điện áp hoạt động' => '3.3–5 V'], 'is_rohs' => true],
            ['category' => 'module-cam-bien', 'sku' => 'MOD-BH1750-GY302', 'mpn' => 'GY-302', 'name' => 'Module cảm biến ánh sáng BH1750', 'brand' => 'Generic', 'price' => 19000, 'stock' => 95, 'specifications' => ['Cảm biến' => 'BH1750FVI', 'Dải đo' => '1–65535 lux', 'Giao tiếp' => 'I²C', 'Điện áp hoạt động' => '3–5 V'], 'is_rohs' => true],
            ['category' => 'module-cam-bien', 'sku' => 'MOD-RELAY-5V-1CH', 'mpn' => 'SRD-05VDC-SL-C', 'name' => 'Module rơ-le 1 kênh 5V', 'brand' => 'Songle', 'price' => 22000, 'stock' => 68, 'specifications' => ['Số kênh' => '1', 'Điện áp cuộn hút' => '5 VDC', 'Tiếp điểm tối đa' => '10 A, 250 VAC', 'Cách ly' => 'Optocoupler'], 'is_rohs' => true],
            ['category' => 'board-phat-trien', 'sku' => 'BOARD-RPI-PICO-W', 'mpn' => 'SC0918', 'name' => 'Board Raspberry Pi Pico W', 'brand' => 'Raspberry Pi', 'price' => 145000, 'stock' => 34, 'specifications' => ['MCU' => 'RP2040', 'Wi-Fi' => '2.4 GHz 802.11n', 'Bluetooth' => 'Bluetooth 5.2', 'GPIO' => '26'], 'is_rohs' => true],
            ['category' => 'board-phat-trien', 'sku' => 'BOARD-NANO-CH340', 'mpn' => null, 'name' => 'Board Nano V3 tương thích CH340', 'brand' => 'Generic', 'price' => 98000, 'stock' => 47, 'specifications' => ['MCU' => 'ATmega328P', 'USB-to-Serial' => 'CH340', 'Điện áp hoạt động' => '5 V', 'Digital I/O' => '14'], 'is_rohs' => true, 'is_esd_sensitive' => true],
            ['category' => 'board-phat-trien', 'sku' => 'BOARD-NUCLEO-F401RE', 'mpn' => 'NUCLEO-F401RE', 'name' => 'Board phát triển STM32 Nucleo-F401RE', 'brand' => 'STMicroelectronics', 'price' => 520000, 'stock' => 18, 'specifications' => ['MCU' => 'STM32F401RET6', 'Kiến trúc' => 'ARM Cortex-M4', 'Xung nhịp tối đa' => '84 MHz', 'Kết nối nạp' => 'ST-LINK/V2-1'], 'is_rohs' => true, 'is_esd_sensitive' => true],
            ['category' => 'dung-cu-phu-kien', 'sku' => 'TOOL-ESD-MAT-60X40', 'mpn' => null, 'name' => 'Thảm chống tĩnh điện ESD 60 × 40 cm', 'brand' => 'Proskit', 'price' => 165000, 'stock' => 32, 'specifications' => ['Kích thước' => '600 × 400 mm', 'Chất liệu' => 'Cao su hai lớp', 'Phụ kiện' => 'Dây tiếp đất'], 'is_rohs' => false],
            ['category' => 'dung-cu-phu-kien', 'sku' => 'TOOL-SOLDER-IRON-FX600', 'mpn' => 'FX-600', 'name' => 'Mỏ hàn điều chỉnh nhiệt Hakko FX-600', 'brand' => 'Hakko', 'price' => 450000, 'stock' => 12, 'specifications' => ['Công suất' => '50 W', 'Dải nhiệt độ' => '200–500°C', 'Điện áp' => '220 VAC'], 'is_rohs' => false],
            ['category' => 'dung-cu-phu-kien', 'sku' => 'TOOL-MULTIMETER-DT830B', 'mpn' => 'DT830B', 'name' => 'Đồng hồ vạn năng kỹ thuật số DT830B', 'brand' => 'Generic', 'price' => 85000, 'stock' => 28, 'specifications' => ['Đo điện áp DC' => '200 mV–1000 V', 'Đo điện áp AC' => '200–750 V', 'Đo dòng DC' => '200 µA–10 A', 'Màn hình' => 'LCD'], 'is_rohs' => false],
            ['category' => 'dung-cu-phu-kien', 'sku' => 'TOOL-JUMPER-MM-40P', 'mpn' => null, 'name' => 'Dây jumper đực-đực 40 sợi', 'brand' => 'Generic', 'price' => 12000, 'stock' => 150, 'specifications' => ['Số lượng' => '40 sợi', 'Đầu nối' => 'Đực-đực', 'Chiều dài' => '20 cm', 'Bước chân' => '2.54 mm'], 'is_rohs' => false],
            ['category' => 'dung-cu-phu-kien', 'sku' => 'TOOL-BREADBOARD-830P', 'mpn' => null, 'name' => 'Breadboard 830 điểm cắm', 'brand' => 'Generic', 'price' => 28000, 'stock' => 74, 'specifications' => ['Số điểm' => '830', 'Khoảng cách chân' => '2.54 mm', 'Nguồn cấp' => '2 thanh nguồn', 'Kích thước' => '165 × 55 mm'], 'is_rohs' => false],
            ['category' => 'dung-cu-phu-kien', 'sku' => 'TOOL-WIRE-SET-22AWG', 'mpn' => null, 'name' => 'Bộ dây điện lõi đơn 22 AWG nhiều màu', 'brand' => 'Generic', 'price' => 39000, 'stock' => 58, 'specifications' => ['Kích thước dây' => '22 AWG', 'Số màu' => '6', 'Chiều dài mỗi cuộn' => '7.5 m (25 ft)', 'Lõi' => 'Đồng'], 'is_rohs' => false],
        ];

        $categories = Category::query()
            ->whereIn('slug', collect($products)->pluck('category')->unique())
            ->get()
            ->keyBy('slug');

        foreach ($products as $data) {
            $category = $categories->get($data['category']);

            if (! $category) {
                throw new RuntimeException("Danh mục [{$data['category']}] chưa tồn tại.");
            }

            $product = Product::query()->updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'category_id' => $category->id,
                    'mpn' => $data['mpn'],
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name'].' '.$data['sku']),
                    'brand' => $data['brand'],
                    'description' => 'Dữ liệu mẫu cho '.$data['name'].'. Vui lòng kiểm tra giá và thông số với nhà cung cấp trước khi sử dụng cho giao dịch thực tế.',
                    'specifications' => $data['specifications'],
                    'price' => $data['price'],
                    'stock' => $data['stock'],
                    'reserved_stock' => 0,
                    'minimum_stock' => 5,
                    'moq' => 1,
                    'pack_size' => 1,
                    'is_rohs' => $data['is_rohs'],
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
    }
}
