<?php

namespace App\Listeners;

use App\Events\PostContentEvent;
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
     * @param PostContentEvent $event
     *
     * @return void
     */
    public function handle(PostContentEvent $event): void
    {
        $this->console->call('site:build');

        return;
    }
}
