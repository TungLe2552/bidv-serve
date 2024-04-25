<?php

use App\Http\Controllers\Api\Auth\AuthenticateController;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Auth'], function () {
    Route::post('register', [AuthenticateController::class, 'createUser']);
    Route::post('send-otp', [AuthenticateController::class, 'sendOtp']);
    Route::post('login', [AuthenticateController::class, 'login']);
    Route::get('occupations', [AuthenticateController::class, 'getOccupations']);
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('logout', [AuthenticateController::class, 'logout']);
        Route::post('info/{id}', [AuthenticateController::class, 'getInfo']);
    });
});
