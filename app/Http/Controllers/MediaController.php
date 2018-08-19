<?php

namespace App\Http\Controllers;

use App\Service\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class MediaController
 */
class MediaController extends Controller
{
    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * MediaController constructor.
     *
     * @param MediaService $mediaService
     */
    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function latestUpload(Request $request): JsonResponse
    {
        return new JsonResponse(
            [
                'url' => $this->mediaService->getLatestUpload(),
            ]
        );
    }

    /**
     * Handle uploading a photo to the media endpoint.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        if (!$request->hasFile('file')) {
            throw new BadRequestHttpException('"file" parameter missing from media upload request.');
        }

        $url = $this->mediaService->uploadPhoto($request->file('file'));

        return new JsonResponse(
            [
                'url' => $url,
            ],
            201,
            [
                'Location' => $url,
            ]
        );
    }
}
