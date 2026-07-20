<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\ApiTesting;

use App\Domains\ApiTesting\Enums\AuthType;
use App\Domains\ApiTesting\Models\ApiEnvironment;
use App\Domains\ApiTesting\Models\ApiFunction;
use App\Domains\ApiTesting\Models\ApiInterface;
use App\Domains\ApiTesting\Models\ApiModule;
use App\Domains\ApiTesting\Models\ApiTestCase;
use App\Domains\ApiTesting\Services\TestExecutorService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TestExecutorAuthTest extends TestCase
{
    protected function makeEnvironment(AuthType $authType, array $config = []): ApiEnvironment
    {
        return ApiEnvironment::create([
            'name' => '测试环境',
            'base_url' => 'http://example.com',
            'auth_type' => $authType,
            'auth_config' => $config,
            'headers' => ['Accept' => 'application/json'],
        ]);
    }

    protected function makeTestCase(ApiEnvironment $environment): ApiTestCase
    {
        $module = ApiModule::create([
            'name' => '测试模块',
            'sort_order' => 0,
        ]);

        $function = ApiFunction::create([
            'module_id' => $module->id,
            'name' => '测试功能',
            'sort_order' => 0,
        ]);

        $interface = ApiInterface::create([
            'function_id' => $function->id,
            'name' => '测试接口',
            'path' => 'api/ping',
            'method' => 'GET',
        ]);

        return ApiTestCase::create([
            'interface_id' => $interface->id,
            'environment_id' => $environment->id,
            'name' => '用例',
            'expected_status' => 200,
        ]);
    }

    public function test_jwt_auth_logs_in_and_injects_token_header(): void
    {
        Http::fake([
            'example.com/api/login' => Http::response(['token' => 'abc123'], 200),
            'example.com/api/ping' => Http::response(['ok' => true], 200),
        ]);

        $env = $this->makeEnvironment(AuthType::JWT, [
            'token_url' => 'api/login',
            'username' => 'admin@example.com',
            'password' => 'password',
            'token_path' => 'token',
            'header_name' => 'Authorization',
            'header_prefix' => 'Bearer',
        ]);

        $result = app(TestExecutorService::class)->execute($this->makeTestCase($env));

        expect($result->status->value)->toBe('pass');
        Http::assertSent(fn ($request) => $request->toPsrRequest()->getHeaderLine('Authorization') === 'Bearer abc123');
    }

    public function test_session_auth_logs_in_and_sends_cookie(): void
    {
        Http::fake([
            'example.com/api/login' => Http::response(['ok' => true], 200, [
                'Set-Cookie' => 'laravel_session=sess-xyz; path=/; HttpOnly',
            ]),
            'example.com/api/ping' => Http::response(['ok' => true], 200),
        ]);

        $env = $this->makeEnvironment(AuthType::SESSION, [
            'login_url' => 'api/login',
            'username' => 'admin@example.com',
            'password' => 'password',
        ]);

        $result = app(TestExecutorService::class)->execute($this->makeTestCase($env));

        expect($result->status->value)->toBe('pass');
        Http::assertSent(fn ($request) => str_contains((string) $request->toPsrRequest()->getHeaderLine('Cookie'), 'laravel_session=sess-xyz'));
    }

    public function test_apikey_auth_injects_key_header(): void
    {
        Http::fake([
            'example.com/api/ping' => Http::response(['ok' => true], 200),
        ]);

        $env = $this->makeEnvironment(AuthType::APIKEY, [
            'key_name' => 'X-API-Key',
            'key_value' => 'secret',
            'key_location' => 'header',
        ]);

        $result = app(TestExecutorService::class)->execute($this->makeTestCase($env));

        expect($result->status->value)->toBe('pass');
        Http::assertSent(fn ($request) => $request->toPsrRequest()->getHeaderLine('X-API-Key') === 'secret');
    }
}
