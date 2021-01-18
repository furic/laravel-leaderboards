<?php

namespace Furic\Leaderboards;

use Illuminate\Support\ServiceProvider;

class LeaderboardsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->publishes([
        //     __DIR__ . '/../config/leaderboards.php' => config_path('leaderboards.php'),
        // ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('Furic\Leaderboards\Http\Controllers\LeaderboardController');
        // $this->mergeConfigFrom(
        //     __DIR__ . '/../config/leaderboards.php', 'leaderboards'
        // );
    }
}