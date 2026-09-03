<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\EncryptsRouteKey;

/**
 * @method bool|null delete()
 */
class Product extends Model
{
    use HasFactory, EncryptsRouteKey;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'original_price',
        'rating',
        'reviews_count',
        'sizes',
        'colors',
        'size_chart',
        'size_guide',
        'stock',
        'weight',
        'main_photo',
        'is_active',
        'is_recommended',
        'is_event_maba',
        'maba_color_ganjil',
        'maba_color_genap',
    ];

    protected $appends = [
        'encrypted_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'original_price' => 'integer',
            'rating' => 'decimal:1',
            'reviews_count' => 'integer',
            'sizes' => 'array',
            'colors' => 'array',
            'size_guide' => 'array',
            'stock' => 'integer',
            'weight' => 'integer',
            'is_active' => 'boolean',
            'is_recommended' => 'boolean',
            'is_event_maba' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    protected static function booted(): void
    {
        static::saved(fn($product) => static::clearCache($product));
        static::deleted(fn($product) => static::clearCache($product));
    }

    public static function clearCache($product = null): void
    {
        // Gunakan default cache store (database) — sama dengan yang dipakai API ProductController
        $defaultCache = \Illuminate\Support\Facades\Cache::store(config('cache.default'));

        // Coba hapus cache detail produk spesifik dari default store
        if ($product) {
            $defaultCache->forget("product:detail:{$product->id}");
        }

        // Karena database driver tidak support tags, kita flush semua cache produk
        // dengan cara hapus semua key yang mengandung pattern 'products:index'
        // Caranya: flush keseluruhan cache (aman karena cache bisa dibangun ulang)
        try {
            \Illuminate\Support\Facades\Cache::flush();
        } catch (\Throwable) {
            // Abaikan jika flush gagal
        }

        // Jika Redis tersedia, hapus juga dari Redis sebagai fallback
        try {
            $redisCache = \Illuminate\Support\Facades\Cache::store('redis');
            $redisCache->increment('admin:products:version');
            if (method_exists($redisCache, 'tags')) {
                /** @var mixed $redisCache */
                $redisCache->tags(['products-list'])->flush();
            }
            if ($product) {
                $redisCache->forget("product:detail:{$product->id}");
            }
        } catch (\Throwable) {
            // Redis tidak tersedia atau tidak dikonfigurasi — abaikan
        }
    }
}
