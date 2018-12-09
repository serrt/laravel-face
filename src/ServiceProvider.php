<?php

namespace Serrt\LaravelFace;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/face.php' => config_path('face.php'),
        ]);
    }

    public function register()
    {
        $this->app->singleton('face.plus', function () {
            return new FacePlusService($this->app['config']['face.plus']);
        });
    }
}