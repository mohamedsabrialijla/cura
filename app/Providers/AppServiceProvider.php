<?php

namespace App\Providers;

use App\Http\Middleware\AuthenticateAdmin;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
            Schema::defaultStringLength(191);

            Vite::createAssetPathsUsing(function (string $path, ?bool $secure = null) {
                return '/' . ltrim($path, '/');
            });

            if (! $this->app->environment('local')) {
                URL::forceRootUrl(config('app.url'));
            }

            $router = $this->app->make('router');
            if (method_exists($router, 'aliasMiddleware')) {
                $router->aliasMiddleware('auth.admin', AuthenticateAdmin::class);
            }

            // if (app()->environment('local')) {
            //     URL::forceScheme('https');
            // }

    }
}
