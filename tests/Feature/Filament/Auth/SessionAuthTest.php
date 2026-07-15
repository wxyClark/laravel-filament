<?php

declare(strict_types=1);

use App\Models\Admin;

/*
|--------------------------------------------------------------------------
| Filament Session Authentication Tests
|--------------------------------------------------------------------------
|
| 测试 Filament 后台面板的 Session 认证流程
| Filament 使用 Livewire 组件处理 CRUD，无法通过 HTTP 直接测试
|
*/

describe('Filament Session Auth', function () {

    describe('Admin Panel Access', function () {

        test('admin panel login page is accessible', function () {
            $response = $this->get('/admin/login');

            $response->assertOk();
        });

        test('admin panel redirects to login when unauthenticated', function () {
            $response = $this->get('/admin');

            $response->assertRedirect('/admin/login');
        });

        test('authenticated admin can access dashboard', function () {
            $admin = Admin::factory()->create();

            $this->actingAs($admin, 'admin');

            $response = $this->get('/admin');

            $response->assertOk();
        });
    });

    describe('Session Security', function () {

        test('admin session is invalidated after logout', function () {
            $admin = Admin::factory()->create([
                'password' => bcrypt('password123'),
            ]);

            $this->actingAs($admin, 'admin');

            $response = $this->post('/admin/logout');

            $response->assertRedirect('/admin/login');
        });

        test('admin cannot access dashboard after logout', function () {
            $admin = Admin::factory()->create([
                'password' => bcrypt('password123'),
            ]);

            $this->actingAs($admin, 'admin');
            $this->post('/admin/logout');

            $response = $this->get('/admin');

            $response->assertRedirect('/admin/login');
        });
    });
});
