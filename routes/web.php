<?php

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\AppointmentStatusController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\admin\userController;
use App\Http\Controllers\ApplicationController;
use App\Models\Appointment;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

/*
Route::get('/admin/dashboard', function(){
    return view('dashboard');
}); */


Route::get('/api/users', [userController::class, 'index']);
Route::post('/api/users', [userController::class, 'store']);
//Route::get('/api/users/search', [userController::class, 'search']);
Route::patch('/api/users/{user}/change-role', [userController::class, 'changeRole']);
Route::put('/api/users/{user}', [userController::class, 'update']);
Route::delete('/api/users/{user}', [userController::class, 'destory']);
Route::delete('/api/users', [userController::class, 'bulkDelete']);


Route::get('/api/clients', [ClientController::class, 'index']);

Route::get('/api/appointment-status', [AppointmentStatusController::class, 'getStatusWithCount']);
Route::get('/api/appointments', [AppointmentController::class, 'index']);
Route::post('/api/appointments/create', [AppointmentController::class, 'store']);
Route::get('/api/appointments/{appointment}/edit', [AppointmentController::class, 'edit']);
Route::put('/api/appointments/{appointment}/edit', [AppointmentController::class, 'update']);
Route::delete('/api/appointments/{appointment}/', [AppointmentController::class, 'destroy']);


Route::get('{view}', ApplicationController::class)->where('view', '(.*)');
