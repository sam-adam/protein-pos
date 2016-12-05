<?php

namespace App\Providers;

use App\Listeners\LoginSessionLogger;
use Illuminate\Auth\Events\Login as LoginEvent;
use Illuminate\Auth\Events\Logout as LogoutEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        LoginEvent::class  => [LoginSessionLogger::class],
        LogoutEvent::class => [LoginSessionLogger::class],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        //
    }
}
