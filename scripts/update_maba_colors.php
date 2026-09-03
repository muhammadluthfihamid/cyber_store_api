<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

Product::where('is_event_maba', true)->update([
    'maba_color_ganjil' => 'Putih',
    'maba_color_genap' => 'Biru',
]);

Cache::flush();
echo "Updated event maba products with default ganjil=Putih and genap=Biru.\n";
