<?php

use App\Http\Controllers\Api\AddressApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::post('/admin/api/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (! Auth::guard('admin')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    return response()->json([
        'message' => 'Logged in',
        'user' => Auth::guard('admin')->user(),
    ]);
})->name('admin.api.login');

/*
|--------------------------------------------------------------------------
| Public Address API Routes (No Auth Required)
|--------------------------------------------------------------------------
*/

Route::prefix('api/addresses')->group(function () {
    Route::get('/', [AddressApiController::class, 'index']);
    Route::get('/children', [AddressApiController::class, 'children']);
    Route::get('/by-level/{level}', [AddressApiController::class, 'byLevel']);
    Route::get('/find/{code}', [AddressApiController::class, 'findByCode']);
    Route::get('/tree', [AddressApiController::class, 'tree']);
});
