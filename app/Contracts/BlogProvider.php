<?php

namespace App\Contracts;

use App\Providers\HugoProvider;

/**
 * Interface BlogProvider
 */
interface BlogProvider
{
    /**
     * Array of Micropub content types and their mapping values.
     *
     * @see HugoProvider for example of content types -> mapping values
     *
     * @return array
     */
    public function getContentTypes(): array;
}
