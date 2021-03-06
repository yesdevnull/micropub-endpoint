<?php

namespace App\Service;

use Symfony\Component\Yaml\Yaml;

/**
 * Class ItemWriterService
 */
class ItemWriterService
{
    /**
     * Build and write an entry ready to be persisted to the filesystem.
     *
     * @param array  $frontMatter Array of front matter for the entry.
     * @param string $content     The content of the post.
     *
     * @return string Entry with YAML structured front matter.
     */
    public function build(
        array $frontMatter,
        string $content
    ): string {
        $frontMatter = $this->cleanAndFormatFrontMatter($frontMatter);

        $yamlFrontMatter = Yaml::dump($frontMatter);

        // Yaml::dump has a trailing new line, so the ending front matter delimiter
        // is on the same line so we avoid a blank line.
        return <<<HEREDOC
---
$yamlFrontMatter---
$content
HEREDOC;
    }

    /**
     * Drop "empty" values from the front matter array (while ensuring we keep boolean false).
     *
     * @param array $frontMatter Array of front matter that hasn't been cleaned.
     *
     * @return array Cleaned and sorted front matter array.
     */
    private function cleanAndFormatFrontMatter(array $frontMatter = []): array
    {
        // Discard empty values in the array.
        $frontMatter = array_filter(
            $frontMatter,
            function ($entry) {
                // We explicitly allow all booleans otherwise empty below will
                // filter out any boolean false values.
                if (\is_bool($entry)) {
                    return true;
                }

                return !empty($entry);
            }
        );

        ksort($frontMatter);

        return $frontMatter;
    }

    /**
     * (over)writes the file with supplied content.
     *
     * @param string $content   Contents of a file
     * @param string $file      Name of the file to write
     * @param bool   $overwrite Whether to overwrite an existing file or not
     *
     * @return void
     */
    public function writeFile(
        string $content,
        string $file,
        bool $overwrite = false
    ): void {
        $containingDir = \dirname($file);

        if (!file_exists($containingDir)) {
            if (!mkdir($containingDir, 0777, true) && !is_dir($containingDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $containingDir));
            }
        }

        if (!$overwrite && file_exists($file)) {
            throw new \RuntimeException("'$file' already exists and overwrite was not enabled.");
        }

        file_put_contents($file, $content, LOCK_EX);

        return;
    }
}
