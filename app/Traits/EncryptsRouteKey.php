<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

trait EncryptsRouteKey
{
    /**
     * Get the value of the model's route key.
     * Encrypts the key if the request is for the admin panel.
     *
     * @return mixed
     */
    /**
     * Get the value of the model's route key.
     * Encrypts the key if the request is for the admin panel, or prefers slug/encrypted key for public.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        if (request()->is('admin*')) {
            return urlencode(Crypt::encryptString((string) $this->getKey()));
        }

        if (!empty($this->attributes['slug'])) {
            return $this->attributes['slug'];
        }

        return urlencode(Crypt::encryptString((string) $this->getKey()));
    }

    /**
     * Get an encrypted representation of the ID.
     */
    public function getEncryptedIdAttribute(): string
    {
        try {
            return urlencode(Crypt::encryptString((string) $this->getKey()));
        } catch (\Throwable) {
            return (string) $this->getKey();
        }
    }

    /**
     * Decrypt an encrypted ID string.
     */
    public static function decryptId(string $encrypted): ?int
    {
        try {
            $decrypted = Crypt::decryptString(urldecode($encrypted));
            return is_numeric($decrypted) ? (int) $decrypted : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Retrieve the model for a bound value.
     * Decrypts the value if it's for the admin panel or looks like encrypted string, or resolves by slug.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $isAdmin = request()->is('admin*');

        if ($isAdmin || (is_string($value) && !is_numeric($value))) {
            try {
                $decrypted = Crypt::decryptString(urldecode((string) $value));
                return static::query()->where($field ?? $this->getRouteKeyName(), $decrypted)->first();
            } catch (\Throwable $e) {
                // If not encrypted, and not in admin, check if model has slug
                if (!$isAdmin && \Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'slug')) {
                    $slugMatch = static::query()->where('slug', $value)->first();
                    if ($slugMatch) {
                        return $slugMatch;
                    }
                }
                return null;
            }
        }

        return parent::resolveRouteBinding($value, $field);
    }
}
