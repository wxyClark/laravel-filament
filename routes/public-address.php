<?php

declare(strict_types=1);

use App\Http\Controllers\AddressPublicController;
use Illuminate\Support\Facades\Route;

Route::get('/public-address', [AddressPublicController::class, 'index'])
    ->middleware('throttle:30,1');
