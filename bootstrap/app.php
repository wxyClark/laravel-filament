<?php

use App\Infrastructure\Http\Middleware\RequestLogging;
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
            // JWT 前台接口
            Route::middleware('api')
                ->group(base_path('routes/api.php'));

            // 公开接口（无需认证）
            Route::middleware('api')
                ->group(base_path('routes/open.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'open/*',
        ]);

        // API 请求日志中间件
        $middleware->alias([
            'request.log' => RequestLogging::class,
        ]);

        $middleware->group('api', [
            RequestLogging::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
