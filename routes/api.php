<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\EmployeeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('admin-support', [SupportController::class, 'adminGetSupport'])->name('admin-support');
Route::get('admin-support-pending', [SupportController::class, 'adminGetSupportPending'])->name('admin-support-pending');
Route::get('admin-support-cancelled', [SupportController::class, 'adminGetSupportCancelled'])->name('admin-support-cancelled');
Route::get('admin-support-completed', [SupportController::class, 'adminGetSupportCompleted'])->name('admin-support-completed');


Route::get('support', [SupportController::class, 'getSupport'])->name('support');
Route::get('supportPending', [SupportController::class, 'getSupportPending'])->name('supportPending');
Route::get('supportPending2', [SupportController::class, 'getSupportPending2'])->name('supportPending2');
Route::get('supportCancelled', [SupportController::class, 'getSupportCancelled'])->name('supportCancelled');
Route::get('supportCompleted', [SupportController::class, 'getSupportCompleted'])->name('supportCompleted');
Route::get('supportUpdate', [SupportController::class, 'supportUpdate'])->name('supportUpdate');

Route::get('search', [EmployeeController::class, 'getEmployees'])->name('search');
Route::get('search-with-trashed', [EmployeeController::class, 'getEmployeesWithTrashed'])->name('search-with-trashed');
Route::get('select-employee', [EmployeeController::class, 'getEmployeesDepartment'])->name('select-employee');
Route::get('update', [EmployeeController::class, 'updateEmployee'])->name('update');
Route::get('get-departments', [EmployeeController::class, 'getDepartments'])->name('get-departments');
Route::get('add', [EmployeeController::class, 'createEmployee'])->name('add');
Route::get('remove', [EmployeeController::class, 'removeEmployee'])->name('remove');
//Route::get('support', [SupportController::class, 'getTechnician'])->name('technician');

Route::get('supportPendingTech', [SupportController::class, 'getSupportPendingTech'])->name('supportPendingTech');
Route::get('get-employees', [SupportController::class, 'getEmployees'])->name('get-employees');
Route::get('get-tasks', [SupportController::class, 'getTasks'])->name('get-tasks');
Route::get('create-ticket', [SupportController::class, 'createTicket'])->name('create-ticket');

Route::get('get-survey', [SurveyController::class, 'getSurvey'])->name('get-survey');
Route::get('get-survey-by-support', [SurveyController::class, 'getSurveyBySupport'])->name('get-survey-by-support');

URL::forceScheme('https');
