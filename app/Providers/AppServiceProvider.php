<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') === 'production' || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Pastikan semua timestamp yang dikirim ke JSON API menggunakan
        // offset +07:00 (WIB) bukan suffix 'Z' (UTC) yang menyesatkan.
        // Dengan ini Flutter dan admin panel akan selalu menampilkan jam yang sama.
        Carbon::serializeUsing(function (Carbon $carbon) {
            return $carbon->setTimezone('Asia/Jakarta')->toIso8601String();
        });
    }
}
