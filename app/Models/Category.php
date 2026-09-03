<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\EncryptsRouteKey;

class Category extends Model
{
    use HasFactory, EncryptsRouteKey;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $appends = [
        'encrypted_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected static function booted(): void
    {
        static::saved(fn() => static::clearCache());
        static::deleted(fn() => static::clearCache());
    }

    public static function clearCache(): void
    {
        \Illuminate\Support\Facades\Cache::flush();
    }
}
