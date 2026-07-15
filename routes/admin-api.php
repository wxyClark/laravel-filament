<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AddressApiController;
use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API Routes — JWT Authentication
|--------------------------------------------------------------------------
|
| 公开路由：注册、登录
| 需认证路由：退出、刷新、个人信息
|
*/

Route::prefix('admin/api')->group(function () {

    // 公开路由（无需认证）
    Route::post('/register', [AuthController::class, 'register'])
        ->name('admin.api.register');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('admin.api.login');

    // 需要 JWT 认证的路由
    Route::middleware('auth:admin-api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('admin.api.logout');

        Route::post('/refresh', [AuthController::class, 'refresh'])
            ->name('admin.api.refresh');

        Route::get('/me', [AuthController::class, 'me'])
            ->name('admin.api.me');
    });

    // 公开地址 API（无需认证）
    Route::prefix('addresses')->group(function () {
        Route::get('/', [AddressApiController::class, 'index']);
        Route::get('/children', [AddressApiController::class, 'children']);
        Route::get('/by-level/{level}', [AddressApiController::class, 'byLevel']);
        Route::get('/find/{code}', [AddressApiController::class, 'findByCode']);
        Route::get('/tree', [AddressApiController::class, 'tree']);
    });
});
