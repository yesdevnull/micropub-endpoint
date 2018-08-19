<?php

namespace App\Service;

use Illuminate\Http\Request;

/**
 * Interface IndieAuthServiceInterface
 */
interface IndieAuthServiceInterface
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    public function authenticate(Request $request): bool;
}
