<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

trait HasEncryptedRouteKey
{
    /**
     * Get the value of the model's route key (Encrypted for URL).
     */
    public function getRouteKey()
    {
        return urlencode(Crypt::encryptString((string) $this->getKey()));
    }

    /**
     * Retrieve the model for a bound value (Decrypted from URL).
     */
    public function resolveRouteBinding($value, $field = null)
    {
        try {
            $decrypted = Crypt::decryptString(urldecode($value));
            return static::query()->where($field ?? $this->getKeyName(), $decrypted)->first();
        } catch (\Exception $e) {
            // Fallback for raw numeric ID or invalid token
            if (is_numeric($value)) {
                return static::query()->where($field ?? $this->getKeyName(), $value)->first();
            }
            return null;
        }
    }
}
