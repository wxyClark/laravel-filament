<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Auth DDD Architecture Tests
|--------------------------------------------------------------------------
|
| 验证 Auth 模块的 DDD 分层边界：
| - Domain 层不依赖 Infrastructure/Http
| - Infrastructure 实现 Domain 接口
| - Application 层编排 Domain 对象
|
*/

describe('Auth DDD Layer Boundaries', function () {

    test('Domain layer has no Infrastructure dependencies', function () {
        expect('App\Domains\Auth')
            ->not->toUse([
                'App\Infrastructure',
                'App\Http',
                'Filament',
            ]);
    });

    test('Domain layer has no framework HTTP dependencies', function () {
        expect('App\Domains\Auth\Services')
            ->not->toUse([
                'Illuminate\Http',
                'App\Http',
            ]);
    });

    test('Infrastructure implements Domain interfaces', function () {
        expect('App\Infrastructure\Repositories\Eloquent\AdminRepository')
            ->toImplement('App\Domains\Auth\Repositories\AdminRepositoryInterface');
    });

    test('Application layer depends on Domain', function () {
        expect('App\Services\AuthService')
            ->toUse('App\Domains\Auth');
    });

    test('Application layer does not depend on Infrastructure', function () {
        expect('App\Services\AuthService')
            ->not->toUse('App\Infrastructure');
    });

    test('Controller depends on Application and Presentation layers only', function () {
        expect('App\Http\Controllers\Api\Auth')
            ->toUse([
                'App\Services',
                'App\Http\Requests',
                'App\Http\Resources',
            ])
            ->not->toUse([
                'App\Infrastructure',
                'App\Domains',
            ]);
    });

    test('Domain DTOs are readonly', function () {
        expect('App\Domains\Auth\Data')
            ->toBeReadonly();
    });

    test('Domain Repository is an interface', function () {
        expect('App\Domains\Auth\Repositories')
            ->toBeInterface();
    });
});
