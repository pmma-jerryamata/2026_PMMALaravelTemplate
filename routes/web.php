<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HelpdeskController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

use App\Mail\Mailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SupportController;
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
//Route::get('/student-2', [StudentController::class, 'indexViews'])->name('student-2');
Route::redirect('/student-2', '/student')->name('student-2');
//--------------------------------------------------------------------------------------//

Route::redirect('/test', '/dashboard')->middleware('auth');

Route::get('/', [HelpdeskController::class, 'home'])->name('home');

Route::get('/info', [HelpdeskController::class, 'create'])->name('info');

Route::view('/phone', 'helpdesk.portal.phone')->name('phone');

Route::redirect('/notify', '/tickets/create')->name('notify');

//Route::get('/index', [SupportController::class, 'view'])->name('support'); JAN 10 2025

//Route::view('/support', 'support')->name('support');

Route::get('/student', [LoginController::class, 'studentView'])->name('student');
Route::post('/student/store', [LoginController::class, 'studentStore'])->name('student.store');

Route::view('/success', 'success')->name('success');
Route::view('/failed', 'failed')->name('failed');

//Route::get('/support', [SupportController::class, 'view'])->name('support');

Route::redirect('/ticket', '/track')->name('ticket');

Route::redirect('/mailer', '/contact');

////////////////////////////  SURVERY - FORM  ////////////////////////////////////////
//Route::get('/take-survey', [SurveyController::class, 'surveyForm'])->name('take-survey');
Route::get('/take-survey', [SurveyController::class, 'surveyForm'])
    ->name('take-survey')
    ->middleware('signed'); // <-- Require signed URLs

Route::post('/survey', [SurveyController::class, 'store'])->middleware('signed')->name('survey');
Route::get('/survey', fn () => redirect()->route('home'))->name('survey.legacy');
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

    
    Route::get('/edit', [HelpdeskController::class, 'legacyEdit'])->name('edit');
    Route::post('/save', [SupportController::class, 'saveSpecificSupport'])->name('save');


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //////////////////removed to avoid relog in in /admin route///////////////////////////
    //Route::get('/admin', function () {
    //    return view('/auth/login');
    //});
    Route::redirect('/admin', '/dashboard')->name('admin');
    Route::redirect('/admin.php', '/dashboard');
    Route::redirect('/admin/index.php', '/dashboard');
    //////////////////////////////////////////////////////////////////////////////////////

    ////////////////SUPPORT MAIN PAGE/////////////////////////////////////////////////////
    Route::post('/support-admin', [SupportController::class, 'saveSupportAdmin'])->name('support-admin');
    Route::get('/support', [HelpdeskController::class, 'dashboard'])->name('support');
    Route::get('/report/jobs', [ReportController::class, 'jobs'])->name('report.jobs');
    Route::get('/report/jobs/pdf', [ReportController::class, 'jobsPdf'])->name('report.jobs.pdf');
    Route::match(['get', 'post'], '/report', [ReportController::class, 'index'])->name('report');
    Route::match(['get', 'post'], '/report/pdf', [ReportController::class, 'pdf'])->name('report.pdf');
    Route::get('/add', [HelpdeskController::class, 'create'])->name('add');

    ////////////////ADMIN CONFIGURATIONS - CONTROLLER/////////////////////////////////////
    Route::get('/admin/index', [HelpdeskController::class, 'dashboard'])->name('admin.index');
    Route::redirect('/admin/add-employee', '/dashboard/settings/employees/create')->name('admin.add-employee');
    //Route::view('/admin/index', 'admin.index')->name('admin.index');

    ////////////////TECHNICIAN MOBILE - CONTROLLER////////////////////////////////////////
    //Route::view('tech/index', 'tech.index')->name('tech.index');
    Route::get('/tech', [\App\Http\Controllers\TechnicianDashboardController::class, 'index'])->name('tech.index');
    Route::post('/tech/tickets/{ticket}', [HelpdeskController::class, 'update'])->whereNumber('ticket')->name('tech.tickets.update');

});
///////////////////////////////////////////////////////////////////////////////////////////////////
require __DIR__.'/auth.php';
require __DIR__.'/helpdesk.php';
Route::redirect('/admin/login', '/login');
