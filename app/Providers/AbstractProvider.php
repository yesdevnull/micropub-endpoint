<?php

namespace App\Providers;

/**
 * Class AbstractProvider
 */
abstract class AbstractProvider
{
    /**
     * @param string $entry Name of the entry.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getContentType(string $entry): string
    {
        if (!isset($this->getContentTypes()[$entry])) {
            throw new \InvalidArgumentException("'$entry' for content type is invalid.");
        }

        return $this->getContentTypes()[$entry];
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
}
