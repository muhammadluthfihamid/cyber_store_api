<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CustomerAddress;
use App\Models\Expedition;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductReviewReply;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan direktori storage products siap
        Storage::disk('public')->makeDirectory('products');

        // Copy aset gambar lokal ke storage/products jika ada
        $imgDir = public_path('assets/img');
        if (file_exists($imgDir)) {
            $files = scandir($imgDir);
            foreach ($files as $f) {
                if (in_array($f, ['.', '..'])) continue;
                $src = $imgDir . DIRECTORY_SEPARATOR . $f;
                $dest = 'products/' . $f;
                if (is_file($src) && !Storage::disk('public')->exists($dest)) {
                    Storage::disk('public')->put($dest, file_get_contents($src));
                }
            }
        }

        // ============================
        // 1. KATEGORI RESMI
        // ============================
        $categories = [
            'Baju & Apparel' => 'Koleksi pakaian resmi, kaos, jersey, dan apparel eksklusif BSI Cyber Store.',
            'Topi & Aksesoris' => 'Topi snapback, baseball cap, dan aksesoris kepala resmi kampus.',
            'Tumbler & Drinkware' => 'Botol minum tumbler stainless steel, mug, dan perlengkapan minum ramah lingkungan.',
            'Perlengkapan & Bantal' => 'Bantal santai, totebag, dan pernak-pernik resmi universitas.',
        ];

        $categoryModels = [];
        foreach ($categories as $name => $desc) {
            $categoryModels[$name] = Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $desc, 'is_active' => true]
            );
        }

        // Ambil atau buat user pembeli
        $user = User::first() ?? User::factory()->create([
            'name' => 'Alex Cybertron',
            'email' => 'customer@cyberstore.test',
        ]);

        $expedition = Expedition::first() ?? Expedition::create([
            'name' => 'J&T Express',
            'code' => 'jnt',
            'service' => 'EZ',
            'base_cost' => 14000,
            'estimated_days' => 2,
            'is_active' => true,
        ]);

        $address = CustomerAddress::firstOrCreate(
            ['user_id' => $user->id],
            [
                'label' => 'Rumah',
                'receiver_name' => $user->name,
                'phone' => '081234567890',
                'address' => 'Jl. Cybertech No. 88',
                'province' => 'DKI Jakarta',
                'city' => 'Jakarta Selatan',
                'district' => 'Kebayoran Baru',
                'village' => 'Senayan',
                'postal_code' => '12345',
                'is_default' => true,
            ]
        );

        // ============================
        // 2. DATA PRODUK RESMI LARAVEL
        // ============================
        $productsData = [
            [
                'category' => 'Baju & Apparel',
                'name' => 'Kaos BSI Cyber Elite Hijau Tosca',
                'sku' => 'TS-BSI-GRN',
                'price' => 95000,
                'original_price' => 125000,
                'stock' => 50,
                'weight' => 250,
                'is_recommended' => true,
                'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
                'colors' => ['Hijau Tosca', 'Hitam'],
                'description' => "Kaos resmi edisi khusus BSI Cyber Store dengan material Cotton Combed 30s premium ultra-soft. Nyaman dipakai seharian untuk kuliah, nongkrong, atau kegiatan harian kampus.\n\nSablon Discharge elastis tahan cuci berulang kali.",
                'size_guide' => 'S: LD 96cm, P 68cm | M: LD 100cm, P 70cm | L: LD 104cm, P 72cm | XL: LD 108cm, P 74cm | XXL: LD 112cm, P 76cm.',
                'main_photo' => 'products/baju ubsi hijau.jpg',
                'reviews' => [
                    ['rating' => 5, 'comment' => 'Bahannya adem banget dan sablonnya sangat rapi. Recommended!'],
                    ['rating' => 5, 'comment' => 'Ukuran pas dan warnanya bagus persis di foto.'],
                ]
            ],
            [
                'category' => 'Baju & Apparel',
                'name' => 'Kaos BSI Kampus Merdeka Kuning Emas',
                'sku' => 'TS-BSI-YLW',
                'price' => 95000,
                'original_price' => 120000,
                'stock' => 45,
                'weight' => 250,
                'is_recommended' => true,
                'sizes' => ['S', 'M', 'L', 'XL'],
                'colors' => ['Kuning Emas', 'Putih'],
                'description' => "Kaos official edisi Merdeka Belajar dengan warna cerah khas almamater. Dibuat dengan jahitan rantai rapi dan bahan katun combed yang menyerap keringat dengan baik.",
                'size_guide' => 'S: LD 96cm | M: LD 100cm | L: LD 104cm | XL: LD 108cm.',
                'main_photo' => 'products/baju ubsi kuning.jpg',
                'reviews' => [
                    ['rating' => 5, 'comment' => 'Keren banget buat acara kampus!'],
                ]
            ],
            [
                'category' => 'Baju & Apparel',
                'name' => 'Kaos Polo BSI Cyber Exclusive Putih',
                'sku' => 'TS-BSI-WHT',
                'price' => 110000,
                'original_price' => 145000,
                'stock' => 30,
                'weight' => 280,
                'is_recommended' => true,
                'sizes' => ['M', 'L', 'XL'],
                'colors' => ['Putih Bersih', 'Navy'],
                'description' => "Polo shirt berkerah dengan bordir emblem logo resmi Cyber Store. Memberikan kesan rapi, profesional, dan tetap kasual untuk kegiatan akademik maupun presentasi proyek.",
                'size_guide' => 'M: LD 102cm, P 71cm | L: LD 106cm, P 73cm | XL: LD 110cm, P 75cm.',
                'main_photo' => 'products/baju ubsi putih.jpg',
                'reviews' => [
                    ['rating' => 5, 'comment' => 'Bordirannya sangat rapi, kerahnya tegak dan elegan.'],
                ]
            ],
            [
                'category' => 'Topi & Aksesoris',
                'name' => 'Topi Snapback BSI Official Merah Marun',
                'sku' => 'CP-BSI-RED',
                'price' => 65000,
                'original_price' => 85000,
                'stock' => 35,
                'weight' => 150,
                'is_recommended' => true,
                'sizes' => ['All Size Adjustable'],
                'colors' => ['Merah Marun', 'Hitam'],
                'description' => "Topi Snapback dengan bordir 3D logo almamater di bagian depan. Menggunakan bahan rafel katun berkualitas tinggi dengan strap belakang adjustable yang mudah disesuaikan.",
                'size_guide' => 'Lingkar kepala 54 - 60 cm (Strap pengatur di bagian belakang).',
                'main_photo' => 'products/topi ubsi merah.jpg',
                'reviews' => [
                    ['rating' => 5, 'comment' => 'Bordir 3D-nya tebal dan bagus banget.'],
                ]
            ],
            [
                'category' => 'Topi & Aksesoris',
                'name' => 'Topi Baseball BSI Signature Kuning',
                'sku' => 'CP-BSI-YLW',
                'price' => 65000,
                'original_price' => 85000,
                'stock' => 40,
                'weight' => 150,
                'is_recommended' => false,
                'sizes' => ['All Size Adjustable'],
                'colors' => ['Kuning Almamater'],
                'description' => "Topi baseball bergaya kasual dengan visor lengkung yang melindungi wajah dari sinar matahari saat kegiatan outdoor di kampus.",
                'size_guide' => 'Lingkar kepala 54 - 60 cm (Adjustable buckle metal).',
                'main_photo' => 'products/topi ubsi kuning.jpg',
                'reviews' => [
                    ['rating' => 5, 'comment' => 'Pas di kepala dan bahannya lembut.'],
                ]
            ],
            [
                'category' => 'Topi & Aksesoris',
                'name' => 'Topi Trucker BSI Casual Edition Putih',
                'sku' => 'CP-BSI-WHT',
                'price' => 60000,
                'original_price' => 80000,
                'stock' => 30,
                'weight' => 130,
                'is_recommended' => false,
                'sizes' => ['All Size Adjustable'],
                'colors' => ['Putih - Biru'],
                'description' => "Topi model trucker dengan jaring sirkulasi udara di bagian belakang, memastikan kepala tetap sejuk dan tidak gerah saat beraktivitas.",
                'size_guide' => 'Lingkar kepala 55 - 61 cm.',
                'main_photo' => 'products/topi ubsi putih.jpg',
                'reviews' => [
                    ['rating' => 5, 'comment' => 'Sangat nyaman dipakai siang hari.'],
                ]
            ],
            [
                'category' => 'Tumbler & Drinkware',
                'name' => 'Tumbler Termos BSI Vacuum Insulated Hitam',
                'sku' => 'TM-BSI-BLK',
                'price' => 85000,
                'original_price' => 115000,
                'stock' => 60,
                'weight' => 380,
                'is_recommended' => true,
                'sizes' => ['500 ml'],
                'colors' => ['Matte Black', 'Silver'],
                'description' => "Tumbler stainless steel SUS 304 dengan teknologi insulasi ganda (Double Wall Vacuum). Mampu menahan suhu air panas hingga 12 jam dan air dingin hingga 24 jam.\n\nDilengkapi tutup anti tumpah dan filter teh.",
                'size_guide' => 'Kapasitas: 500ml | Tinggi: 22.5cm | Diameter: 6.5cm.',
                'main_photo' => 'products/tumbler ubsi hitam.jpg',
                'reviews' => [
                    ['rating' => 5, 'comment' => 'Tahan dingin seharian dari pagi sampai sore! Keren ada logonya.'],
                ]
            ],
            [
                'category' => 'Tumbler & Drinkware',
                'name' => 'Tumbler Sport BSI Cyber Biru Elektrik',
                'sku' => 'TM-BSI-BLU',
                'price' => 85000,
                'original_price' => 115000,
                'stock' => 50,
                'weight' => 380,
                'is_recommended' => true,
                'sizes' => ['500 ml'],
                'colors' => ['Biru Elektrik'],
                'description' => "Tumbler elegan dengan sentuhan finishing warna metalik biru. Bebas BPA (BPA Free), ramah lingkungan, dan cocok dibawa saat kuliah dan olahraga.",
                'size_guide' => 'Kapasitas: 500ml | Tinggi: 22.5cm | Diameter: 6.5cm.',
                'main_photo' => 'products/tumbler ubsi biru.jpg',
                'reviews' => [
                    ['rating' => 5, 'comment' => 'Warnanya cakep banget!'],
                ]
            ],
            [
                'category' => 'Perlengkapan & Bantal',
                'name' => 'Bantal Leher & Tidur BSI Cyber Ergonomic',
                'sku' => 'PL-BSI-PLW',
                'price' => 75000,
                'original_price' => 95000,
                'stock' => 30,
                'weight' => 400,
                'is_recommended' => false,
                'sizes' => ['Standard 40x40 cm'],
                'colors' => ['Navy Blue'],
                'description' => "Bantal empuk dengan isian dakron silikon grade A dan cover kain velboa yang super halus. Nyaman untuk menemani waktu istirahat di kosan atau perjalanan.",
                'size_guide' => 'Dimensi: 40 x 40 cm. Berat: 400 gram.',
                'main_photo' => 'products/bantal ubsi.jpg',
                'reviews' => [
                    ['rating' => 5, 'comment' => 'Empuk banget dan kainnya halus.'],
                ]
            ],
        ];

        // ============================
        // 3. SEEDING KE DATABASE
        // ============================
        foreach ($productsData as $data) {
            $catModel = $categoryModels[$data['category']] ?? Category::first();

            $product = Product::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'category_id' => $catModel->id,
                    'name' => $data['name'],
                    'sku' => $data['sku'],
                    'price' => $data['price'],
                    'original_price' => $data['original_price'],
                    'stock' => $data['stock'],
                    'weight' => $data['weight'],
                    'is_active' => true,
                    'is_recommended' => $data['is_recommended'],
                    'sizes' => $data['sizes'],
                    'colors' => $data['colors'],
                    'description' => $data['description'],
                    'size_guide' => $data['size_guide'],
                    'main_photo' => $data['main_photo'],
                ]
            );

            // Tambahkan foto ke relasi images jika belum ada
            $product->images()->firstOrCreate(
                ['image' => $data['main_photo']],
                ['sort_order' => 1]
            );

            // Buat Dummy Order untuk ulasan
            $order = Order::firstOrCreate(
                ['user_id' => $user->id, 'invoice_number' => 'INV-DEMO-' . $product->id],
                [
                    'customer_address_id' => $address->id,
                    'expedition_id' => $expedition->id,
                    'status' => 'completed',
                    'subtotal' => $product->price,
                    'shipping_cost' => $expedition->base_cost,
                    'grand_total' => $product->price + $expedition->base_cost,
                    'resi_number' => 'JT' . rand(100000000, 999999999),
                ]
            );

            OrderItem::firstOrCreate(
                ['order_id' => $order->id, 'product_id' => $product->id],
                [
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => 1,
                    'size' => $data['sizes'][0] ?? null,
                    'color' => $data['colors'][0] ?? null,
                    'total' => $product->price,
                ]
            );

            // Reviews
            foreach ($data['reviews'] as $revData) {
                $review = ProductReview::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                    ],
                    [
                        'rating' => $revData['rating'],
                        'comment' => $revData['comment'],
                        'reply' => 'Terima kasih telah berbelanja merchandise resmi di Cyber Store! Semoga produk bermanfaat. Salam hangat dari tim kami 🙏',
                        'is_read' => true,
                    ]
                );
            }
        }

        Product::clearCache();
        $this->command->info('✅ Berhasil menyematkan produk lokal resmi Laravel ke database!');
    }
}
