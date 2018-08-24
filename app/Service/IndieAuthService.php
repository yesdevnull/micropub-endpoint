<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Webmozart\Assert\Assert;

/**
 * Class IndieAuthService
 */
class IndieAuthService implements IndieAuthServiceInterface
{
    public function authenticate(Request $request): bool
    {
        // Skip auth on local environments.
        if ('local' === app()->environment()) {
            return true;
        }

        $client = new Client();

        app('log')->info('Request Headers: '.print_r($request->headers->all(), true));

        try {
            $authRequest = $client->get(
                env('INDIEAUTH_ENDPOINT'),
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => $request->header('Authorization'),
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'User-Agent' => env('ME_URL'),
                    ]
                ]
            );
        } catch (RequestException $e) {
            app('log')->error('Psr Request: '.Psr7\str($e->getRequest()));

            if ($e->hasResponse()) {
                app('log')->error('Psr Response: '.Psr7\str($e->getResponse()));
            }

            return false;
        }

        $response = json_decode($authRequest->getBody()->getContents(), true);

        if (isset($response['error'])) {
            throw new AuthenticationException($response['error']);
        }

        if (null === $response['me']) {
            app('log')->error('Psr Response: '.Psr7\str($authRequest));
            app('log')->error('IndieAuth response: '.print_r($response, true));

            return false;
        }

        Assert::eq($response['me'], env('ME_URL'));

        Assert::keyExists($response, 'scope');

        if (\is_array($response['scope']) && !\in_array('create', $response['scope'], true) && !\in_array('post', $response['scope'], true)) {
            return false;
        }

        if (\is_string($response['scope']) && false === stripos($response['scope'], 'create')) {
            return false;
        }

        return true;
    }
}
