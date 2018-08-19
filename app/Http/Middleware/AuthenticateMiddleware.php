<?php

namespace App\Http\Middleware;

use App\Service\IndieAuthService;
use App\Service\IndieAuthServiceInterface;
use Closure;

class AuthenticateMiddleware
{
    /**
     * @var IndieAuthServiceInterface
     */
    private $indieAuthService;

    /**
     * Create a new middleware instance.
     *
     * @param IndieAuthService $indieAuthService
     */
    public function __construct(IndieAuthService $indieAuthService)
    {
        $this->indieAuthService = $indieAuthService;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Closure  $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (false === $this->indieAuthService->authenticate($request)) {
            return response('Unauthorized.', 401);
        }

        return $next($request);
    }
}
