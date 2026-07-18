<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__.'/../routes/web.php',
            __DIR__.'/../routes/public-address.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->group(base_path('routes/admin-api.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/address-api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'admin/api/*',
        ]);

        // API 请求日志中间件
        $middleware->alias([
            'request.log' => \App\Infrastructure\Http\Middleware\RequestLogging::class,
        ]);

        $middleware->group('api', [
            \App\Infrastructure\Http\Middleware\RequestLogging::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
