<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Illuminate\Support\Facades\View::composer(['helpdesk.*', 'auth.login'], function ($view) {
            $view->with('systemSettings', config('helpdesk'));
            $view->with('designVersion', max(
                filemtime(public_path('helpdesk/css/legacy-adapter.css')),
                filemtime(public_path('helpdesk/js/legacy-adapter.js')),
                filemtime(public_path('helpdesk/js/dashboard-tables.js')),
                filemtime(public_path('helpdesk/css/dashboard-tables.css'))
            ));
        });
    }
}
