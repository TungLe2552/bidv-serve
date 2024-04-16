<?php

use App\Http\Controllers\Api\Export\ExportCustomController;
use App\Module\Export\Controllers\ExportFeatureController;
use Illuminate\Support\Facades\Route;

Route::post('models/{table_id}/export', [ExportFeatureController::class, 'export']);
Route::post('models/{table_id}/export/giam-sat', [ExportCustomController::class, 'exportGiamSat']);
Route::post('models/{table_id}/export/danh-sach', [ExportCustomController::class, 'exportDSYC']);
Route::post('models/{table_id}/export/customer-resource', [ExportCustomController::class, 'exportResourceCustomer']);

