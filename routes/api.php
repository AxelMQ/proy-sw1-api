<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserDataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('user-register', [UserController::class, 'register']);
Route::get('user-all', [UserController::class, 'allUsers']);
Route::delete('user/{id}', [UserController::class, 'delete']);

Route::post('userdata-register', [UserDataController::class, 'register']);
Route::get('users-data-all', [UserDataController::class, 'allData']);
Route::delete('user-data/{id}', [UserDataController::class, 'delete']);
Route::put('user-data/{id}', [UserDataController::class, 'update']);

Route::post('login', [AuthController::class, 'login']);