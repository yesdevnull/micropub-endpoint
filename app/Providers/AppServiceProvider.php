<?php

namespace App\Providers;

use App\Service\MediaService;
use Illuminate\Support\ServiceProvider;

/**
 * Class AppServiceProvider
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(
            MediaService::class,
            function () {
                return new MediaService(
                    'media'
                );
            }
        );
    }
}
