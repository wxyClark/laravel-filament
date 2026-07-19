<?php

declare(strict_types=1);

use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

require __DIR__.'/auth.php';

// 数据导出 API（需要 session 认证，必须在 web 中间件组下）
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
