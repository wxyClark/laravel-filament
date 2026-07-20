<?php

declare(strict_types=1);

use App\Domains\Auth\Data\AuthResult;
use App\Domains\Auth\Repositories\AdminRepositoryInterface;
use App\Models\Admin;
use App\Services\AuthService;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| AuthService Unit Tests
|--------------------------------------------------------------------------
|
| 测试 AuthService 的业务逻辑：注册、登录、登出、刷新
| 使用 Mock 模拟 Repository，不依赖数据库
|
*/

beforeEach(function () {
    $this->repository = mock(AdminRepositoryInterface::class);
    $this->service = new AuthService($this->repository);
});

describe('AuthService', function () {

    describe('register', function () {

        test('register creates admin and returns auth result', function () {
            $data = [
                'name' => 'Test Admin',
                'email' => 'test@example.com',
                'password' => 'password123',
            ];

            $admin = Admin::factory()->make([
                'id' => 1,
                'name' => 'Test Admin',
                'email' => 'test@example.com',
            ]);

            $this->repository->shouldReceive('emailExists')
                ->with('test@example.com')
                ->andReturn(false);

            $this->repository->shouldReceive('create')
                ->with([
                    'name' => 'Test Admin',
                    'email' => 'test@example.com',
                    'password' => 'password123',
                ])
                ->andReturn($admin);

            $result = $this->service->register($data);

            expect($result)->toBeInstanceOf(AuthResult::class);
            expect($result->admin)->toBe($admin);
            expect($result->token)->toBeString();
            expect($result->expiresIn)->toBeInt();
        });

        test('register throws exception for duplicate email', function () {
            $data = [
                'name' => 'Test Admin',
                'email' => 'existing@example.com',
                'password' => 'password123',
            ];

            $this->repository->shouldReceive('emailExists')
                ->with('existing@example.com')
                ->andReturn(true);

            $this->service->register($data);
        })->throws(ValidationException::class);
    });

    describe('login', function () {

        test('login returns auth result for valid credentials', function () {
            $data = [
                'email' => 'test@example.com',
                'password' => 'password123',
            ];

            $admin = Admin::factory()->make([
                'id' => 1,
                'email' => 'test@example.com',
                'password' => bcrypt('password123'),
            ]);

            $this->repository->shouldReceive('findByEmail')
                ->with('test@example.com')
                ->andReturn($admin);

            $result = $this->service->login($data);

            expect($result)->toBeInstanceOf(AuthResult::class);
            expect($result->admin)->toBe($admin);
        });

        test('login throws exception for wrong password', function () {
            $data = [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ];

            $admin = Admin::factory()->make([
                'id' => 1,
                'email' => 'test@example.com',
                'password' => bcrypt('password123'),
            ]);

            $this->repository->shouldReceive('findByEmail')
                ->with('test@example.com')
                ->andReturn($admin);

            $this->service->login($data);
        })->throws(ValidationException::class);

        test('login throws exception for non-existent email', function () {
            $data = [
                'email' => 'nonexistent@example.com',
                'password' => 'password123',
            ];

            $this->repository->shouldReceive('findByEmail')
                ->with('nonexistent@example.com')
                ->andReturn(null);

            $this->service->login($data);
        })->throws(ValidationException::class);
    });
});
