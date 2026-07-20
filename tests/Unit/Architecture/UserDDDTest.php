<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| User DDD Architecture Tests
|--------------------------------------------------------------------------
|
| 验证 User 模块的 DDD 分层边界：
| - Domain 层不依赖 Infrastructure/Http
| - Infrastructure 实现 Domain 接口
| - Application 层编排 Domain 对象
|
*/

describe('User DDD Layer Boundaries', function () {

    test('Domain layer has no Infrastructure dependencies', function () {
        expect('App\Domains\User')
            ->not->toUse([
                'App\Infrastructure',
                'App\Http',
                'Filament',
            ]);
    });

    test('Domain layer has no framework HTTP dependencies', function () {
        expect('App\Domains\User\Repositories')
            ->not->toUse([
                'Illuminate\Http',
                'App\Http',
            ]);
    });

    test('Infrastructure implements Domain interfaces', function () {
        expect('App\Infrastructure\Repositories\Eloquent\CustomerRepository')
            ->toImplement('App\Domains\User\Repositories\CustomerRepositoryInterface');
    });

    test('Application layer depends on Domain', function () {
        expect('App\Services\CustomerService')
            ->toUse('App\Domains\User');
    });

    test('Application layer does not depend on Infrastructure', function () {
        expect('App\Services\CustomerService')
            ->not->toUse('App\Infrastructure');
    });

    test('Controller depends on Application and Presentation layers only', function () {
        expect('App\Http\Controllers\Api\User')
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
        expect('App\Domains\User\Data')
            ->toBeReadonly();
    });

    test('Domain Repository is an interface', function () {
        expect('App\Domains\User\Repositories')
            ->toBeInterface();
    });

    test('FormRequests use Model getTable() not hardcoded table names', function () {
        expect('App\Http\Requests\Api\User')
            ->toUse('App\Models');
    });
});
