<?php

use App\Http\Controllers\HelpdeskController;
use App\Http\Controllers\DashboardTableController;
use Illuminate\Support\Facades\Route;

Route::redirect('/index', '/')->name('index');
Route::get('/services', [HelpdeskController::class, 'servicesIndex'])->name('services');
Route::get('/services/{slug}', [HelpdeskController::class, 'service'])->whereNumber('slug')->name('services.show');
Route::get('/tickets/create', [HelpdeskController::class, 'create'])->name('tickets.create');
Route::post('/tickets/requester', [HelpdeskController::class, 'requester'])->middleware('throttle:20,1')->name('tickets.requester');
Route::post('/tickets', [HelpdeskController::class, 'store'])->middleware('throttle:10,1')->name('tickets.store');
Route::get('/tickets/receipt', [HelpdeskController::class, 'receipt'])->name('tickets.receipt');
Route::match(['get', 'post'], '/track', [HelpdeskController::class, 'track'])->middleware('throttle:30,1')->name('track');
Route::get('/queue', [HelpdeskController::class, 'queue'])->name('queue');
Route::view('/faq', 'helpdesk.portal.faq')->name('faq');
Route::view('/contact', 'helpdesk.portal.contact')->name('contact');
Route::view('/privacy', 'helpdesk.portal.privacy')->name('privacy');
Route::get('/time', fn () => response()->json(['timestamp' => (int) round(microtime(true) * 1000), 'synced' => false])->header('Cache-Control', 'no-store'))->name('time');

Route::middleware('auth')->group(function () {
    Route::view('/dashboard/user-guide', 'helpdesk.dashboard.user-guide')->name('dashboard.user-guide');
    Route::get('/dashboard/tables/tickets', [DashboardTableController::class, 'tickets'])->name('dashboard.tables.tickets');
    Route::get('/dashboard/tables/catalog/{type}', [DashboardTableController::class, 'catalog'])
        ->where('type', 'employees|departments|technicians|services')->name('dashboard.tables.catalog');
    Route::get('/dashboard/tables/surveys', [DashboardTableController::class, 'surveys'])->name('dashboard.tables.surveys');
    Route::get('/dashboard', [HelpdeskController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/tickets/create', [HelpdeskController::class, 'create'])->name('dashboard.tickets.create');
    Route::post('/dashboard/tickets', [HelpdeskController::class, 'store'])->name('dashboard.tickets.store');
    Route::get('/dashboard/tickets/{ticket}', [HelpdeskController::class, 'show'])->whereNumber('ticket')->name('dashboard.tickets.show');
    Route::get('/dashboard/tickets/{ticket}/delete', [HelpdeskController::class, 'confirmDelete'])->whereNumber('ticket')->name('dashboard.tickets.confirm-delete');
    Route::delete('/dashboard/tickets/{ticket}', [HelpdeskController::class, 'destroy'])->whereNumber('ticket')->middleware('throttle:5,1')->name('dashboard.tickets.destroy');
    Route::post('/dashboard/tickets/{ticket}', [HelpdeskController::class, 'update'])->whereNumber('ticket')->name('dashboard.tickets.update');
    Route::get('/dashboard/settings', [HelpdeskController::class, 'settings'])->name('dashboard.settings');
    Route::get('/dashboard/settings/{type}/create', [HelpdeskController::class, 'editRecord'])->name('dashboard.settings.create');
    Route::get('/dashboard/settings/{type}/{id}', [HelpdeskController::class, 'editRecord'])->whereNumber('id')->name('dashboard.settings.edit');
    Route::post('/dashboard/settings/{type}/{id?}', [HelpdeskController::class, 'saveRecord'])->whereNumber('id')->name('dashboard.settings.save');
    Route::get('/dashboard/queue', [HelpdeskController::class, 'queue'])->name('dashboard.queue');
    Route::get('/dashboard/surveys', [HelpdeskController::class, 'surveyResults'])->name('dashboard.surveys');
});
