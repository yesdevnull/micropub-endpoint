<?php

namespace App\Contracts;

/**
 * Interface BlogProvider
 */
interface BlogProvider
{
    /**
     * @param string $entry Name of the entry.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getContentType(string $entry): string;

    /**
     * @param null|string $path
     *
     * @return string
     */
    public function getContentPath(?string $path = null): string;

    /**
     * @param string $contentType
     *
     * @return string
     */
    public function getContentPathForType(string $contentType): string;

    public function writeFile(
        string $content,
        string $file
    );
}
