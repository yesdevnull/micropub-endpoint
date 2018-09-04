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

        app('log')->info('Building site...');

        exec(env('SITE_BUILD_COMMAND'), $output, $returnCode);

        foreach ($output as $outputRow) {
            $this->info($outputRow);
            app('log')->info($outputRow);
        }

        app('log')->info('Return code: '.$returnCode);

        return $returnCode;
    }
}
