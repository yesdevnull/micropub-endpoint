<?php

namespace App\ServiceProviders;

use App\Events\RebuildSiteEvent;
use App\Listeners\RebuildSiteListener;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class EventServiceProvider
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        RebuildSiteEvent::class => [
            RebuildSiteListener::class,
        ],
    ];
}
