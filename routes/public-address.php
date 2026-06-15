<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressPublicController;

Route::get('/public-address', [AddressPublicController::class, 'index']);
