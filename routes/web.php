<?php

use App\Http\Controllers\RegistrarContactController;
use App\Http\Controllers\TimeController;
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
Route::view('/qualification-requirements', 'registrar.qualifications')->name('qualifications');
Route::view('/contact', 'registrar.contact')->name('contact');
Route::post('/contact', RegistrarContactController::class)->middleware('throttle:5,1')->name('contact.send');
Route::get('/time', TimeController::class)->middleware('throttle:60,1,registrar-time')->name('time');
