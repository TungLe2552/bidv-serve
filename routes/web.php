<?php

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

Route::get('/test', function () {
    // return view('welcome');
    return View::make('emails.operatingExpenses', [
        "data" => [
            "title" => 'test',
            "body" => [
                "name" => "TSOn oc cho",
                "employee_name" => 'Nguyễn Văn Chiến'
            ],
            "model" => [
                "updated_at" => "ngay bay gio",
                "id" => "007"
            ],
            "link" => "#"
        ]
    ]);
});
