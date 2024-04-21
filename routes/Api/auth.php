<?php

use App\Http\Controllers\Api\Auth\AuthenticateController;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Auth'], function () {
    Route::post('register', [AuthenticateController::class, 'createUser']);
    Route::post('info', [AuthenticateController::class, 'getInfo']);
    Route::post('send-otp', [AuthenticateController::class, 'sendOtp']);
    Route::post('login', [AuthenticateController::class, 'login']);
    Route::post('transaction', [AuthenticateController::class, 'bankTransactions']);
    Route::post('transaction-data', [AuthenticateController::class, 'transactionData']);
});
