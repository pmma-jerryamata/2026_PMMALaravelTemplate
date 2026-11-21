<?php

namespace App\Providers;

use App\Console\CreateHelpdeskStaff;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $registrar = config('registrar') ?? [];

        if (! isset($registrar['contact'], $registrar['government_columns'])) {
            config(['registrar' => $registrar + (require config_path('registrar.php'))]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->environment('testing') || ! config('helpdesk.demo')) {
            return;
        }

        $this->loadMigrationsFrom(database_path('helpdesk-demo'));
        $this->commands([CreateHelpdeskStaff::class]);

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
