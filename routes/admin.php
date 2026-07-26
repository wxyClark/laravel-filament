<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| 管理后台路由 (admin.example.com)
|--------------------------------------------------------------------------
|
| 管理后台的路由配置
|
*/

use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

// 数据导出 API（需要 session 认证）
Route::middleware('auth:admin')->prefix('admin/api/export')->group(function () {
    Route::post('/address', [ExportController::class, 'exportAddress'])
        ->name('admin.api.export.address');

    Route::get('/status', [ExportController::class, 'checkExportStatus'])
        ->name('admin.api.export.status');

    Route::get('/download/{filePath}', [ExportController::class, 'downloadExport'])
        ->where('filePath', '.*')
        ->name('admin.api.export.download');

    Route::post('/sync', [ExportController::class, 'syncExport'])
        ->name('admin.api.export.sync');
});

// 数据导入 API（需要 session 认证）
Route::middleware('auth:admin')->prefix('admin/api/import')->group(function () {
    Route::post('/product', [ImportController::class, 'importProducts'])
        ->name('admin.api.import.product');

    Route::post('/order', [ImportController::class, 'importOrders'])
        ->name('admin.api.import.order');

    Route::get('/product/template', [ImportController::class, 'downloadProductTemplate'])
        ->name('admin.api.import.product.template');

    Route::get('/order/template', [ImportController::class, 'downloadOrderTemplate'])
        ->name('admin.api.import.order.template');
});
