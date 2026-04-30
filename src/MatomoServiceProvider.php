<?php

namespace FyrnDly\Matomo;

use Illuminate\Support\ServiceProvider;

class MatomoServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // Merge package configuration with application configuration
        $this->mergeConfigFrom(__DIR__.'/../config/matomo.php', 'matomo');

        // Register the service as a singleton in the container
        $this->app->singleton(MatomoService::class, function ($app) {
            return new MatomoService($app['config']['matomo']);
        });

        // Register an alias for easier access
        $this->app->alias(MatomoService::class, 'matomo-tracker');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Allow users to publish the configuration file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/matomo.php' => config_path('matomo.php'),
            ], 'matomo-config');
        }
    }
}