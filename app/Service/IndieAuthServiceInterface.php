<?php

namespace App\Service;

use Illuminate\Http\Request;

/**
 * Interface IndieAuthServiceInterface
 */
interface IndieAuthServiceInterface
{
    public function authenticate(Request $request): bool;
}
