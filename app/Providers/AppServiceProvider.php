<?php

namespace App\Providers;

use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Laravel 8 does not read APP_TIMEZONE from .env, so it is set here.
        $timezone = config('bikego.timezone', 'Asia/Ho_Chi_Minh');
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
    }

    public function boot()
    {
        Schema::defaultStringLength(191);

        // "admin" middleware alias used by every /admin/* route
        $this->app['router']->aliasMiddleware('admin', EnsureAdmin::class);
    }
}
