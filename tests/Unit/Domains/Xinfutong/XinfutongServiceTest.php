<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Xinfutong;

use App\Domains\Xinfutong\Models\Account;
use App\Domains\Xinfutong\Services\XinfutongService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XinfutongServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_throws_exception_when_no_account(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('薪福通账号未配置');

        new XinfutongService;
    }

    public function test_service_can_be_created_with_account_id(): void
    {
        $account = Account::factory()->create();

        $service = new XinfutongService($account->id);

        $this->assertEquals($account->id, $service->getAccount()->id);
    }

    public function test_service_can_be_created_with_company_name(): void
    {
        $account = Account::factory()->create(['company_name' => '测试企业']);

        $service = new XinfutongService('测试企业');

        $this->assertEquals($account->id, $service->getAccount()->id);
    }

    public function test_service_can_be_created_with_account_instance(): void
    {
        $account = Account::factory()->create();

        $service = new XinfutongService($account);

        $this->assertEquals($account->id, $service->getAccount()->id);
    }

    public function test_service_uses_default_account_when_no_param(): void
    {
        Account::factory()->create(['is_default' => false]);
        $default = Account::factory()->create(['is_default' => true]);

        $service = new XinfutongService;

        $this->assertEquals($default->id, $service->getAccount()->id);
    }

    public function test_generate_timestamp_returns_13_digit_string(): void
    {
        $account = Account::factory()->create();
        $service = new XinfutongService($account);

        $timestamp = $service->generateTimestamp();

        $this->assertIsString($timestamp);
        $this->assertEquals(13, strlen($timestamp));
        $this->assertMatchesRegularExpression('/^\d{13}$/', $timestamp);
    }

    public function test_generate_nonce_returns_32_character_hex_string(): void
    {
        $account = Account::factory()->create();
        $service = new XinfutongService($account);

        $nonce = $service->generateNonce();

        $this->assertIsString($nonce);
        $this->assertEquals(32, strlen($nonce));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $nonce);
    }

    public function test_get_available_accounts_returns_active_accounts(): void
    {
        Account::factory()->create(['is_active' => true]);
        Account::factory()->create(['is_active' => true]);
        Account::factory()->create(['is_active' => false]);

        $accounts = XinfutongService::getAvailableAccounts();

        $this->assertCount(2, $accounts);
    }

    public function test_sign_request_throws_exception_without_private_key(): void
    {
        $account = Account::factory()->create(['sign_private_key' => '']);
        $service = new XinfutongService($account);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('薪福通签名私钥未配置');

        $service->signRequest([
            'app_id' => 'test',
            'timestamp' => '1234567890123',
            'nonce' => 'abc123',
            'body' => '{}',
        ]);
    }

    public function test_call_api_throws_exception_without_credentials(): void
    {
        $account = Account::factory()->create([
            'app_id' => '',
            'authority_secret' => '',
        ]);
        $service = new XinfutongService($account);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('薪福通 AppId 或 AuthoritySecret 未配置');

        $service->callApi('/test');
    }
}
