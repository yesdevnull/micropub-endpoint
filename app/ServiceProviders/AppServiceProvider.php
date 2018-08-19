<?php

namespace App\ServiceProviders;

use App\Contracts\BlogProvider;
use App\Providers\HugoProvider;
use App\Service\MediaService;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;

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
            function (Application $app) {
                return new MediaService(
                    $app->make('filesystem'),
                    env('BASE_UPLOAD_PATH')
                );
            }
        );

        $this->app->bind(
            BlogProvider::class,
            HugoProvider::class
        );
    }
}
