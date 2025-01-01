<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DivisiController;
use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/login', [AuthController::class, 'login']);

// Endpoints
Route::middleware('auth.jwt')->group(function() {
    Route::post('/employees', [EmployeeController::class, 'addNewEmployee']);
    Route::get('/divisions', [DivisiController::class, 'getDivisions']);
    Route::get('/employees', [EmployeeController::class, 'getAllEmployeeData']);
    Route::put('/employees/{id}', [EmployeeController::class, 'updateEmployee']);
    Route::delete('/employees/{id}', [EmployeeController::class, 'deleteEmployee']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
