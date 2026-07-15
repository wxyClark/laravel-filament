<?php

use App\Http\Controllers\AddressPublicController;
use Illuminate\Support\Facades\Route;

Route::get('/public-address', [AddressPublicController::class, 'index']);
