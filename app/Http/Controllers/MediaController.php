<?php

namespace App\Http\Controllers;

use App\Events\RebuildSiteEvent;
use App\Service\MediaService;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class MediaController
 */
class MediaController extends Controller
{
    /**
     * @var DispatcherContract
     */
    private $eventDispatcher;

    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * MediaController constructor.
     *
     * @param DispatcherContract $eventDispatcher
     * @param MediaService       $mediaService
     */
    public function __construct(
        DispatcherContract $eventDispatcher,
        MediaService $mediaService
    ) {
        $this->eventDispatcher = $eventDispatcher;
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

        // Even though we're only uploading a file we need to rebuild the site because it copies the actual file across.
        $this->eventDispatcher->dispatch(
            new RebuildSiteEvent()
        );

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
