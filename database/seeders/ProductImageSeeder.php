<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            'R-10K-025W' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988400/linhkien/products/import-0801515ad36777804b2bcbd745386b16bc550def42bef5b0a8bf1f573951bebb.jpg',
            'R-330R-0603' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988402/linhkien/products/import-a02afbdb41d7a5b0125ad684d5f2ee4417f09be36732480f35f8dc00cd20e1d6.jpg',
            'C-100U-25V' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988403/linhkien/products/import-00daf96975fe56c2791be229679929ead2096073cc1d579ca49b2ae1f2655c8b.jpg',
            'C-100N-50V-0805' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988406/linhkien/products/import-fd89de85c47f597701eaa78e707c6d27fc2145d54ceb7af0450d1851fe5b9dc0.jpg',
            'IC-NE555P' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988407/linhkien/products/import-1f0ba10a30937b5e14fd7ef510a6c0f8d91ccf1f692ff3d18813765e175faf97.jpg',
            'MOS-IRLZ44N' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988409/linhkien/products/import-e027988d119db51473955afd5d95fbc9893eab1217cad1afdc3e2c1ddfcd1158.png',
            'MCU-ATMEGA328P-PU' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988412/linhkien/products/import-e86362e615d4aa1b52821fcedbfd8b3d2df4fe8e8b0c85c7204317bf40e453d8.jpg',
            'MOD-DHT22' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988414/linhkien/products/import-59258e388388ca07b160cf5105dba3bd59cb3f49a44514fbb7f8ddeb568acffd.jpg',
            'MOD-ESP32-WROOM' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988416/linhkien/products/import-8454da43f90086b1af4e6d52c319bd9e679784c2afa62041b795d6ce492a6be3.jpg',
            'BOARD-ARDUINO-UNO-R3' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988418/linhkien/products/import-f2341b7bb098bd6dae38fe6e0b26bc147b8b7a6eca2a009ec830567e09186877.jpg',
            'BOARD-ESP32-DEVKIT' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988421/linhkien/products/import-c10d1e482e170f475e044ec2e6dddddebb6f251936ec1795cf08df1dfb00a66c.png',
            'TOOL-ESD-TWEEZER' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988424/linhkien/products/import-2b4c342408209089d169fd8f22f7e150bb4c53aef86b5da24eb2df4e229c37b1.jpg',
            'RES-1K-0603' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988425/linhkien/products/import-ea1c2e04cf670bbfd683c805ce7a1eafd874671e131a94e108a7e8ba85151fbe.jpg',
            'RES-4K7-025W' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988427/linhkien/products/import-eed47f53dbff2e117436dc40f539d5d6859b5171148a49622dfc4926cbbea196.jpg',
            'RES-10K-0805' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988429/linhkien/products/import-95ee34fac7a7e637d4c708ff1b9887fcec8f02b270a53fe960ecd8b27fbf4645.jpg',
            'CAP-10U-25V-0805' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988430/linhkien/products/import-26ac84ad8bfa381ce09753e7e0df0d77574bac2dde119f903d01729e0cc6581c.jpg',
            'CAP-1U-50V-0603' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988430/linhkien/products/import-26ac84ad8bfa381ce09753e7e0df0d77574bac2dde119f903d01729e0cc6581c.jpg',
            'CAP-470U-16V-RAD' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988431/linhkien/products/import-0abb2eb9fd9777ad7a060067b25e9382c2fc07da704f30f3872f719b0dbfc1c2.jpg',
            'IC-LM358N-DIP8' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988433/linhkien/products/import-db20d169b2d02a5fdd40e1f57ba2703e9a1874cce7312f483bfa06cff1ce8432.jpg',
            'REG-LM7805-TO220' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988435/linhkien/products/import-39c369969faf7a77f54b175e833f1532d421149f8d9817409b751750a13ea234.webp',
            'DIODE-1N4007-DO41' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988436/linhkien/products/import-b380c8f28f1692d7a3a643323fdd1c89a9cdc3ab026693ea673b369985a66f35.jpg',
            'MCU-STM32F103C8T6' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988441/linhkien/products/import-5f763ca0c5edb48cd6cb47dc0a1a751e9a562a38bbbf21a26f9c10faf179d8f5.jpg',
            'MCU-ATTINY85-PU' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988442/linhkien/products/import-149df13b50ec6939cb801a330b62fbb9c009747633ceb7b573115df44875e0f6.jpg',
            'MCU-ESP32-C3-MINI-1' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988444/linhkien/products/import-9961a629a037d1b47498a6cb05c486040b541a2727e43741b3fb10bbccac361e.jpg',
            'MOD-BME280-I2C' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988445/linhkien/products/import-f8c7d23ec436849310a0fde8ca8ff02498564adf3c69981ea82ca7e6157661e6.jpg',
            'MOD-MPU6050-GY521' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988449/linhkien/products/import-b9f7b5dac99176a6e56260cb25f43584eff3bd44cf6750a7e0ed6bdeb79d7393.jpg',
            'MOD-BH1750-GY302' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988450/linhkien/products/import-e8efb794fccd97133a5a7f944354f2e56ab23aebae6ee215443ec9512d35cceb.jpg',
            'MOD-RELAY-5V-1CH' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988452/linhkien/products/import-35cdc17b113e0ed448789049ea6e47776eff8323f003a216913a39ec414df158.jpg',
            'BOARD-RPI-PICO-W' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988455/linhkien/products/import-e6ee18b9a1d3f2f24aefecc269ec26d89330b7c04efbc50180cde6a7f5406512.jpg',
            'BOARD-NANO-CH340' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988457/linhkien/products/import-0f62e26284679977554b18aac5968ff24a8beacdef5133bfeb8bb67ef8b19665.jpg',
            'BOARD-NUCLEO-F401RE' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988459/linhkien/products/import-86143f7691d41c74d9cfc0f3e70a3c2ac52377e3eedae186d8f0c305dbebb188.jpg',
            'TOOL-ESD-MAT-60X40' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988462/linhkien/products/import-684c1c233ed9785237d1feffd9e316480c96554c5e38f69afac5fb085b6b0d1e.jpg',
            'TOOL-SOLDER-IRON-FX600' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988464/linhkien/products/import-b6a138102b706e2ab9ac5447f01288bd4f1103643b85f0e01195556a80c66781.jpg',
            'TOOL-MULTIMETER-DT830B' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988466/linhkien/products/import-e06e537d47c01336fc5c51a33d18215a433aba3b5b7f3b1eba6e38b5b8cc91a8.jpg',
            'TOOL-JUMPER-MM-40P' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988468/linhkien/products/import-a6061daea05fd302426df2b638e48acb2af8568e9877d2976de7f044f29cb5aa.png',
            'TOOL-BREADBOARD-830P' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988470/linhkien/products/import-5ecb28c15ec326412c4c2ed3764408aa31ba1098d37641300c6108114c520fe5.jpg',
            'TOOL-WIRE-SET-22AWG' => 'https://res.cloudinary.com/towpghmp/image/upload/v1794988471/linhkien/products/import-48f5b75ab4b664b6fc1e8d13cdbe1b54fd8da6a9267031f739faaeab512dcd08.jpg',
        ];

        foreach ($images as $sku => $imageUrl) {
            Product::query()->where('sku', $sku)->update(['image_url' => $imageUrl]);
        }
    }
}
