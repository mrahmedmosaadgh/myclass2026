<?php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

public function boot()
{
    RateLimiter::for('ai-api', function (Request $request) {
        return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
    });

    // ... rest of the boot method
}
