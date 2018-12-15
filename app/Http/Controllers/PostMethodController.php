<?php

namespace App\Http\Controllers;

use App\Events\RebuildSiteEvent;
use App\Providers\AbstractProvider;
use App\Service\ItemWriterService;
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
     * @var AbstractProvider
     */
    private $blogProvider;

    /**
     * PostMethodController constructor.
     *
     * @param DispatcherContract $eventDispatcher
     * @param ItemWriterService  $itemWriterService
     * @param AbstractProvider   $blogProvider
     */
    public function __construct(
        DispatcherContract $eventDispatcher,
        ItemWriterService $itemWriterService,
        AbstractProvider $blogProvider
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->itemWriterService = $itemWriterService;
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
        $commands = $newItem->getCommands();
        $content = $newItem->getContent();

        $now = new \DateTime(
            'now',
            new \DateTimeZone(env('APP_TIMEZONE'))
        );

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

        $slug = $now->format('l jS');

        // This slug is derived from https://manton.org which uses the first 3 words of a post as the slug.
        if ('' !== $content && !array_key_exists('mp-slug', $commands) && '' === $frontMatterProperties->get('name', '')) {
            // Get the first 100 characters so we don't do further operations on the entire content string.
            $startText = mb_substr($content, 0, 100);

            // Remove Markdown URLs and replace them with the name of the URL.
            $startText = preg_replace('/\[([^\]]+)\](\([^\)]+\))/', '$1', $startText);

            // Split string by space characters (one or more).
            $words = preg_split("/[\s]+/", $startText);

            $firstThreeWordsArray = \array_slice($words, 0, 3);

            $firstThreeWords = implode(' ', $firstThreeWordsArray);

            $slug = str_slug($firstThreeWords, '-');
        }

        if (!array_key_exists('mp-slug', $commands) && '' !== $frontMatterProperties->get('name', '')) {
            $slug = $frontMatterProperties['name'];
        }

        if (array_key_exists('mp-slug', $commands) && '' !== $commands['mp-slug']) {
            $slug = $commands['mp-slug'];
        }

        $frontMatter['slug'] = str_slug($slug, '-');

        $frontMatter['date'] = $now->format(\DateTime::W3C);

        if ($frontMatterProperties->has('name')) {
            $frontMatter['title'] = $frontMatterProperties->get('name');
        }

        $pathToWriteTo = $this->blogProvider->getContentPathForType($newItem->getType());

        $fileContents = $this->itemWriterService->build($frontMatter->toArray(), $content);

        $filename = sprintf(
            '%s-%s.md',
            $now->format('Y-m-d'),
            $frontMatter['slug']
        );

        $this->itemWriterService->writeFile(
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
            $frontMatter['slug']
        );
    }
}
