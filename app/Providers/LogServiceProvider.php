<?php

namespace App\Providers;

use App\Repositories\LogRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class LogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {

        $this->app->singleton(LogRepository::class, function (Application $app) {
            return LogRepository::getInstance();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
