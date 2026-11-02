<?php

namespace App\Providers;

use App\Support\Jwt;
use App\Support\SiteConfig;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', fn (Request $req) => Limit::perMinute(60)->by($req->ip()));
        RateLimiter::for('auth', fn (Request $req) => Limit::perMinute(5)->by($req->ip()));
        RateLimiter::for('forms', fn (Request $req) => Limit::perMinute(10)->by($req->ip()));
        RateLimiter::for('public', fn (Request $req) => Limit::perMinute(60)->by($req->ip()));
        // Store-pickup holds: 5 per hour per IP.
        RateLimiter::for('reserve', fn (Request $req) => Limit::perHour(5)->by($req->ip()));
        // Signed-in customer writes (wishlist, subscriptions, push): per customer, falling back to IP.
        RateLimiter::for('customer-writes', fn (Request $req) => Limit::perMinute(30)->by($req->cookie('customer_token') ? sha1($req->cookie('customer_token')) : $req->ip()));

        View::composer('*', function ($view) {
            // Cache site config for 5 minutes to avoid env() calls on every request.
            $site = Cache::remember('site_config', 300, fn () => SiteConfig::all());
            $view->with('site', $site);
            $view->with('authCustomer', Jwt::verify(request()->cookie('customer_token')));
            $view->with('authAdmin', Jwt::verify(request()->cookie('admin_token')));
        });
    }
}
