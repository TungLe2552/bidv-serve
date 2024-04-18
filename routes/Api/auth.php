<?php

use App\Http\Controllers\Api\Auth\AuthenticateController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\Auth\ProfileMenuController;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Auth'], function () {
    Route::post('register', [AuthenticateController::class, 'createUser']);
    Route::post('info', [AuthenticateController::class, 'getInfo']);
    Route::post('send-otp', [AuthenticateController::class, 'sendOtp']);
    Route::post('login', [AuthenticateController::class, 'login']);

});
