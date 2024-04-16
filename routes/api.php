<?php

use Illuminate\Support\Facades\Route;


Route::group([
    'namespace' => 'Api',
], function () {
    includeRouteFiles(__DIR__ . '/Api/');
});
