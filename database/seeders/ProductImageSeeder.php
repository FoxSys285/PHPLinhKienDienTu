<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            'R-10K-025W' => 'https://snapeda.s3.amazonaws.com/partpics/Yageo_CFR-25JB-52-10K_83479.jpg',
            'R-330R-0603' => 'https://media.rs-online.com/R1991564-01.jpg',
            'C-100U-25V' => 'https://media.rs-online.com/F7396812-01.jpg',
            'C-100N-50V-0805' => 'https://www.mini-tech.com.ua/image/cache/catalog/radiodetali/cap_smd_0805-550x550.jpg',
            'IC-NE555P' => 'https://robocraze.com/cdn/shop/products/wfx_5.jpg?v=1743775788',
            'MOS-IRLZ44N' => 'https://myproject.com.ua/image/catalog/Tranzistor/5576/5576_1.png',
            'MCU-ATMEGA328P-PU' => 'https://www.rhydolabz.com/image/cache/catalog/MIC0041_2-1000x1000.jpg',
            'MOD-DHT22' => 'https://diyables.io/images/products/dht22-module-2.jpg',
            'MOD-ESP32-WROOM' => 'https://empa.witcdn.net/esp32-wroom-32e-n4-en-wi-fi-modules-espressif-20279-14-B.jpg',
            'BOARD-ARDUINO-UNO-R3' => 'https://www.makerlab.ph/cdn/shop/files/COM00004.jpg?v=1752457069',
            'BOARD-ESP32-DEVKIT' => 'https://funduinoshop.com/media/image/93/5c/14/Espressif-ESP32-Entwicklerplatine-DevKitC-v4-WROOM-32-E-4MB_600x600.png',
            'TOOL-ESD-TWEEZER' => 'https://shop.prokits.com.tw/photo2011/prod_2023/10/TZ-205N.jpg',
            'RES-1K-0603' => 'https://images.tcdn.com.br/img/img_prod/1077281/resistor_smd_0603_1k_ohm_1_rc0603fr_071kl_yageo_1628_1_73b5318dbf21aba720ef71e7ef8bd5c1.jpg',
            'RES-4K7-025W' => 'https://res.cloudinary.com/rsc/image/upload/w_1024/R1991793-01',
            'RES-10K-0805' => 'https://q68yavdponaw8brm.public.blob.vercel-storage.com/uploads/products/rc0805fr-0710kl/0.jpg',
            'CAP-10U-25V-0805' => 'https://mm.digikey.com/Volume0/opasdata/d220001/medias/images/2537/Ceramic-Capacitor-CL-Series.jpg',
            'CAP-1U-50V-0603' => 'https://mm.digikey.com/Volume0/opasdata/d220001/medias/images/2537/Ceramic-Capacitor-CL-Series.jpg',
            'CAP-470U-16V-RAD' => 'https://mm.digikey.com/Volume0/opasdata/d220001/medias/images/2461/FC%20SERIES%2012.5%2C10.jpg?hidebanner=true',
            'IC-LM358N-DIP8' => 'https://images.tcdn.com.br/img/img_prod/648216/kit_250_pecas_circuito_integrado_amplificador_operacional_lm358n_dip_8_pth_st_original_236054_3_a5978b1ccc41208cba3366f8b09884e1.jpg',
            'REG-LM7805-TO220' => 'https://oskolchip.ru/_sh/2/222.webp',
            'DIODE-1N4007-DO41' => 'https://media.rs-online.com/image/upload/bo_1.5px_solid_white%2Cb_auto%2Cc_pad%2Cdpr_2%2Cf_auto%2Ch_399%2Cq_auto%2Cw_710/c_pad%2Ch_399%2Cw_710/F6289546-01?pgw=1',
            'MCU-STM32F103C8T6' => 'https://bizweb.dktcdn.net/100/408/906/products/vi-dieu-khien-stm32-f103c8t6-1.jpg?v=1615229019000',
            'MCU-ATTINY85-PU' => 'https://media.rs-online.com/F1331672-01.jpg',
            'MCU-ESP32-C3-MINI-1' => 'https://robot-italy.com/cdn/shop/files/DFR0809_900x600.jpg?v=1704742660',
            'MOD-BME280-I2C' => 'https://thepihut.com/cdn/shop/products/bme280-environmental-sensor-waveshare-wav-15231-22853122195651.jpg?v=1646625075&width=640',
            'MOD-MPU6050-GY521' => 'https://bizweb.dktcdn.net/100/408/906/products/93.jpg?v=1649995164230',
            'MOD-BH1750-GY302' => 'https://ae01.alicdn.com/kf/Se69f64f63e724f5791acb5c07c4ae34cc.jpg',
            'MOD-RELAY-5V-1CH' => 'https://i.ebayimg.com/images/g/mY0AAOSwu1BeWNhC/s-l1200.jpg',
            'BOARD-RPI-PICO-W' => 'https://iarduino.ru/img/catalog/1429c5c16dac346baf1e922e838537d3.jpg',
            'BOARD-NANO-CH340' => 'https://solectroshop.com/1030-large_default/nano-v30-ch340-placa-compativel-arduino.jpg',
            'BOARD-NUCLEO-F401RE' => 'https://vishaworld.com/cdn/shop/files/STM32F401RE_NUCLEO_Development_Board_1.jpg?v=1768631809&width=2000',
            'TOOL-ESD-MAT-60X40' => 'https://images.satu.kz/207337941_w640_h640_proskit-8bm-405a-antistaticheskij.jpg',
            'TOOL-SOLDER-IRON-FX600' => 'https://www.synergyelectronics.co.nz/store/image/cache/catalog/products/hakko/stations/FX600_L-600x600.jpg',
            'TOOL-MULTIMETER-DT830B' => 'https://www.eelectronicparts.com/cdn/shop/products/s-l1600_0c726b5a-1ad3-4f39-874a-38d7cde829e7_1024x1024.jpg?v=1579718508',
            'TOOL-JUMPER-MM-40P' => 'https://anuelectronics.com/cdn/shop/files/Jumper_Wire_Male_to_Male_v2.png?crop=center&height=700&v=1781930598&width=700',
            'TOOL-BREADBOARD-830P' => 'https://epartners.co.nz/cdn/shop/files/breadboard-830-point-solderless-universal-prototyping-board-659888.jpg?v=1726481464',
            'TOOL-WIRE-SET-22AWG' => 'https://cdn-blog.adafruit.com/uploads/2013/03/1311_LRG-1.jpg',
        ];

        foreach ($images as $sku => $imageUrl) {
            Product::query()->where('sku', $sku)->update(['image_url' => $imageUrl]);
        }
    }
}
