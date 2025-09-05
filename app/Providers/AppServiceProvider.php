<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $key = optional($request->user())->getAuthIdentifier() ?: $request->ip();
            if ($this->app->environment(['local', 'testing'])) {
                return [
                    Limit::perMinute(1000)->by($key),
                ];
            }
            return [
                Limit::perMinute(60)->by($key),
            ];
        });
    }
}
