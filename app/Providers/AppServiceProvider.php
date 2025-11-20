<?php

namespace App\Providers;

use App\Repositories\Recipe\RecipeRepository;
use App\Repositories\Recipe\RecipeRepositoryInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RecipeRepositoryInterface::class, RecipeRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(RateLimiter $rateLimit): void
    {
        $rateLimit->for('simpleLog', function (Request $request) {
            return Limit::perMinute(1)
                ->by($request->ip());
        });

        // RateLimit open routes
        $rateLimit->for('open-api-routes', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->ip());
        });
    }
}
