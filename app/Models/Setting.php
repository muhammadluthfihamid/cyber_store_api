<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, $default = null)
    {
        return \Illuminate\Support\Facades\Cache::remember('setting_' . $key, 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            if (!$setting && $default !== null) {
                $setting = self::create(['key' => $key, 'value' => (string) $default]);
            }
            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, $value)
    {
        \Illuminate\Support\Facades\Cache::forget('setting_' . $key);
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value === null ? null : (string) $value]
        );
    }
}
