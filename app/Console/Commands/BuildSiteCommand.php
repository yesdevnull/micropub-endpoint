<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Class RebuildSiteCommand
 */
class BuildSiteCommand extends Command
{
    protected $signature = 'site:build';

    protected $description = 'Rebuild the site';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $output = [];
        $returnCode = null;

        exec(env('SITE_BUILD_COMMAND'), $output, $returnCode);

        foreach ($output as $outputRow) {
            $this->info($outputRow);
        }

        return $returnCode;
    }
}
