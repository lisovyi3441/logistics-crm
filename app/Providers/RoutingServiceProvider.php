<?php

namespace App\Providers;

use App\Services\Routing\OsrmRoutingService;
use App\Services\Routing\RoutingServiceInterface;
use Illuminate\Support\ServiceProvider;

class RoutingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(RoutingServiceInterface::class, OsrmRoutingService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
