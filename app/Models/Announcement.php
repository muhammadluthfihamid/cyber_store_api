<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptsRouteKey;

class Announcement extends Model
{
    use HasFactory, EncryptsRouteKey;

    protected $fillable = [
        'title',
        'content',
        'type',
        'action_url',
    ];

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }
}
