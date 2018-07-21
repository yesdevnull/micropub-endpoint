<?php

namespace App\Http\Middleware;

use App\Parsers\MicropubRequestParser;

/**
 * Class ParseRequestMiddleware
 */
class ParseRequestMiddleware
{
    /**
     * @var MicropubRequestParser
     */
    private $micropubRequestParser;

    /**
     * ParseRequestMiddleware constructor.
     *
     * @param MicropubRequestParser $micropubRequestParser
     */
    public function __construct(MicropubRequestParser $micropubRequestParser)
    {
        $this->micropubRequestParser = $micropubRequestParser;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        if ($request->isJson()) {
            // JSON object.
            $micropubRequest = $this->micropubRequestParser->createFromJsonRequest($request);
        } else {
            // $_POST object.
            $micropubRequest = $this->micropubRequestParser->createFromFormRequest($request);
        }

        // Replace the request input bag with the new Micropub request object.
        $request->replace([
            'micropub' => $micropubRequest,
        ]);

        return $next($request);
    }
}
