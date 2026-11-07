<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Mail\Mailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\StudentController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/feedback', function () {
    return redirect()->away('https://forms.office.com/Pages/ResponsePage.aspx?id=E0WmHamrN02JqCK44TqgIe1qJWIZnidOmO_BtNyPcx5UNDRJQlVMV1cwVlc3MTJBSjdVSk5XWVpaVCQlQCN0PWcu');
});

//---CADETS-----------------------------------------------------------------------------//
Route::get('/student', [StudentController::class, 'indexViews'])->name('student');
//Route::get('/student-2', [StudentController::class, 'indexViews'])->name('student-2');
Route::get('/student-2', [StudentController::class, 'indexViews2'])->name('student-2');
//--------------------------------------------------------------------------------------//

Route::get('/test', [Controller::class, 'employeeSupport']);

Route::get('/', [LoginController::class, 'indexViews'])->name('index');

Route::get('/info', [LoginController::class, 'jobRequest'])->name('info');

Route::view('/phone', 'phone')->name('phone');

Route::get('/notify', [SupportController::class, 'notifyUser'])->name('notify');

//Route::get('/index', [SupportController::class, 'view'])->name('support'); JAN 10 2025

//Route::view('/support', 'support')->name('support');

Route::get('/student', [LoginController::class, 'studentView'])->name('student');
Route::post('/student/store', [LoginController::class, 'studentStore'])->name('student.store');

Route::view('/success', 'success')->name('success');
Route::view('/failed', 'failed')->name('failed');

//Route::get('/support', [SupportController::class, 'view'])->name('support');

Route::get('/ticket', [SupportController::class, 'ticket'])->name('ticket');

Route::get('/mailer', function (){
    Mail::to('iversehmy.44@gmail.com')
        ->send(new Mailer());
    //return view('mailer');
});

////////////////////////////  SURVERY - FORM  ////////////////////////////////////////
//Route::get('/take-survey', [SurveyController::class, 'surveyForm'])->name('take-survey');
Route::get('/take-survey', [SurveyController::class, 'surveyForm'])
    ->name('take-survey')
    ->middleware('signed'); // <-- Require signed URLs

Route::get('/survey', [SurveyController::class, 'store'])->name('survey');
Route::view('done', 'survey.done')->name('done');



/////////////////////////AUTHENTICATION ROUTES/////////////////////////////////////////////////////
//Route::get('/', function () {
//    return view('/support');
//});

//Route::get('/admin', function () {
//    return view('/auth/login');
//});
//Route::get('/dashboard', function () {
//    return view('dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    
    Route::get('/edit', [SupportController::class, 'getSpecificSupport'])->name('edit');
    Route::post('/save', [SupportController::class, 'saveSpecificSupport'])->name('save');
    Route::get('/add', [SupportController::class, 'addSupport'])->name('add');


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //////////////////removed to avoid relog in in /admin route///////////////////////////
    //Route::get('/admin', function () {
    //    return view('/auth/login');
    //});
    Route::view('/admin', 'admin.index');
    //////////////////////////////////////////////////////////////////////////////////////

    ////////////////SUPPORT MAIN PAGE/////////////////////////////////////////////////////
    Route::post('/support-admin', [SupportController::class, 'saveSupportAdmin'])->name('support-admin');
    Route::view('/support', 'support')->name('support');
    Route::get('/report', [SupportController::class, 'reportSupportAdmin'])->name('report');
    Route::get('/add', [SupportController::class, 'addSupport'])->name('add');

    ////////////////ADMIN CONFIGURATIONS - CONTROLLER/////////////////////////////////////
    Route::view('/admin/index', 'admin.index')->name('admin.index');
    Route::view('/admin/add-employee', 'admin.add-employee')->name('admin.add-employee');
    //Route::view('/admin/index', 'admin.index')->name('admin.index');

    ////////////////TECHNICIAN MOBILE - CONTROLLER////////////////////////////////////////
    //Route::view('tech/index', 'tech.index')->name('tech.index');
    Route::view('/tech', 'layouts.maintech')->name('tech.index');

});
///////////////////////////////////////////////////////////////////////////////////////////////////
require __DIR__.'/auth.php';