<?php

declare(strict_types=1);

namespace Tests\Feature\Xinfutong;

use App\Domains\Xinfutong\Enums\ApprovalStatus;
use App\Domains\Xinfutong\Enums\SyncStatus;
use App\Domains\Xinfutong\Models\Account;
use App\Domains\Xinfutong\Models\ApprovalRequest;
use App\Domains\Xinfutong\Models\Employee;
use App\Domains\Xinfutong\Models\SyncLog;
use App\Domains\Xinfutong\Services\XinfutongService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class XinfutongApiTest extends TestCase
{
    use RefreshDatabase;

    protected Account $account;

    protected XinfutongService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Generate a valid RSA key for testing
        $config = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
        $resource = openssl_pkey_new($config);
        openssl_pkey_export($resource, $privateKey);

        $this->account = Account::factory()->create([
            'app_id' => 'test-app-id',
            'authority_secret' => 'test-secret',
            'sign_private_key' => $privateKey,
        ]);

        $this->service = new XinfutongService($this->account);
    }

    public function test_get_access_token_returns_token(): void
    {
        Http::fake([
            '*' => Http::response([
                'access_token' => 'mock-access-token-12345',
                'token_type' => 'Bearer',
                'expires_in' => 7200,
            ], 200),
        ]);

        $response = $this->service->callApi('/test');

        $this->assertArrayHasKey('access_token', $response);
    }

    public function test_call_api_sends_correct_headers(): void
    {
        Http::fake([
            '*' => Http::response(['code' => 0], 200),
        ]);

        $this->service->callApi('/test', ['key' => 'value']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('AppId')
                && $request->hasHeader('Timestamp')
                && $request->hasHeader('Nonce')
                && $request->hasHeader('Signature');
        });
    }

    public function test_call_api_sends_correct_body(): void
    {
        Http::fake([
            '*' => Http::response(['code' => 0], 200),
        ]);

        $this->service->callApi('/test', ['key' => 'value']);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return isset($body['appId'])
                && $body['appId'] === 'test-app-id'
                && isset($body['timestamp'])
                && isset($body['nonce'])
                && $body['key'] === 'value';
        });
    }

    public function test_call_api_throws_exception_on_http_error(): void
    {
        Http::fake([
            '*' => Http::response('Server Error', 500),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('薪福通 API 调用失败');

        $this->service->callApi('/test');
    }

    public function test_call_api_throws_exception_on_business_error(): void
    {
        Http::fake([
            '*' => Http::response([
                'code' => 1001,
                'message' => '参数错误',
            ], 200),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('薪福通 API 业务错误 [1001]');

        $this->service->callApi('/test');
    }

    public function test_fetch_employees_returns_data(): void
    {
        Http::fake([
            '*' => Http::response([
                'code' => 0,
                'data' => [
                    'list' => [
                        ['employeeId' => 'E001', 'name' => '张三'],
                        ['employeeId' => 'E002', 'name' => '李四'],
                    ],
                    'total' => 2,
                ],
            ], 200),
        ]);

        $result = $this->service->fetchEmployees(1, 10);

        $this->assertCount(2, $result['data']);
        $this->assertEquals(2, $result['total']);
    }

    public function test_sync_employees_creates_records(): void
    {
        Http::fake([
            '*' => Http::response([
                'code' => 0,
                'data' => [
                    'list' => [
                        [
                            'employeeId' => 'E001',
                            'name' => '张三',
                            'empNo' => 'EMP001',
                            'department' => '技术部',
                            'position' => '工程师',
                            'status' => 'active',
                        ],
                        [
                            'employeeId' => 'E002',
                            'name' => '李四',
                            'empNo' => 'EMP002',
                            'department' => '产品部',
                            'position' => '产品经理',
                            'status' => 'active',
                        ],
                    ],
                    'total' => 2,
                ],
            ], 200),
        ]);

        $log = $this->service->syncEmployees();

        $this->assertEquals(SyncStatus::SUCCESS, $log->status);
        $this->assertEquals(2, $log->records_fetched);
        $this->assertEquals(2, $log->records_created);

        $this->assertDatabaseHas('xinfutong_employees', [
            'xinfutong_id' => 'E001',
            'name' => '张三',
        ]);

        $this->assertDatabaseHas('xinfutong_employees', [
            'xinfutong_id' => 'E002',
            'name' => '李四',
        ]);
    }

    public function test_sync_employees_updates_existing_records(): void
    {
        Employee::factory()->create([
            'xinfutong_id' => 'E001',
            'name' => '张三（旧）',
        ]);

        Http::fake([
            '*' => Http::response([
                'code' => 0,
                'data' => [
                    'list' => [
                        [
                            'employeeId' => 'E001',
                            'name' => '张三（新）',
                            'empNo' => 'EMP001',
                            'status' => 'active',
                        ],
                    ],
                    'total' => 1,
                ],
            ], 200),
        ]);

        $log = $this->service->syncEmployees();

        $this->assertEquals(1, $log->records_updated);

        $this->assertDatabaseHas('xinfutong_employees', [
            'xinfutong_id' => 'E001',
            'name' => '张三（新）',
        ]);
    }

    public function test_sync_employees_records_failure(): void
    {
        Http::fake([
            '*' => Http::response('Server Error', 500),
        ]);

        try {
            $this->service->syncEmployees();
        } catch (\RuntimeException $e) {
            // Expected exception
        }

        $log = SyncLog::latest()->first();

        $this->assertNotNull($log);
        $this->assertEquals(SyncStatus::FAILED, $log->status);
        $this->assertNotNull($log->error_message);
    }

    public function test_submit_approval_creates_approval_record(): void
    {
        Http::fake([
            '*' => Http::response([
                'code' => 0,
                'data' => [
                    'workflowId' => 'WF12345',
                ],
            ], 200),
        ]);

        $approval = ApprovalRequest::factory()->create([
            'status' => ApprovalStatus::DRAFT,
        ]);

        $result = $this->service->submitApproval($approval);

        $this->assertEquals(ApprovalStatus::PENDING, $result->status);
        $this->assertEquals('WF12345', $result->xinfutong_approval_id);
        $this->assertNotNull($result->submitted_at);
    }

    public function test_submit_approval_records_failure(): void
    {
        Http::fake([
            '*' => Http::response('Server Error', 500),
        ]);

        $approval = ApprovalRequest::factory()->create([
            'status' => ApprovalStatus::DRAFT,
        ]);

        $this->expectException(\RuntimeException::class);

        $this->service->submitApproval($approval);

        $approval->refresh();
        $this->assertEquals(ApprovalStatus::DRAFT, $approval->status);
    }

    public function test_query_approval_status_updates_status(): void
    {
        Http::fake([
            '*' => Http::response([
                'code' => 0,
                'data' => [
                    'status' => 'approved',
                ],
            ], 200),
        ]);

        $approval = ApprovalRequest::factory()->create([
            'status' => ApprovalStatus::PENDING,
            'xinfutong_approval_id' => 'WF12345',
        ]);

        $result = $this->service->queryApprovalStatus($approval);

        $this->assertEquals(ApprovalStatus::APPROVED, $result->status);
        $this->assertNotNull($result->approved_at);
    }

    public function test_query_approval_status_skips_when_no_xinfutong_id(): void
    {
        $approval = ApprovalRequest::factory()->create([
            'status' => ApprovalStatus::PENDING,
            'xinfutong_approval_id' => null,
        ]);

        $result = $this->service->queryApprovalStatus($approval);

        $this->assertEquals(ApprovalStatus::PENDING, $result->status);
    }

    public function test_withdraw_approval_updates_status(): void
    {
        Http::fake([
            '*' => Http::response([
                'code' => 0,
                'data' => ['success' => true],
            ], 200),
        ]);

        $approval = ApprovalRequest::factory()->create([
            'status' => ApprovalStatus::PENDING,
            'xinfutong_approval_id' => 'WF12345',
        ]);

        $result = $this->service->withdrawApproval($approval);

        $this->assertEquals(ApprovalStatus::WITHDRAWN, $result->status);
    }

    public function test_withdraw_approval_throws_when_no_xinfutong_id(): void
    {
        $approval = ApprovalRequest::factory()->create([
            'status' => ApprovalStatus::PENDING,
            'xinfutong_approval_id' => null,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('审批单未提交到薪福通');

        $this->service->withdrawApproval($approval);
    }

    public function test_sign_request_generates_signature(): void
    {
        $signature = $this->service->signRequest([
            'app_id' => 'test-app-id',
            'timestamp' => '1234567890123',
            'nonce' => 'abc123def456',
            'body' => '{"key":"value"}',
        ]);

        $this->assertIsString($signature);
        $this->assertNotEmpty($signature);
    }

    public function test_generate_timestamp_is_13_digits(): void
    {
        $timestamp = $this->service->generateTimestamp();

        $this->assertMatchesRegularExpression('/^\d{13}$/', $timestamp);
    }

    public function test_generate_nonce_is_32_hex_chars(): void
    {
        $nonce = $this->service->generateNonce();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $nonce);
    }
}
