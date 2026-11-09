<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\TimeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Gate;

Gate::define('staff-dashboard', fn ($user) => (bool) $user->is_staff);
Gate::define('view-activity-logs', fn ($user) => (bool) $user->is_dev_admin);
Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:20,1')->name('login.submit');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware(['auth', 'can:staff-dashboard'])->group(function () {
    Route::get('/dashboard/tickets/data', [\App\Http\Controllers\TicketTableController::class, 'staffData'])->name('dashboard.tickets.data');
    Route::get('/dashboard/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->middleware('can:view-activity-logs')->name('dashboard.activity-logs');
    Route::get('/dashboard/activity-logs/data', [\App\Http\Controllers\ActivityLogController::class, 'data'])->middleware('can:view-activity-logs')->name('dashboard.activity-logs.data');
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');
    Route::get('/dashboard/settings', [SettingsController::class, 'index'])->name('dashboard.settings');
    Route::get('/dashboard/technicians/{technician}/photo', [SettingsController::class, 'photo'])->name('dashboard.technicians.photo');
    Route::post('/dashboard/settings/profile', [SettingsController::class, 'profile'])->name('dashboard.settings.profile');
    Route::post('/dashboard/settings/general', [SettingsController::class, 'general'])->name('dashboard.settings.general');
    Route::post('/dashboard/settings/{type}/{id?}', [SettingsController::class, 'save'])->whereIn('type', ['departments', 'technicians', 'services'])->name('dashboard.settings.save');
    Route::post('/dashboard/tickets', [DashboardController::class, 'store'])->name('dashboard.tickets.store');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/tickets/{ticket}', [DashboardController::class, 'show'])->name('dashboard.tickets.show');
    Route::patch('/dashboard/tickets/{ticket}', [DashboardController::class, 'update'])->name('dashboard.tickets.update');
    Route::delete('/dashboard/tickets/{ticket}', [DashboardController::class, 'destroy'])->name('dashboard.tickets.destroy');
    Route::post('/dashboard/tickets/{ticket}/action', [DashboardController::class, 'action'])->name('dashboard.tickets.action');
    Route::get('/dashboard/tickets/{ticket}/timeline', [DashboardController::class, 'timeline'])->name('dashboard.tickets.timeline');
});

Route::get('/time', TimeController::class)->middleware('throttle:60,1')->name('time');

Route::get('/', [PortalController::class, 'home'])->name('home');
Route::get('/services', [PortalController::class, 'services'])->name('services');
Route::get('/services/{slug}', [PortalController::class, 'service'])->name('services.show');
Route::get('/tickets/create', [PortalController::class, 'create'])->name('tickets.create');
Route::post('/tickets', [PortalController::class, 'store'])->middleware('throttle:10,1')->name('tickets.store');
Route::get('/tickets/receipt', [PortalController::class, 'receipt'])->name('tickets.receipt');
Route::get('/tickets/recent/data', [\App\Http\Controllers\TicketTableController::class, 'publicData'])->middleware('throttle:60,1')->name('tickets.recent.data');
Route::get('/tickets/{reference}/timeline', [PortalController::class, 'timeline'])->middleware('throttle:60,1')->name('tickets.timeline');
Route::match(['get', 'post'], '/track', [PortalController::class, 'track'])->middleware('throttle:20,1')->name('track');
Route::view('/faq', 'portal.faq')->name('faq');
Route::view('/contact', 'portal.contact')->name('contact');
Route::view('/privacy', 'portal.privacy')->name('privacy');
