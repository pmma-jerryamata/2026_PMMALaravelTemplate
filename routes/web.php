<?php

use App\Http\Middleware\LimitDatabaseConcurrency;
use App\Http\Middleware\RecordActivity;
use Illuminate\Support\Facades\Route;

if (app()->environment('testing') && config('helpdesk.demo')) {
    Route::middleware([LimitDatabaseConcurrency::class, RecordActivity::class])
        ->group(__DIR__.'/helpdesk-demo.php');

    return;
}

Route::view('/', 'registrar.home')->name('home');
Route::view('/services', 'registrar.services')->name('services');
Route::view('/faq', 'registrar.faq')->name('faq');
