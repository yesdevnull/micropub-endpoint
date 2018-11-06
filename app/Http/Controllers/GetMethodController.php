<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class GetMethodController
 */
class GetMethodController extends Controller
{
    /**
     * This endpoint is used for querying the server with the q=config query string.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        if (!$request->has('q')) {
            throw new BadRequestHttpException('Missing "q" parameter.');
        }

        $query = $request->get('q');

        if ('config' === $query) {
            return $this->getConfigAction();
        }

        throw new BadRequestHttpException('Invalid "q" value provided.');
    }

    /**
     * Returns the Micropub server configuration array.
     *
     * @see https://www.w3.org/TR/micropub/#configuration
     *
     * @return JsonResponse
     */
    private function getConfigAction(): JsonResponse
    {
        return JsonResponse::create([
            'media-endpoint' => route('media_upload'),
        ]);
    }
}
