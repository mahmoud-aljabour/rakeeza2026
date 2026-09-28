<?php

namespace App\Providers;

use App\Services\ServiceService;
use App\Services\SettingService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View as ViewFactory;
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

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureUrls();

        ViewFactory::composer(['errors::404', 'errors.404'], function (View $view): void {
            $view->with([
                'site' => $this->app->make(SettingService::class)->publicSite(),
                'services' => $this->app->make(ServiceService::class)->listActive(),
            ]);
        });

        RateLimiter::for('quote-requests', function (Request $request) {
            return Limit::perMinute(3)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => __('site.form.rate_limited'),
                    ], 429);
                });
        });

        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->after(static fn ($response): bool => $response->getStatusCode() === 422)
                ->response(function () {
                    return response()->json([
                        'message' => __('site.auth.rate_limited'),
                    ], 429);
                });
        });
    }

    /**
     * In production every generated link, canonical tag, and sitemap entry uses APP_URL,
     * even when the request arrives over plain HTTP behind a proxy or through another host.
     */
    public function configureUrls(): void
    {
        $appUrl = rtrim((string) config('app.url'), '/');

        if (! $this->app->isProduction() || ! str_starts_with($appUrl, 'https://')) {
            return;
        }

        URL::forceRootUrl($appUrl);
        URL::forceScheme('https');
    }
}
