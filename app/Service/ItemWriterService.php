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
        ksort($frontMatter);

        $yamlFrontMatter = Yaml::dump($frontMatter);

        return <<<HEREDOC
---
$yamlFrontMatter
---
$content
HEREDOC;
    }
}
