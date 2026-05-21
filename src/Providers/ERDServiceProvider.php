<?php

declare(strict_types=1);

namespace Rtcoder\LaravelERD\Providers;

use Illuminate\Support\ServiceProvider;
use Rtcoder\LaravelERD\Commands\DoctorERDCommand;
use Rtcoder\LaravelERD\Commands\GenerateERDCommand;
use Rtcoder\LaravelERD\Services\ERDGenerator;

class ERDServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-erd');
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/laravel-erd'),
        ], 'db-erd-views');
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/laravel-erd'),
        ], 'views');

        $this->publishes([
            __DIR__ . '/../config/erd.php' => config_path('erd.php'),
        ], 'db-erd-config');
        $this->publishes([
            __DIR__ . '/../config/erd.php' => config_path('erd.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DoctorERDCommand::class,
                GenerateERDCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/erd.php',
            'erd'
        );

        $this->app->singleton('erd-generator', function (): ERDGenerator {
            return new ERDGenerator();
        });
    }
}
