<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS scheme when APP_URL uses https (helps when behind proxies/ngrok)
        $appUrl = env('APP_URL', '');
        if (!empty($appUrl) && str_starts_with($appUrl, 'https')) {
            URL::forceRootUrl($appUrl);
            URL::forceScheme('https');
        }
    }
}
