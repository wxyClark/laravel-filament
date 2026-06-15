<?php

use App\Http\Controllers\Api\AddressApiController;
use Illuminate\Support\Facades\Route;

// 公开访问的地址API接口（无需认证）
Route::prefix('api/addresses')->group(function () {
    Route::get('/', [AddressApiController::class, 'index']);
    Route::get('/children', [AddressApiController::class, 'children']);
    Route::get('/by-level/{level}', [AddressApiController::class, 'byLevel']);
    Route::get('/find/{code}', [AddressApiController::class, 'findByCode']);
    Route::get('/tree', [AddressApiController::class, 'tree']);
});
