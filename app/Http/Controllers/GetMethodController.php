<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\ResponseTrait;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class GetMethodController
 */
class GetMethodController extends Controller
{
    public function index(Request $request): ResponseTrait
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

    private function getConfigAction(): JsonResponse
    {
        return JsonResponse::create([
            'media-endpoint' => route('media'),
        ]);
    }
}
