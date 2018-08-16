<?php

namespace App\Http\Controllers;

use App\Contracts\BlogProvider;
use App\Service\ItemWriterService;
use App\Service\MediaService;
use App\ValueObjects\ItemRequestValueObjectInterface;
use Illuminate\Http\Request;

/**
 * Class PostMethodController
 */
class PostMethodController extends Controller
{
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
     * @param ItemWriterService $itemWriterService
     * @param MediaService      $mediaService
     * @param BlogProvider      $blogProvider
     */
    public function __construct(
        ItemWriterService $itemWriterService,
        MediaService $mediaService,
        BlogProvider $blogProvider
    ) {
        $this->itemWriterService = $itemWriterService;
        $this->mediaService = $mediaService;
        $this->blogProvider = $blogProvider;
    }

    public function index(Request $request)
    {
        /** @var ItemRequestValueObjectInterface $micropubRequestObject */
        $micropubRequestObject = $request->input('micropub');

        if (ItemRequestValueObjectInterface::ACTION_CREATE === $micropubRequestObject->getAction()) {
            $this->handleCreateItem($micropubRequestObject);
        }
    }

    private function handleCreateItem(ItemRequestValueObjectInterface $newItem)
    {
        $frontMatter = collect([]);
        $frontMatterProperties = $newItem->getFrontMatter();
        $content = $newItem->getContent();

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

        // This slug is derived from https://manton.org who uses the first 3 words of a post as the slug.
        if (!$frontMatterProperties->has('name') && !$frontMatterProperties->has('slug')) {
            // Get the first 100 characters so we don't do further operations on the entire content string.
            $startText = mb_substr($content, 0, 100);

            // Split string by space characters (one or more).
            $words = preg_split("/[\s]+/", $startText);

            $firstThreeWordsArray = \array_slice($words, 0, 3);

            $firstThreeWords = implode(' ', $firstThreeWordsArray);

            $slug = str_slug($firstThreeWords, '-');
        }

        if ($frontMatterProperties->has('name') && !$frontMatterProperties->has('slug')) {
            $slug = $frontMatterProperties['name'];
        }

        if ($frontMatterProperties->has('slug')) {
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
    }
}
