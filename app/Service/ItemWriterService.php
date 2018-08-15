<?php

namespace App\Service;

use Symfony\Component\Yaml\Yaml;

/**
 * Class ItemWriterService
 */
class ItemWriterService
{
    public function build(
        array $frontMatter,
        string $content
    ): string {
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

        $yamlFrontMatter = Yaml::dump($frontMatter);

        // Yaml::dump has a trailing new line, so the ending front matter delimiter
        // is on the same line so we avoid a blank line.
        return <<<HEREDOC
---
$yamlFrontMatter---
$content
HEREDOC;
    }
}
