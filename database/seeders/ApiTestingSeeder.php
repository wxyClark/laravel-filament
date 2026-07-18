<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\ApiTesting\Enums\AuthType;
use App\Domains\ApiTesting\Models\ApiEnvironment;
use App\Domains\ApiTesting\Models\ApiFunction;
use App\Domains\ApiTesting\Models\ApiInterface;
use App\Domains\ApiTesting\Models\ApiModule;
use App\Domains\ApiTesting\Models\ApiTestCase;
use Illuminate\Database\Seeder;

class ApiTestingSeeder extends Seeder
{
    public function run(): void
    {
        $this->createEnvironments();
        $this->createModules();
        $this->createTestCases();
    }

    protected function createEnvironments(): void
    {
        ApiEnvironment::updateOrCreate(
            ['name' => '开发环境'],
            [
                'base_url' => 'http://nginx:80',
                'auth_type' => AuthType::JWT,
                'auth_config' => [
                    'token_url' => 'admin/api/login',
                    'username_field' => 'email',
                    'password_field' => 'password',
                    'username' => 'admin@example.com',
                    'password' => 'password',
                    'token_path' => 'token',
                    'header_name' => 'Authorization',
                    'header_prefix' => 'Bearer',
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'is_default' => true,
                'sort_order' => 0,
            ]
        );

        $this->command->info('✓ 环境数据已创建');
    }

    protected function createModules(): void
    {
        // 用户认证模块
        $authModule = ApiModule::updateOrCreate(
            ['name' => '用户认证'],
            ['description' => '管理员认证相关接口', 'icon' => 'heroicon-o-shield-check', 'sort_order' => 0]
        );

        $authFunction = ApiFunction::updateOrCreate(
            ['module_id' => $authModule->id, 'name' => 'JWT 认证'],
            ['description' => '基于 JWT 的管理员认证', 'sort_order' => 0]
        );

        $this->createAuthInterfaces($authFunction);

        // 地址管理模块
        $addressModule = ApiModule::updateOrCreate(
            ['name' => '地址管理'],
            ['description' => '行政区划数据管理', 'icon' => 'heroicon-o-map-pin', 'sort_order' => 1]
        );

        // Admin 地址接口
        $adminAddressFunction = ApiFunction::updateOrCreate(
            ['module_id' => $addressModule->id, 'name' => 'Admin 地址接口'],
            ['description' => '管理员后台地址接口', 'sort_order' => 0]
        );

        $this->createAdminAddressInterfaces($adminAddressFunction);

        // 公开地址接口
        $publicAddressFunction = ApiFunction::updateOrCreate(
            ['module_id' => $addressModule->id, 'name' => '公开地址接口'],
            ['description' => '无需认证的地址查询接口', 'sort_order' => 1]
        );

        $this->createPublicAddressInterfaces($publicAddressFunction);

        $this->command->info('✓ 模块和接口数据已创建');
    }

    protected function createAuthInterfaces(ApiFunction $function): void
    {
        $interfaces = [
            [
                'name' => '管理员注册',
                'description' => '注册新的管理员账户',
                'method' => 'POST',
                'path' => 'admin/api/register',
                'auth_required' => false,
            ],
            [
                'name' => '管理员登录',
                'description' => '使用邮箱密码登录，返回 JWT Token',
                'method' => 'POST',
                'path' => 'admin/api/login',
                'auth_required' => false,
            ],
            [
                'name' => '管理员登出',
                'description' => '使当前 JWT Token 失效',
                'method' => 'POST',
                'path' => 'admin/api/logout',
                'auth_required' => true,
            ],
            [
                'name' => '刷新 Token',
                'description' => '使用当前 Token 刷新获取新 Token',
                'method' => 'POST',
                'path' => 'admin/api/refresh',
                'auth_required' => true,
            ],
            [
                'name' => '获取当前用户',
                'description' => '获取当前登录管理员的信息',
                'method' => 'GET',
                'path' => 'admin/api/me',
                'auth_required' => true,
            ],
        ];

        foreach ($interfaces as $index => $data) {
            ApiInterface::updateOrCreate(
                ['function_id' => $function->id, 'path' => $data['path']],
                array_merge($data, ['sort_order' => $index])
            );
        }
    }

    protected function createAdminAddressInterfaces(ApiFunction $function): void
    {
        $interfaces = [
            [
                'name' => '获取所有地址',
                'description' => '获取所有地址列表（带缓存）',
                'method' => 'GET',
                'path' => 'admin/api/addresses',
                'auth_required' => false,
            ],
            [
                'name' => '获取子级地址',
                'description' => '根据 parent_id 获取子级地址',
                'method' => 'GET',
                'path' => 'admin/api/addresses/children',
                'auth_required' => false,
            ],
            [
                'name' => '按级别获取地址',
                'description' => '按省/市/区/街道级别获取地址',
                'method' => 'GET',
                'path' => 'admin/api/addresses/by-level/province',
                'auth_required' => false,
            ],
            [
                'name' => '按编码查找地址',
                'description' => '根据行政区划编码查找地址',
                'method' => 'GET',
                'path' => 'admin/api/addresses/find/110000',
                'auth_required' => false,
            ],
            [
                'name' => '获取地址树',
                'description' => '获取完整的地址树形结构',
                'method' => 'GET',
                'path' => 'admin/api/addresses/tree',
                'auth_required' => false,
            ],
        ];

        foreach ($interfaces as $index => $data) {
            ApiInterface::updateOrCreate(
                ['function_id' => $function->id, 'path' => $data['path']],
                array_merge($data, ['sort_order' => $index])
            );
        }
    }

    protected function createPublicAddressInterfaces(ApiFunction $function): void
    {
        $interfaces = [
            [
                'name' => '获取所有地址',
                'description' => '获取所有地址列表',
                'method' => 'GET',
                'path' => 'api/addresses',
                'auth_required' => false,
            ],
            [
                'name' => '获取地址树',
                'description' => '获取完整的地址树形结构',
                'method' => 'GET',
                'path' => 'api/addresses/tree',
                'auth_required' => false,
            ],
            [
                'name' => '按级别获取地址',
                'description' => '按省/市/区/街道级别获取地址',
                'method' => 'GET',
                'path' => 'api/addresses/by-level/province',
                'auth_required' => false,
            ],
            [
                'name' => '按编码查找地址',
                'description' => '根据行政区划编码查找地址',
                'method' => 'GET',
                'path' => 'api/addresses/find/110000',
                'auth_required' => false,
            ],
        ];

        foreach ($interfaces as $index => $data) {
            ApiInterface::updateOrCreate(
                ['function_id' => $function->id, 'path' => $data['path']],
                array_merge($data, ['sort_order' => $index])
            );
        }
    }

    protected function createTestCases(): void
    {
        $environment = ApiEnvironment::where('name', '开发环境')->first();

        if (! $environment) {
            $this->command->error('请先运行 createEnvironments()');

            return;
        }

        // 为每个接口创建默认测试用例
        $interfaces = ApiInterface::with('function.module')->get();

        foreach ($interfaces as $interface) {
            $this->createDefaultTestCase($interface, $environment);
        }

        $this->command->info('✓ 测试用例已创建');
    }

    protected function createDefaultTestCase(ApiInterface $interface, ApiEnvironment $environment): void
    {
        $testCaseData = match (true) {
            str_contains($interface->path, 'login') => [
                'name' => '正常登录',
                'description' => '使用正确的邮箱密码登录',
                'expected_status' => 200,
                'expected_data' => [
                    ['path' => 'message', 'operator' => 'equals', 'expected' => '登录成功'],
                ],
            ],
            str_contains($interface->path, 'register') => [
                'name' => '正常注册',
                'description' => '使用有效信息注册',
                'expected_status' => 201,
            ],
            str_contains($interface->path, 'logout') => [
                'name' => '正常登出',
                'description' => '使用有效 Token 登出',
                'expected_status' => 200,
            ],
            str_contains($interface->path, 'refresh') => [
                'name' => '刷新 Token',
                'description' => '使用有效 Token 刷新',
                'expected_status' => 200,
            ],
            str_contains($interface->path, 'me') => [
                'name' => '获取当前用户',
                'description' => '获取当前登录用户信息',
                'expected_status' => 200,
                'expected_data' => [
                    ['path' => 'data.email', 'operator' => 'equals', 'expected' => 'admin@example.com'],
                ],
            ],
            str_contains($interface->path, 'tree') => [
                'name' => '获取地址树',
                'description' => '获取完整的地址树',
                'expected_status' => 200,
                'expected_data' => [
                    ['path' => 'data', 'operator' => 'exists'],
                ],
            ],
            str_contains($interface->path, 'by-level') => [
                'name' => '按级别查询',
                'description' => '查询省级地址',
                'expected_status' => 200,
                'expected_data' => [
                    ['path' => 'data', 'operator' => 'exists'],
                ],
            ],
            str_contains($interface->path, 'find') => [
                'name' => '按编码查找',
                'description' => '查找北京编码 110000',
                'expected_status' => 200,
                'expected_data' => [
                    ['path' => 'data.code', 'operator' => 'equals', 'expected' => '110000'],
                ],
            ],
            default => [
                'name' => '基础请求测试',
                'description' => '验证接口可正常访问',
                'expected_status' => 200,
            ],
        };

        ApiTestCase::updateOrCreate(
            ['interface_id' => $interface->id, 'environment_id' => $environment->id, 'name' => $testCaseData['name']],
            $testCaseData
        );
    }
}
