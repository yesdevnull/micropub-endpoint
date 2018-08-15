<?php

namespace App\Providers;

use App\Contracts\BlogProvider;
use App\Service\ItemWriterService;

/**
 * Class HugoProvider
 */
class HugoProvider implements BlogProvider
{
    private const CONTENT_TYPES = [
        'entry' => 'posts',
    ];

    /**
     * @var ItemWriterService
     */
    private $itemWriterService;

    /**
     * HugoProvider constructor.
     *
     * @param ItemWriterService $itemWriterService
     */
    public function __construct(ItemWriterService $itemWriterService)
    {
        $this->itemWriterService = $itemWriterService;
    }

    /**
     * @param string $entry Name of the entry.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getContentType(string $entry): string
    {
        if (!isset(self::CONTENT_TYPES[$entry])) {
            throw new \InvalidArgumentException("'$entry' for content type is invalid.");
        }

        return self::CONTENT_TYPES[$entry];
    }

    /**
     * @param null|string $path
     *
     * @return string
     */
    public function getContentPath(?string $path = null): string
    {
        $basePath = env('BASE_CONTENT_ENTRY_PATH', app()->storagePath());

        if (null === $path) {
            return $basePath;
        }

        $basePath = ends_with($basePath, '/') ? $basePath : $basePath.'/';

        return $basePath.$path;
    }

    /**
     * @param string $contentType
     *
     * @return string
     */
    public function getContentPathForType(string $contentType): string
    {
        return $this->getContentPath(
            $this->getContentType($contentType)
        );
    }

    public function writeFile(
        string $content,
        string $file,
        bool $overwrite = false
    ) {
        $containingDir = \dirname($file);

        if (!file_exists($containingDir)) {
            if (!mkdir($containingDir, 0777, true) && !is_dir($containingDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $containingDir));
            }

            // touch($containingDir.'/_index.md');
        }

        if (!$overwrite && file_exists($file)) {
            throw new \RuntimeException("'$file' already exists and overwrite was not enabled.");
        }

        file_put_contents($file, $content, LOCK_EX);
    }
}
