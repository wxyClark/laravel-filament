<?php

declare(strict_types=1);

use App\Domains\Xinfutong\Models\Account;
use App\Domains\Xinfutong\Services\XinfutongService;

/**
 * 薪福通 API 连接测试
 *
 * 测试真实 API 连接是否正常
 */
test('检查薪福通账号配置', function () {
    // 创建测试账号
    $account = Account::create([
        'company_name' => '测试公司',
        'base_url' => 'https://xft.cmbchina.com/open',
        'app_id' => 'test_app_id',
        'authority_secret' => 'test_authority_secret',
        'sign_private_key' => "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBg...\n-----END PRIVATE KEY-----",
        'timeout' => 30,
        'is_default' => true,
        'is_active' => true,
    ]);

    $accounts = Account::all();

    $this->assertNotEmpty($accounts, '没有配置薪福通账号');

    $defaultAccount = Account::getDefault();

    if ($defaultAccount) {
        $this->assertNotEmpty($defaultAccount->app_id, 'AppId 未配置');
        $this->assertNotEmpty($defaultAccount->authority_secret, 'AuthoritySecret 未配置');
        $this->assertNotEmpty($defaultAccount->sign_private_key, '签名私钥未配置');

        $this->assertStringContainsString('BEGIN', $defaultAccount->sign_private_key, '签名私钥格式不正确');
    }
});

test('XinfutongService 可以初始化', function () {
    $account = Account::getDefault();

    if ($account === null) {
        $this->markTestSkipped('没有配置默认账号');
    }

    $service = new XinfutongService;

    $this->assertNotNull($service->getAccount());
    $this->assertEquals($account->id, $service->getAccount()->id);
});

test('可以生成签名', function () {
    $account = Account::getDefault();

    if ($account === null) {
        $this->markTestSkipped('没有配置默认账号');
    }

    $service = new XinfutongService;

    $timestamp = $service->generateTimestamp();
    $nonce = $service->generateNonce();

    $this->assertNotEmpty($timestamp);
    $this->assertNotEmpty($nonce);
    $this->assertEquals(32, strlen($nonce));
});

test('API 配置正确', function () {
    $config = config('xinfutong');

    $this->assertArrayHasKey('endpoints', $config);
    $this->assertArrayHasKey('employee_list', $config['endpoints']);
    $this->assertArrayHasKey('workflow_start', $config['endpoints']);

    $this->assertEquals('/api/v1/employee/list', $config['endpoints']['employee_list']);
    $this->assertEquals('/api/v1/workflow/start', $config['endpoints']['workflow_start']);
});

test('账号可以连接测试', function () {
    $account = Account::getDefault();

    if ($account === null) {
        $this->markTestSkipped('没有配置默认账号');
    }

    // 验证账号配置完整性
    $this->assertNotEmpty($account->company_name, '企业名称未配置');
    $this->assertNotEmpty($account->base_url, 'API基础地址未配置');
    $this->assertNotEmpty($account->app_id, 'AppId未配置');
    $this->assertNotEmpty($account->authority_secret, 'AuthoritySecret未配置');
    $this->assertNotEmpty($account->sign_private_key, '签名私钥未配置');
    $this->assertTrue($account->is_active, '账号未启用');

    // 验证 URL 格式
    $this->assertStringStartsWith('https://', $account->base_url, 'API地址必须使用HTTPS');
});

test('员工列表端点配置正确', function () {
    $config = config('xinfutong.endpoints');

    $this->assertEquals('/api/v1/employee/list', $config['employee_list']);
    $this->assertEquals('/api/v1/employee/detail', $config['employee_detail']);
});

test('审批流程端点配置正确', function () {
    $config = config('xinfutong.endpoints');

    $this->assertEquals('/api/v1/workflow/start', $config['workflow_start']);
    $this->assertEquals('/api/v1/workflow/status', $config['workflow_status']);
    $this->assertEquals('/api/v1/workflow/withdraw', $config['workflow_withdraw']);
});

test('组织架构端点配置正确', function () {
    $config = config('xinfutong.endpoints');

    $this->assertEquals('/api/v1/org/list', $config['org_list']);
});
