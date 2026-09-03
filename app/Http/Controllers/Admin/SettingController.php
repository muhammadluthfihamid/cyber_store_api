<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class SettingController extends Controller
{
    public function index()
    {
        $storeName = Setting::get('store_name', 'BSI Cyber Store');
        $storeAddress = Setting::get('store_address', 'jl. Dewi Sartika Blok, Jl. H. Abdul Hamid No.77, RT.8/RW.4, Cawang, Kec. Kramat jati, Kota Jakarta Timur, Daerah Khusus Ibukota Jakarta 13630');
        $storeCityId = Setting::get('store_city_id', '152');
        $storeEmail = Setting::get('store_email', 'support@bsi.ac.id');
        $storePhone = Setting::get('store_phone', '(021) 7867868');

        $cities = [];
        $citiesPath = database_path('data/rajaongkir_cities.json');
        if (File::exists($citiesPath)) {
            $cities = json_decode(File::get($citiesPath), true);
        }

        $helpWhatsapp = Setting::get('help_whatsapp', '628123456789');
        $helpEmail = Setting::get('help_email', 'cs@cyberstore.id');
        $helpPhone = Setting::get('help_phone', '(021) 7867868');

        $defaultFaqs = [
            [
                'question' => 'Bagaimana cara mendaftar akun?',
                'answer' => 'Anda dapat mendaftar melalui Halaman Register dengan mengisi nama lengkap, email, nomor telepon, dan membuat kata sandi baru.',
            ],
            [
                'question' => 'Apakah pembelian merchandise dapat dikirim ke luar kota?',
                'answer' => 'Ya, kami bekerjasama dengan ekspedisi resmi (JNE, J&T, SiCepat, Anteraja) untuk pengiriman ke seluruh wilayah Indonesia.',
            ],
            [
                'question' => 'Bagaimana cara melacak pesanan saya?',
                'answer' => 'Anda dapat melihat status pelacakan pesanan Anda di tab "Riwayat Transaksi" dan memilih pesanan yang ingin dilacak.',
            ],
            [
                'question' => 'Bagaimana jika barang yang saya terima cacat/tidak sesuai?',
                'answer' => 'Silakan hubungi customer service kami melalui kontak WhatsApp/Email yang tersedia di menu bantuan dengan melampirkan video unboxing.',
            ],
        ];

        $faqsRaw = Setting::get('help_faqs');
        $helpFaqs = !empty($faqsRaw) ? json_decode($faqsRaw, true) : $defaultFaqs;

        $defaultGuides = [
            [
                'title' => 'Cara Berbelanja & Checkout',
                'description' => 'Langkah mudah memilih produk hingga menyelesaikan pesanan Anda.',
                'image' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=600&auto=format&fit=crop&q=80',
                'steps' => [
                    ['title' => 'Cari & Pilih Produk', 'desc' => 'Jelajahi kategori di Beranda atau gunakan fitur pencarian untuk menemukan produk yang Anda inginkan.'],
                    ['title' => 'Pilih Ukuran & Warna', 'desc' => 'Buka halaman detail produk, lalu pilih varian ukuran dan warna sesuai kebutuhan Anda.'],
                    ['title' => 'Tambah ke Keranjang', 'desc' => 'Tekan "+ Keranjang" untuk menyimpan produk, atau tekan "Beli Sekarang" untuk langsung menuju checkout.'],
                    ['title' => 'Isi Alamat & Ekspedisi', 'desc' => 'Pilih alamat pengiriman dan jasa kurir ekspedisi yang tersedia.'],
                    ['title' => 'Selesaikan Pembayaran', 'desc' => 'Pilih metode pembayaran (VA / QRIS / Transfer), lalu tekan "Bayar Sekarang".'],
                ],
            ],
            [
                'title' => 'Metode Pembayaran',
                'description' => 'Panduan melakukan pembayaran pesanan secara aman dan cepat.',
                'image' => 'https://images.unsplash.com/photo-1556742049-0a67daf4005a?w=600&auto=format&fit=crop&q=80',
                'steps' => [
                    ['title' => 'Virtual Account (VA)', 'desc' => 'Salin nomor Virtual Account yang tampil di layar, lalu bayar melalui m-Banking atau ATM Bank BCA, Mandiri, BNI, BRI, atau Permata.'],
                    ['title' => 'QRIS (Gopay/OVO/Dana/ShopeePay)', 'desc' => 'Pilih QRIS di layar pembayaran Midtrans, lalu scan kode QR menggunakan aplikasi e-wallet pilihan Anda.'],
                    ['title' => 'Waktu Pembayaran', 'desc' => 'Setiap batas pembayaran berlaku 24 jam. Pesanan akan otomatis dikonfirmasi setelah pembayaran berhasil.'],
                ],
            ],
            [
                'title' => 'Lacak Pesanan & Nomor Resi',
                'description' => 'Memantau pengiriman barang dan melacak nomor resi kurir.',
                'image' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=600&auto=format&fit=crop&q=80',
                'steps' => [
                    ['title' => 'Buka Menu Pesanan Saya', 'desc' => 'Masuk ke menu Profil > Pesanan Saya untuk melihat daftar transaksi Anda.'],
                    ['title' => 'Cek Detail Pesanan', 'desc' => 'Pilih pesanan berstatus "Dikirim" untuk melihat nomor resi dan rincian pengiriman.'],
                    ['title' => 'Konfirmasi Penerimaan', 'desc' => 'Setelah paket tiba di lokasi Anda, tekan tombol "Pesanan Diterima" untuk menyelesaikan transaksi.'],
                ],
            ],
            [
                'title' => 'Layanan Chat & Ulasan',
                'description' => 'Hubungi Admin CS dan berikan ulasan produk.',
                'image' => 'https://images.unsplash.com/photo-1534536281715-e28d76689b4d?w=600&auto=format&fit=crop&q=80',
                'steps' => [
                    ['title' => 'Tanya Produk / Tanya Stok', 'desc' => 'Buka halaman detail produk, lalu tekan ikon Chat untuk bertanya kepada Admin mengenai stok atau spesifikasi.'],
                    ['title' => 'Pusat Bantuan Live Chat', 'desc' => 'Masuk ke Profil > Pusat Bantuan > Buka Chat Admin untuk terhubung langsung dengan Customer Service.'],
                    ['title' => 'Berikan Ulasan & Rating', 'desc' => 'Setelah pesanan selesai, Anda dapat memberikan ulasan bintang dan foto pada halaman detail pesanan.'],
                ],
            ],
            [
                'title' => 'Kelola Akun & Keamanan',
                'description' => 'Mengubah profil, alamat pengiriman, dan kata sandi.',
                'image' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=600&auto=format&fit=crop&q=80',
                'steps' => [
                    ['title' => 'Ubah Profil & Foto', 'desc' => 'Masuk ke Profil > Edit Profil untuk mengunggah foto profil baru, nama, atau nomor telepon.'],
                    ['title' => 'Kelola Alamat', 'desc' => 'Tambah dan atur alamat utama pengiriman Anda agar mempermudah proses checkout selanjutnya.'],
                    ['title' => 'Ubah Kata Sandi', 'desc' => 'Gunakan fitur Ubah Password di menu Pengaturan untuk menjaga keamanan akun Anda.'],
                ],
            ],
        ];

        $guidesRaw = Setting::get('help_guide_sections');
        $helpGuides = !empty($guidesRaw) ? json_decode($guidesRaw, true) : $defaultGuides;

        $announcementIsActive = (bool) Setting::get('top_announcement_is_active', true);
        $announcementBadge = Setting::get('top_announcement_badge', 'BSI Cyber Store Official');
        $announcementText = Setting::get('top_announcement_text', '🔥 Diskon Hingga 50% untuk Semua Gadget & Aksesoris Gaming! Gunakan Kode: CYBER2026');
        $announcementInfo = Setting::get('top_announcement_info', '⚡ Garansi Resmi 100%');
        $announcementLink = Setting::get('top_announcement_link', '');

        return view('admin.settings.index', compact(
            'storeName', 'storeAddress', 'storeCityId', 'storeEmail', 'storePhone', 'cities',
            'helpWhatsapp', 'helpEmail', 'helpPhone', 'helpFaqs', 'helpGuides',
            'announcementIsActive', 'announcementBadge', 'announcementText', 'announcementInfo', 'announcementLink'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'store_name' => ['required', 'string', 'max:200'],
            'store_address' => ['required', 'string'],
            'store_city_id' => ['required', 'integer'],
            'store_email' => ['required', 'email', 'max:200'],
            'store_phone' => ['required', 'string', 'max:50'],
            'store_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'top_announcement_badge' => ['nullable', 'string', 'max:100'],
            'top_announcement_text' => ['nullable', 'string', 'max:500'],
            'top_announcement_info' => ['nullable', 'string', 'max:100'],
            'top_announcement_link' => ['nullable', 'string', 'max:255'],
            'help_whatsapp' => ['nullable', 'string', 'max:50'],
            'help_email' => ['nullable', 'email', 'max:200'],
            'help_phone' => ['nullable', 'string', 'max:50'],
            'faqs' => ['nullable', 'array'],
            'faqs.*.question' => ['nullable', 'string'],
            'faqs.*.answer' => ['nullable', 'string'],
            'guides' => ['nullable', 'array'],
        ]);

        Setting::set('store_name', $request->store_name);
        Setting::set('store_address', $request->store_address);
        Setting::set('store_city_id', $request->store_city_id);
        Setting::set('store_email', $request->store_email);
        Setting::set('store_phone', $request->store_phone);

        // Announcement Bar Settings
        Setting::set('top_announcement_is_active', $request->has('top_announcement_is_active') ? '1' : '0');
        Setting::set('top_announcement_badge', $request->input('top_announcement_badge', 'BSI Cyber Store Official'));
        Setting::set('top_announcement_text', $request->input('top_announcement_text', ''));
        Setting::set('top_announcement_info', $request->input('top_announcement_info', '⚡ Garansi Resmi 100%'));
        Setting::set('top_announcement_link', $request->input('top_announcement_link', ''));

        // Help Center Settings
        Setting::set('help_whatsapp', $request->input('help_whatsapp', '628123456789'));
        Setting::set('help_email', $request->input('help_email', 'cs@cyberstore.id'));
        Setting::set('help_phone', $request->input('help_phone', '(021) 7867868'));

        // Save FAQs
        $faqs = [];
        if ($request->has('faqs') && is_array($request->faqs)) {
            foreach ($request->faqs as $f) {
                if (!empty(trim($f['question'] ?? '')) && !empty(trim($f['answer'] ?? ''))) {
                    $faqs[] = [
                        'question' => trim($f['question']),
                        'answer' => trim($f['answer']),
                    ];
                }
            }
        }
        Setting::set('help_faqs', json_encode($faqs));

        // Save Guides
        $guides = [];
        if ($request->has('guides') && is_array($request->guides)) {
            foreach ($request->guides as $gIdx => $g) {
                if (!empty(trim($g['title'] ?? ''))) {
                    $steps = [];
                    if (!empty($g['steps']) && is_array($g['steps'])) {
                        foreach ($g['steps'] as $s) {
                            if (!empty(trim($s['title'] ?? ''))) {
                                $steps[] = [
                                    'title' => trim($s['title']),
                                    'desc' => trim($s['desc'] ?? ''),
                                ];
                            }
                        }
                    }

                    $imagePath = $g['image'] ?? null;
                    if ($request->hasFile("guides.{$gIdx}.image_file")) {
                        $uploadedFile = $request->file("guides.{$gIdx}.image_file");
                        $stored = $uploadedFile->store('guides', 'public');
                        $imagePath = asset('storage/' . $stored);
                    }

                    $guides[] = [
                        'title' => trim($g['title']),
                        'description' => trim($g['description'] ?? ''),
                        'image' => $imagePath,
                        'steps' => $steps,
                    ];
                }
            }
        }
        Setting::set('help_guide_sections', json_encode($guides));

        if ($request->hasFile('store_logo')) {
            $oldLogo = Setting::get('store_logo');
            if ($oldLogo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('store_logo')->store('settings', 'public');
            Setting::set('store_logo', $path);
        }

        // Fetch city name from JSON to store it
        $cityName = 'Jakarta Pusat';
        $citiesPath = database_path('data/rajaongkir_cities.json');
        if (File::exists($citiesPath)) {
            $cities = json_decode(File::get($citiesPath), true);
            foreach ($cities as $city) {
                if ($city['city_id'] == $request->store_city_id) {
                    $cityName = $city['type'] . ' ' . $city['city_name'];
                    break;
                }
            }
        }
        Setting::set('store_city_name', $cityName);

        // Hapus cache agar API Flutter segera mendapat data terbaru
        try {
            Cache::forget('store:info');
            Cache::forget('info:help');
            Cache::store('redis')->forget('store:info');
            Cache::store('redis')->forget('info:help');
        } catch (\Exception $e) {
            // Ignore cache errors
        }

        return back()->with('success', 'Pengaturan toko dan Pusat Bantuan berhasil diperbarui.');
    }
}
