<?php

namespace App\Providers;

use App\Contracts\BlogProvider;

/**
 * Class HugoProvider
 */
class HugoProvider extends AbstractProvider implements BlogProvider
{
    /**
     * Map the Micropub entry types to the Hugo content folders.
     *
     * "entry" maps to "posts" in the "contents" folder.
     *
     * @return array
     */
    public function getContentTypes(): array
    {
        return [
            'entry' => 'posts',
        ];
    }
}
