<?php

namespace App\Http\Controllers;

use App\Contracts\BlogProvider;
use App\Events\RebuildSiteEvent;
use App\Service\ItemWriterService;
use App\Service\MediaService;
use App\ValueObjects\ItemRequestValueObjectInterface;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class PostMethodController
 */
class PostMethodController extends Controller
{
    /**
     * @var DispatcherContract
     */
    private $eventDispatcher;

    /**
     * @var ItemWriterService
     */
    private $itemWriterService;

    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * @var BlogProvider
     */
    private $blogProvider;

    /**
     * PostMethodController constructor.
     *
     * @param DispatcherContract $eventDispatcher
     * @param ItemWriterService  $itemWriterService
     * @param MediaService       $mediaService
     * @param BlogProvider       $blogProvider
     */
    public function __construct(
        DispatcherContract $eventDispatcher,
        ItemWriterService $itemWriterService,
        MediaService $mediaService,
        BlogProvider $blogProvider
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->itemWriterService = $itemWriterService;
        $this->mediaService = $mediaService;
        $this->blogProvider = $blogProvider;
    }

    public function index(Request $request)
    {
        /** @var ItemRequestValueObjectInterface $micropubRequestObject */
        $micropubRequestObject = $request->input('micropub');

        if (ItemRequestValueObjectInterface::ACTION_CREATE === $micropubRequestObject->getAction()) {
            $url = $this->handleCreateItem($micropubRequestObject);

            // Return an empty body with the URL as the "Location" header.
            return new Response(
                '',
                201,
                [
                    'Location' => $url,
                ]
            );
        }

        // TBA...
    }

    private function handleCreateItem(ItemRequestValueObjectInterface $newItem): string
    {
        $frontMatter = collect([]);
        $frontMatterProperties = $newItem->getFrontMatter();
        $content = $newItem->getContent();

        info('frontMatterProperties: '.print_r($frontMatterProperties, true));
        info('content: '.print_r($content, true));

        if ($frontMatterProperties->has('post-status')) {
            $frontMatter['published'] = 'draft' !== $frontMatterProperties['post-status'];
        } else {
            $frontMatter['published'] = true;
        }

        $frontMatter['photo'] = array_merge(
            $frontMatterProperties['photo'] ?? [],
            $newItem->getPhotos()
        );

        $slug = '';

        // This slug is derived from https://manton.org which uses the first 3 words of a post as the slug.
        if ('' === $frontMatterProperties->get('name', '') && '' === $frontMatterProperties->get('slug', '')) {
            // Get the first 100 characters so we don't do further operations on the entire content string.
            $startText = mb_substr($content, 0, 100);

            // Split string by space characters (one or more).
            $words = preg_split("/[\s]+/", $startText);

            $firstThreeWordsArray = \array_slice($words, 0, 3);

            $firstThreeWords = implode(' ', $firstThreeWordsArray);

            $slug = str_slug($firstThreeWords, '-');
        }

        if ('' !== $frontMatterProperties->get('name', '') && '' === $frontMatterProperties->get('slug', '')) {
            $slug = $frontMatterProperties['name'];
        }

        if ('' !== $frontMatterProperties->get('slug', '')) {
            $slug = $frontMatterProperties['slug'];
        }

        $frontMatter['slug'] = str_slug($slug, '-');

        $now = new \DateTime('now', new \DateTimeZone(env('APP_TIMEZONE')));

        $frontMatter['date'] = $now->format(\DateTime::W3C);

        $pathToWriteTo = $this->blogProvider->getContentPathForType($newItem->getType());

        $fileContents = $this->itemWriterService->build($frontMatter->toArray(), $content);

        // $filename = $frontMatter['slug'].'.md';

        $filename = sprintf(
            '%s-%s.md',
            $now->format('Y-m-d'),
            $frontMatter['slug']
        );

        $this->blogProvider->writeFile(
            $fileContents,
            $pathToWriteTo.'/'.$filename
        );

        $this->eventDispatcher->dispatch(
            new RebuildSiteEvent()
        );

        return sprintf(
            '%s%d/%02d/%s/',
            env('ME_URL'),
            $now->format('Y'),
            $now->format('m'),
            $slug
        );
    }
}
