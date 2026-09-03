<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Buat / update Kaos Khusus Ormik & Semot
$ormikProduct = App\Models\Product::updateOrCreate(
    ['slug' => 'kaos-resmi-ormik-semot-ubsi-2026'],
    [
        'category_id'    => 1, // Baju & Apparel
        'name'           => 'Kaos Resmi Mahasiswa Baru Ormik & Semot UBSI 2026',
        'sku'            => 'ORM-BSI-2026',
        'price'          => 95000,
        'original_price' => 125000,
        'stock'          => 150,
        'weight'         => 250,
        'is_active'      => true,
        'is_recommended' => true,
        'is_event_maba'  => true,
        'sizes'          => ['S', 'M', 'L', 'XL', 'XXL'],
        'colors'         => ['Putih', 'Biru'],
        'main_photo'     => 'products/baju ubsi putih.jpg',
        'description'    => "Kaos resmi seragam kegiatan ORMIK (Orientasi Akademik) dan SEMOT (Seminar Motivasi) Universitas BSI 2026.\n\nMaterial Cotton Combed 30s premium ultra-soft dengan sablon emblem resmi kampus. Nyaman digunakan seharian selama kegiatan orientasi.\n\n⚠️ KETENTUAN WARNA KHUSUS:\nSesuai aturan panitia, warna baju wajib mengikuti digit terakhir NIM Anda:\n- Digit Terakhir Ganjil (1, 3, 5, 7, 9) = Warna Putih\n- Digit Terakhir Genap (0, 2, 4, 6, 8) = Warna Biru",
        'size_guide'     => 'S: LD 96cm, P 68cm | M: LD 100cm, P 70cm | L: LD 104cm, P 72cm | XL: LD 108cm, P 74cm | XXL: LD 112cm, P 76cm.',
        'rating'         => 5.0,
        'reviews_count'  => 12,
    ]
);

// 2. Tandai juga produk pelengkap maba lainnya
$polo = App\Models\Product::find(11);
if ($polo) {
    $polo->update(['is_event_maba' => true, 'colors' => ['Putih', 'Biru']]);
}

$topi = App\Models\Product::find(14);
if ($topi) {
    $topi->update(['is_event_maba' => true]);
}

echo "Ormik Product ID: {$ormikProduct->id}" . PHP_EOL;
echo "Total Event Maba Products: " . App\Models\Product::where('is_event_maba', true)->count() . PHP_EOL;
