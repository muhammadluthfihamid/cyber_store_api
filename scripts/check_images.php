<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = App\Models\Product::where('name', 'like', '%BSI%')->get();
foreach ($products as $p) {
    echo "ID: {$p->id} | Name: {$p->name} | Price: {$p->price} | Colors: " . json_encode($p->colors) . " | Event: " . ($p->is_event_maba ? 'YES' : 'NO') . PHP_EOL;
}
