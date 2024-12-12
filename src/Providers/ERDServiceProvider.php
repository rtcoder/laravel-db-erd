<?php

namespace Rtcoder\LaravelERD\Providers;

use Illuminate\Support\ServiceProvider;
use Rtcoder\LaravelERD\Commands\GenerateERDCommand;
use Rtcoder\LaravelERD\Services\ERDGenerator;

class ERDServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-erd');
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/laravel-erd'),
        ], 'views');

        $this->publishes([
            __DIR__ . '/../config/erd.php' => config_path('erd.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([GenerateERDCommand::class]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/erd.php',
            'erd'
        );

        $this->app->singleton('erd-generator', function ($app) {
            return new ERDGenerator();
        });
    }
}
