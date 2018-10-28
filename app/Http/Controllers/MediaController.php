<?php

namespace App\Http\Controllers;

use App\Events\RebuildSiteEvent;
use App\Service\MediaService;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * Fetch the URL for the most recent media upload.
     *
     * Note that we return a 400 error and not a 404.  A 404 would declare that the
     * *endpoint* doesn't exist when it in fact does.
     *
     * @return JsonResponse
     */
    public function latestUpload(): JsonResponse
    {
        $url = $this->mediaService->getLatestUpload();

        if ('' === $url) {
            return new JsonResponse(
                [
                    'error' => 'invalid_request',
                    'error_description' => 'Unable to find the last media upload.',
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

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
            Response::HTTP_CREATED,
            [
                'Location' => $url,
            ]
        );
    }
}
