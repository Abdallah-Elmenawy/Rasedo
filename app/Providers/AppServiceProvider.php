<?php

namespace App\Providers;

use App\Models\PublicSettings\ActivityLog;
use App\Support\LegacyModelAliases;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        LegacyModelAliases::register();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFour();
        Paginator::defaultView('pagination.rasedo');
        Paginator::defaultSimpleView('pagination.rasedo');

        View::composer('layouts.main-header', function ($view) {
            try {
                $view->with('recentLogs', ActivityLog::with('user')->latest()->limit(6)->get());
            } catch (\Throwable) {
                $view->with('recentLogs', collect());
            }
        });
    }
}
