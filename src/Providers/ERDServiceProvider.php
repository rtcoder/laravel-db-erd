<?php
namespace Rtcoder\LaravelERD\Providers;

use Illuminate\Support\ServiceProvider;
use Rtcoder\LaravelERD\Commands\GenerateERDCommand;

class ERDServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/erd.php' => config_path('erd.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([GenerateERDCommand::class]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/erd.php',
            'erd'
        );
    }
}
