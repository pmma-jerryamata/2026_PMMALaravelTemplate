<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        \Illuminate\Support\Facades\View::composer(['layouts.portal', 'layouts.auth', 'layouts.dashboard', 'auth.login', 'dashboard.settings'], function ($view) {
            $view->with('systemSettings', \App\Models\SystemSetting::values());
        });
    }
}
