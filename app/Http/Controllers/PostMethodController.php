<?php

namespace App\Http\Controllers;

use App\Service\MediaService;
use App\ValueObjects\ItemRequestValueObjectInterface;
use Illuminate\Http\Request;

/**
 * Class PostMethodController
 */
class PostMethodController extends Controller
{
    /**
     * @var MediaService
     */
    private $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function index(Request $request)
    {
        /** @var ItemRequestValueObjectInterface $micropubRequestObject */
        $micropubRequestObject = $request->input('micropub');

        if ($micropubRequestObject->hasPhotos()) {
            $this->mediaService->uploadPhotos($micropubRequestObject->getPhotos());
        }

        if (ItemRequestValueObjectInterface::ACTION_CREATE === $micropubRequestObject->getAction()) {
            $this->handleCreateItem($micropubRequestObject);
        }
    }

    private function handleCreateItem(ItemRequestValueObjectInterface $newItem)
    {
        $frontMatter = collect([]);
        $frontMatterProperties = $newItem->getFrontMatter();
        //$frontMatter = collect($frontMatterProperties);
        $content = $newItem->getContent();

        if ($frontMatterProperties->has('post-status')) {
            $frontMatter['published'] = 'draft' !== $frontMatterProperties['post-status'];
        } else {
            $frontMatter['published'] = true;
        }

        // This slug is derived from https://manton.org who uses the first 3 words of a post as the slug.
        if (!$frontMatterProperties->has('name') && !$frontMatterProperties->has('slug')) {
            // Get the first 100 characters so we don't do further operations on the entire content string.
            $startText = mb_substr($content, 0, 100);

            // Split string by space characters (one or more).
            $words = preg_split("/[\s]+/", $startText);

            $firstThreeWordsArray = \array_slice($words, 0, 3);

            $slug = implode($firstThreeWordsArray);
        }

        if ($frontMatterProperties->has('name') && !$frontMatterProperties->has('slug')) {
            $slug = $frontMatterProperties['name'];
        }

        if ($frontMatterProperties->has('slug')) {
            $slug = $frontMatterProperties['slug'];
        }

        $frontMatter['slug'] = str_slug($slug, '-');

        $frontMatter['date'] = (new \DateTime('now', env('APP_TIMEZONE')))->format('Y-m-d H:i:s');
    }
}
