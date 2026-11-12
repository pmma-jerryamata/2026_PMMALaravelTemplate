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
        \Illuminate\Support\Facades\RateLimiter::for('submissions', function (\Illuminate\Http\Request $request) {
            $identity = $request->user() ? 'user:'.$request->user()->id : 'session:'.$request->session()->getId();
            return [
                \Illuminate\Cache\RateLimiting\Limit::perMinutes(config('submissions.minutes'), config('submissions.attempts'))->by($identity),
                \Illuminate\Cache\RateLimiting\Limit::perMinutes(config('submissions.minutes'), config('submissions.shared_ip_attempts'))->by('shared-ip:'.$request->ip()),
            ];
        });
        \Illuminate\Support\Facades\RateLimiter::for('lookups', fn (\Illuminate\Http\Request $request) =>
            \Illuminate\Cache\RateLimiting\Limit::perMinute(config('submissions.lookup_attempts'))->by($request->user()?->id ?? $request->session()->getId()));
        \Illuminate\Support\Facades\View::composer(['layouts.portal', 'layouts.auth', 'layouts.dashboard', 'auth.login', 'dashboard.settings'], function ($view) {
            $view->with('systemSettings', \App\Models\SystemSetting::values());
        });
    }
}
