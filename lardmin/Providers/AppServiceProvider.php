<?php

namespace Lardmin\Providers;

use Illuminate\Support\ServiceProvider;
use App;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Set views path
        $this->loadViewsFrom(__DIR__ . '/../Views', 'lardmin');

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        App::register('Lardmin\Providers\RouteServiceProvider');
    }
}
