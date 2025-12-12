<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Middleware\TrackLastActive;
use App\Http\Middleware\LogPageVisit;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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
        // Register custom middleware
        $this->app['router']->aliasMiddleware('track_last_active', TrackLastActive::class);
        $this->app['router']->aliasMiddleware('log_page_visit', LogPageVisit::class);

        // Create symbolic link if it doesn't exist
        if (!file_exists(public_path('storage'))) {
            app('files')->link(
                storage_path('app/public'),
                public_path('storage')
            );
        }

        $this->configureRateLimiting();



    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('ai-api', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}


