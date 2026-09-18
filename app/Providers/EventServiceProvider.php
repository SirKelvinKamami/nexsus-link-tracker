<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // The framework's SendEmailVerificationNotification listener is
        // deliberately NOT registered: it fires outside any try/catch, so a
        // misconfigured/outage mail server would 500 every registration.
        // Verification sends live solely in RegisteredUserController::store,
        // gated on REGISTER_AUTH=verified and wrapped in try/catch (reported,
        // never fatal).
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
