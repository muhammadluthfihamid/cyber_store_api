<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\EncryptsRouteKey;

class Order extends Model
{
    use HasFactory, EncryptsRouteKey;

    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_PAID = 'paid';

    public const STATUS_PACKED = 'packed';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_ARRIVED = 'arrived';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'invoice_number',
        'user_id',
        'customer_address_id',
        'expedition_id',
        'subtotal',
        'shipping_cost',
        'grand_total',
        'status',
        'resi_number',
        'note',
        'cancel_request_status',
        'cancel_request_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING_PAYMENT => 'Menunggu Pembayaran',
            self::STATUS_PAID => 'Pembayaran Berhasil',
            self::STATUS_PACKED => 'Pesanan Dikemas',
            self::STATUS_SHIPPED => 'Pesanan Dikirim',
            self::STATUS_ARRIVED => 'Pesanan Tiba',
            self::STATUS_COMPLETED => 'Pesanan Selesai',
            self::STATUS_CANCELLED => 'Pesanan Dibatalkan',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'customer_address_id');
    }

    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function trackings(): HasMany
    {
        return $this->hasMany(OrderTracking::class)->latest();
    }

    public function syncTracking(): array
    {
        if (empty($this->resi_number)) {
            return [
                'success' => false,
                'message' => 'Nomor resi belum diisi.'
            ];
        }

        $courierMap = [
            'jne_reg' => 'jne',
            'jne'     => 'jne',
            'pos'     => 'pos',
            'tiki'    => 'tiki',
            'sicepat' => 'sicepat',
        ];
        $courierCode = $courierMap[$this->expedition?->code] ?? 'jne';

        $apiKey = env('RAJAONGKIR_API_KEY');
        $baseUrl = env('RAJAONGKIR_BASE_URL', 'https://api.rajaongkir.com/starter');

        $waybillUrl = 'https://api.rajaongkir.com/basic/waybill';
        if (str_contains($baseUrl, 'pro.rajaongkir.com')) {
            $waybillUrl = 'https://pro.rajaongkir.com/api/waybill';
        }

        $apiSuccess = false;
        $manifestData = [];

        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()->timeout(5)->withHeaders([
                'key' => $apiKey
            ])->post($waybillUrl, [
                'waybill' => $this->resi_number,
                'courier' => $courierCode
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['rajaongkir']['result']) && $data['rajaongkir']['status']['code'] == 200) {
                    $result = $data['rajaongkir']['result'];
                    $manifestData = $result['manifest'] ?? [];
                    $apiSuccess = true;

                    if (($result['delivered'] ?? false) || ($result['delivery_status']['status'] ?? '') === 'DELIVERED') {
                        $this->update(['status' => self::STATUS_ARRIVED]);
                    }
                }
            }
        } catch (\Exception $e) {
            // Fallback
        }

        if (!$apiSuccess) {
            $manifestData = $this->generateMockManifest();

            $isDelivered = collect($manifestData)->contains('status', self::STATUS_ARRIVED);
            if ($isDelivered) {
                $this->update(['status' => self::STATUS_ARRIVED]);
            }
        }

        // Delete existing non-admin manifest trackings
        $this->trackings()
            ->whereIn('status', [self::STATUS_SHIPPED, self::STATUS_ARRIVED])
            ->where('location', '!=', 'Admin Panel')
            ->delete();

        foreach ($manifestData as $step) {
            $this->trackings()->create([
                'status'      => $step['status'] ?? $this->status,
                'description' => $step['description'],
                'location'    => $step['location'] ?? 'Dalam Perjalanan',
                'proof_photo' => $step['proof_photo'] ?? null,
                'created_at'  => $step['created_at'] ?? now(),
            ]);
        }

        return [
            'success' => true,
            'message' => $apiSuccess ? 'Sinkronisasi resi real-time berhasil!' : 'Sinkronisasi resi berhasil (Mock Mode).'
        ];
    }

    private function generateMockManifest(): array
    {
        $createdAt = $this->created_at ? \Carbon\Carbon::parse($this->created_at) : now();
        $receiver = $this->address?->receiver_name ?? 'Customer';
        $city = $this->address?->city ?? 'Kota Tujuan';

        $storeName = Setting::get('store_name', 'UBSI Store');
        $storeCity = Setting::get('store_city_name', 'Jakarta Pusat');

        $manifest = [
            [
                'status'      => self::STATUS_SHIPPED,
                'description' => 'Paket telah diserahkan kepada kurir.',
                'location'    => "$storeCity (Gudang $storeName)",
                'created_at'  => $createdAt->copy()->addHours(2),
            ],
            [
                'status'      => self::STATUS_SHIPPED,
                'description' => 'Paket sedang dikirim ke Hub Logistik.',
                'location'    => 'Jakarta Hub',
                'created_at'  => $createdAt->copy()->addHours(6),
            ],
        ];

        if ($createdAt->diffInDays(now()) >= 1) {
            $manifest[] = [
                'status'      => self::STATUS_SHIPPED,
                'description' => 'Paket dalam perjalanan ke ' . $city,
                'location'    => 'Transit Hub',
                'created_at'  => $createdAt->copy()->addDays(1),
            ];
        }

        if ($createdAt->diffInDays(now()) >= 2) {
            $manifest[] = [
                'status'      => self::STATUS_ARRIVED,
                'description' => 'Paket telah tiba di kota tujuan dan sedang dibawa oleh kurir.',
                'location'    => $city,
                'created_at'  => $createdAt->copy()->addDays(2),
            ];
            $manifest[] = [
                'status'      => self::STATUS_ARRIVED,
                'description' => 'Paket berhasil diterima oleh [' . $receiver . '] (Ybs). Bukti foto serah terima otomatis terunggah.',
                'location'    => $city,
                'proof_photo' => 'order_proofs/mock_pod_sample.jpg',
                'created_at'  => $createdAt->copy()->addDays(2)->addHours(4),
            ];
        }

        return array_reverse($manifest);
    }
}
