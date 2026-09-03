<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductImage;
use Illuminate\Support\Facades\Cache;

ProductImage::firstOrCreate(
    ['product_id' => 18, 'image' => 'products/baju ubsi putih.jpg'],
    ['sort_order' => 1]
);
ProductImage::firstOrCreate(
    ['product_id' => 18, 'image' => 'products/baju ubsi kuning.jpg'],
    ['sort_order' => 2]
);
ProductImage::firstOrCreate(
    ['product_id' => 18, 'image' => 'products/baju ubsi hijau.jpg'],
    ['sort_order' => 3]
);

Cache::flush();
echo "Images seeded and cache flushed successfully.\n";
