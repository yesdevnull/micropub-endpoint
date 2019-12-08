<?php

namespace App\Events;

/**
 * Class PostContentEvent
 */
class PostContentEvent extends Event
{
    /**
     * @var string|null
     */
    private $url;

    /**
     * PostContentEvent constructor.
     *
     * @param string|null $url
     */
    public function __construct(?string $url = null)
    {
        $this->url = $url;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
}
