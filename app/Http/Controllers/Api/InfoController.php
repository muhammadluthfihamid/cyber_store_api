<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class InfoController extends Controller
{
    public function about(): JsonResponse
    {
        $data = Cache::remember('info:about', now()->addDay(), function () {
            return [
                'app_name' => 'Aplikasi Mahasiswa Baru Universitas Bina Sarana Informatika',
                'version' => 'v1.2.0',
                'description' => 'Aplikasi Resmi Mahasiswa Baru Universitas Bina Sarana Informatika. Untuk pembelian atribut dan keperluan Semot dan Ormik mahasiswa baru Universitas Bina Sarana Infromatika.',
                'terms_and_conditions' => [
                    'title' => 'Syarat & Ketentuan',
                    'content' => '1. Penggunaan aplikasi ini ditujukan khusus untuk mahasiswa baru Universitas Bina Sarana Informatika. 2. Transaksi pembelian produk tunduk pada kebijakan pengiriman dan pengembalian barang yang berlaku. 3. Pengguna bertanggung jawab penuh atas kerahasiaan akun dan kata sandi masing-masing.',
                ],
                'privacy_policy' => [
                    'title' => 'Kebijakan Privasi',
                    'content' => 'Kami menghargai privasi Anda. Informasi pribadi Anda seperti nama, email, dan nomor telepon digunakan hanya untuk kepentingan transaksi di aplikasi. Kami tidak membagikan data Anda kepada pihak ketiga tanpa persetujuan Anda.',
                ],
                'credits_and_contributors' => [
                    'title' => 'Kredit & Kontributor',
                    'content' => 'Dikembangkan oleh tim BTI Universitas Bina Sarana Informatika bekerjasama dengan Divisi Kemahasiswaan Universitas Bina Sarana Informatika.',
                ],
            ];
        });

        return response()->json($data);
    }

    public function help(): JsonResponse
    {
        $data = Cache::remember('info:help', now()->addDay(), function () {
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

            $faqsRaw = \App\Models\Setting::get('help_faqs');
            $faqs = !empty($faqsRaw) ? json_decode($faqsRaw, true) : $defaultFaqs;

            $whatsapp = \App\Models\Setting::get('help_whatsapp', '628123456789');
            $email = \App\Models\Setting::get('help_email', 'support@bsi.ac.id');
            $phone = \App\Models\Setting::get('help_phone', '(021) 7867868');

            $guidesRaw = \App\Models\Setting::get('help_guide_sections');
            $guides = !empty($guidesRaw) ? json_decode($guidesRaw, true) : null;

            return [
                'faqs' => $faqs,
                'contacts' => [
                    'whatsapp' => [
                        'label' => 'WhatsApp CS',
                        'value' => str_starts_with($whatsapp, 'http') ? $whatsapp : 'https://wa.me/' . preg_replace('/[^0-9]/', '', $whatsapp),
                        'icon' => 'whatsapp',
                    ],
                    'email' => [
                        'label' => 'Email Support',
                        'value' => $email,
                        'icon' => 'email',
                    ],
                    'telephone' => [
                        'label' => 'Customer Call Center',
                        'value' => $phone,
                        'icon' => 'phone',
                    ],
                ],
                'app_guide' => [
                    'title' => 'Panduan Aplikasi',
                    'url' => 'https://ubsistore.test/guides/app-manual.pdf',
                    'sections' => $guides,
                ],
            ];
        });

        return response()->json($data);
    }

    public function storeInfo(): JsonResponse
    {
        // Cache store info — hapus cache jika setting berubah di admin
        $data = Cache::remember('store:info', now()->addDay(), function () {
            $logo = \App\Models\Setting::get('store_logo');

            return [
                'store_name'      => \App\Models\Setting::get('store_name', 'BSI Cyber Store'),
                'store_address'   => \App\Models\Setting::get('store_address', 'Jl. Kramat Raya No.98, Senen, Jakarta Pusat'),
                'store_city_name' => \App\Models\Setting::get('store_city_name', 'Jakarta Pusat'),
                'store_email'     => \App\Models\Setting::get('store_email', 'support@bsi.ac.id'),
                'store_phone'     => \App\Models\Setting::get('store_phone', '(021) 7867868'),
                'store_logo'      => $logo ? asset('storage/' . $logo) : asset('assets/img/logo-cyberstore.jpg'),
                'announcement'    => [
                    'is_active' => (bool) \App\Models\Setting::get('top_announcement_is_active', true),
                    'badge'     => \App\Models\Setting::get('top_announcement_badge', 'BSI Cyber Store Official'),
                    'text'      => \App\Models\Setting::get('top_announcement_text', '🔥 Diskon Hingga 50% untuk Semua Gadget & Aksesoris Gaming! Gunakan Kode: CYBER2026'),
                    'info'      => \App\Models\Setting::get('top_announcement_info', '⚡ Garansi Resmi 100%'),
                    'link'      => \App\Models\Setting::get('top_announcement_link', ''),
                ],
            ];
        });

        return response()->json($data);
    }
}
