<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AddressController;
use Illuminate\Support\Facades\Route;

Route::prefix('addresses')->group(function () {
    Route::get('/', [AddressController::class, 'index']);
    Route::get('/tree', [AddressController::class, 'tree']);
    Route::get('/by-level/{level}', [AddressController::class, 'byLevel']);
    Route::get('/find/{code}', [AddressController::class, 'findByCode']);
});
