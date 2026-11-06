<?php

return [
    'standard_shipping_fee' => (float) env('STORE_SHIPPING_FEE', 30000),
    'free_shipping_threshold' => (float) env('STORE_FREE_SHIPPING_THRESHOLD', 500000),
    'vat_rate' => (float) env('STORE_VAT_RATE', 0),
];
