<?php

namespace App\ServiceProviders;

use App\Contracts\BlogProvider;
use App\Providers\HugoProvider;
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

        $this->app->bind(
            BlogProvider::class,
            HugoProvider::class
        );
    }
}
