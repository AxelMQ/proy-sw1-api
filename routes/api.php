<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\Api\FriendshipController;
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
Route::middleware('auth:sanctum')->get('user', [AuthController::class, 'getUser']);
Route::post('user-photo/{id}', [UserDataController::class, 'updatePhoto']);
Route::delete('user-photo/{id}', [UserDataController::class, 'deletePhoto']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('send-solicitud/{friend_id}', [FriendshipController::class, 'sendSolicitud']);
    Route::post('aceptar-solicitud/{id}', [FriendshipController::class, 'acceptSolicitud']);
    Route::post('rechazar-solicitud/{id}', [FriendshipController::class, 'rechazadoSolicitud']);
    Route::post('cancelar-solicitud/{id}', [FriendshipController::class, 'cancelarSolicitud']);
    Route::get('list-friends/{id}', [FriendshipController::class, 'listFriends']);
    Route::post('delete-friends/{id}', [FriendshipController::class, 'deleteFriend']);
    Route::get('search-users', [UserController::class, 'search']);
    Route::get('solicitudes', [FriendshipController::class, 'getSolicitudes']);
    Route::get('user/{id}/friendsCount', [FriendshipController::class, 'getFriendsCount']);
});
