<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'comment',
        'photo',
        'reply',
        'is_read',
    ];

    protected $appends = [
        'photos',
    ];

    public function getPhotosAttribute(): array
    {
        $photo = $this->attributes['photo'] ?? null;
        if (!$photo) {
            return [];
        }

        $decoded = json_decode($photo, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return [$photo];
    }

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'user_id' => 'integer',
            'order_id' => 'integer',
            'rating' => 'integer',
            'is_read' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function replies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductReviewReply::class, 'product_review_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
