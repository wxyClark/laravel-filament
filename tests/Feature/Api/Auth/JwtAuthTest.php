<?php

declare(strict_types=1);

use App\Models\Admin;
use Tymon\JWTAuth\Facades\JWTAuth;

/*
|--------------------------------------------------------------------------
| JWT Authentication API Tests
|--------------------------------------------------------------------------
|
| 测试管理员 JWT 认证流程：注册、登录、获取信息、刷新、退出
|
*/

beforeEach(function () {
    $this->admin = Admin::factory()->create([
        'password' => bcrypt('password123'),
    ]);
});

describe('Admin JWT Auth API', function () {

    describe('POST /admin/api/register', function () {

        test('admin can register with valid data', function () {
            $response = $this->postJson('/admin/api/register', [
                'name' => 'Test Admin',
                'email' => 'newadmin@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'token_type',
                    'expires_in',
                ])
                ->assertJson([
                    'message' => '注册成功',
                    'token_type' => 'bearer',
                ]);

            $this->assertDatabaseHas('admins', ['email' => 'newadmin@example.com']);
        });

        test('register fails with duplicate email', function () {
            $response = $this->postJson('/admin/api/register', [
                'name' => 'Duplicate Admin',
                'email' => $this->admin->email,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors('email');
        });

        test('register fails with invalid email', function () {
            $response = $this->postJson('/admin/api/register', [
                'name' => 'Test',
                'email' => 'not-an-email',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors('email');
        });

        test('register fails with short password', function () {
            $response = $this->postJson('/admin/api/register', [
                'name' => 'Test',
                'email' => 'test@example.com',
                'password' => '123',
                'password_confirmation' => '123',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors('password');
        });

        test('register fails without password confirmation', function () {
            $response = $this->postJson('/admin/api/register', [
                'name' => 'Test',
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors('password');
        });
    });

    describe('POST /admin/api/login', function () {

        test('admin can login with valid credentials', function () {
            $response = $this->postJson('/admin/api/login', [
                'email' => $this->admin->email,
                'password' => 'password123',
            ]);

            $response->assertOk()
                ->assertJsonStructure([
                    'message',
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'token_type',
                    'expires_in',
                ])
                ->assertJson([
                    'message' => '登录成功',
                    'token_type' => 'bearer',
                ]);
        });

        test('login fails with wrong password', function () {
            $response = $this->postJson('/admin/api/login', [
                'email' => $this->admin->email,
                'password' => 'wrong-password',
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });

        test('login fails with non-existent email', function () {
            $response = $this->postJson('/admin/api/login', [
                'email' => 'nonexistent@example.com',
                'password' => 'password123',
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });

        test('login fails without email', function () {
            $response = $this->postJson('/admin/api/login', [
                'password' => 'password123',
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        });

        test('login fails without password', function () {
            $response = $this->postJson('/admin/api/login', [
                'email' => $this->admin->email,
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors('password');
        });
    });

    describe('GET /admin/api/me', function () {

        test('authenticated admin can get profile', function () {
            $token = JWTAuth::fromUser($this->admin);

            $response = $this->withHeader('Authorization', 'Bearer '.$token)
                ->getJson('/admin/api/me');

            $response->assertOk()
                ->assertJsonPath('data.email', $this->admin->email)
                ->assertJsonPath('data.name', $this->admin->name);
        });

        test('unauthenticated request is rejected', function () {
            $response = $this->getJson('/admin/api/me');

            $response->assertUnauthorized();
        });

        test('invalid token is rejected', function () {
            $response = $this->withHeader('Authorization', 'Bearer invalid-token-123')
                ->getJson('/admin/api/me');

            $response->assertUnauthorized();
        });
    });

    describe('POST /admin/api/refresh', function () {

        test('authenticated admin can refresh token', function () {
            $token = JWTAuth::fromUser($this->admin);

            $response = $this->withHeader('Authorization', 'Bearer '.$token)
                ->postJson('/admin/api/refresh');

            $response->assertOk()
                ->assertJsonStructure([
                    'message',
                    'token',
                    'token_type',
                    'expires_in',
                ]);
        });

        test('refresh fails without token', function () {
            $response = $this->postJson('/admin/api/refresh');

            $response->assertUnauthorized();
        });
    });

    describe('POST /admin/api/logout', function () {

        test('authenticated admin can logout', function () {
            $token = JWTAuth::fromUser($this->admin);

            $response = $this->withHeader('Authorization', 'Bearer '.$token)
                ->postJson('/admin/api/logout');

            $response->assertOk()
                ->assertJsonPath('message', '退出成功');
        });

        test('logout fails without token', function () {
            $response = $this->postJson('/admin/api/logout');

            $response->assertUnauthorized();
        });
    });
});
