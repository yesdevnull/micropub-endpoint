<?php

namespace App\Listeners;

use App\Events\RebuildSiteEvent;
use Illuminate\Contracts\Console\Kernel;

/**
 * Class RebuildSiteListener
 */
class RebuildSiteListener
{
    /**
     * @var Kernel
     */
    private $console;

    /**
     * Create the event listener.
     *
     * @param Kernel $console
     */
    public function __construct(Kernel $console)
    {
        $this->console = $console;
    }

    /**
     * Handle the event.
     *
     * @param RebuildSiteEvent $event
     *
     * @return void
     */
    public function handle(RebuildSiteEvent $event): void
    {
        $this->console->call('site:build');

        return;
    }
}
