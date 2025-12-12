<?php

namespace App\Providers;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
    ];

    public function boot(): void
    {
        parent::boot();

        Event::listen(Authenticated::class, function ($event) {
            $user = $event->user;
            $user->last_login = now();
            $user->last_active = now();
            $user->save();
        });
    }
}

