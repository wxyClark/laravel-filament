<?php

declare(strict_types=1);

namespace App\Domains\ApiTesting\Services;

use App\Domains\ApiTesting\Enums\TestStatus;
use App\Domains\ApiTesting\Models\ApiEnvironment;
use App\Domains\ApiTesting\Models\ApiInterface;
use App\Domains\ApiTesting\Models\ApiTestCase;
use App\Domains\ApiTesting\Models\ApiTestResult;
use Illuminate\Support\Facades\Http;

class TestExecutorService
{
    /**
     * 执行单个测试用例
     */
    public function execute(ApiTestCase $testCase, ?ApiEnvironment $environment = null): ApiTestResult
    {
        $environment = $environment ?? $testCase->environment;
        $interface = $testCase->interface;

        // 构建请求 URL
        $url = rtrim($environment->base_url, '/').'/'.ltrim($interface->path, '/');

        // 合并 Headers
        $headers = array_merge(
            $environment->headers ?? [],
            $interface->headers ?? [],
            $testCase->headers ?? []
        );

        // 添加认证信息
        $headers = $this->addAuthHeaders($headers, $environment);

        // 合并 Query 参数
        $queryParams = $testCase->query_params ?? [];

        // 准备请求 Body
        $body = $testCase->body;

        $startTime = microtime(true);

        try {
            $request = Http::withHeaders($headers)
                ->timeout(30)
                ->withoutVerifying();

            // 添加 Query 参数
            if (! empty($queryParams)) {
                $request = $request->withQueryParameters($queryParams);
            }

            // 发送请求
            $response = match ($interface->method->value) {
                'GET' => $request->get($url),
                'POST' => $request->post($url, $body),
                'PUT' => $request->put($url, $body),
                'PATCH' => $request->patch($url, $body),
                'DELETE' => $request->delete($url),
                default => $request->get($url),
            };

            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            // 解析响应
            $responseBody = $response->json();
            $responseStatus = $response->status();
            $responseHeaders = $response->headers();

            // 执行断言
            $assertionResults = $this->runAssertions(
                $testCase,
                $responseStatus,
                $responseBody,
                $responseTime
            );

            $allPassed = collect($assertionResults)->every(fn ($a) => $a['passed']);

            return ApiTestResult::create([
                'test_case_id' => $testCase->id,
                'interface_id' => $interface->id,
                'environment_id' => $environment->id,
                'status' => $allPassed ? TestStatus::PASS : TestStatus::FAIL,
                'request_url' => $url,
                'request_method' => $interface->method->value,
                'request_headers' => $headers,
                'request_body' => $body,
                'response_status' => $responseStatus,
                'response_headers' => $responseHeaders,
                'response_body' => $responseBody,
                'response_time' => $responseTime,
                'assertion_results' => $assertionResults,
                'executed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            return ApiTestResult::create([
                'test_case_id' => $testCase->id,
                'interface_id' => $interface->id,
                'environment_id' => $environment->id,
                'status' => TestStatus::ERROR,
                'request_url' => $url,
                'request_method' => $interface->method->value,
                'request_headers' => $headers,
                'request_body' => $body,
                'response_status' => null,
                'response_body' => null,
                'response_time' => $responseTime,
                'error_message' => $e->getMessage(),
                'assertion_results' => [],
                'executed_at' => now(),
            ]);
        }
    }

    /**
     * 批量执行测试用例
     */
    public function executeBatch(array $testCases, ?ApiEnvironment $environment = null): array
    {
        $results = [];
        foreach ($testCases as $testCase) {
            $results[] = $this->execute($testCase, $environment);
        }

        return $results;
    }

    /**
     * 执行接口的所有测试用例
     */
    public function executeAllForInterface(ApiInterface $interface, ?ApiEnvironment $environment = null): array
    {
        $testCases = $interface->testCases()->orderBy('sort_order')->get();

        return $this->executeBatch($testCases->toArray(), $environment);
    }

    /**
     * 添加认证 Headers
     */
    protected function addAuthHeaders(array $headers, ApiEnvironment $environment): array
    {
        if ($environment->auth_type->value === 'none') {
            return $headers;
        }

        $authConfig = $environment->auth_config ?? [];

        return match ($environment->auth_type->value) {
            'jwt' => $this->addJwtAuth($headers, $authConfig),
            'apikey' => $this->addApiKeyAuth($headers, $authConfig),
            default => $headers,
        };
    }

    /**
     * 添加 JWT 认证
     */
    protected function addJwtAuth(array $headers, array $config): array
    {
        $tokenUrl = $config['token_url'] ?? null;
        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;
        $headerName = $config['header_name'] ?? 'Authorization';
        $headerPrefix = $config['header_prefix'] ?? 'Bearer';
        $tokenPath = $config['token_path'] ?? 'token';

        if (! $tokenUrl || ! $username || ! $password) {
            return $headers;
        }

        try {
            $baseUrl = $headers['base_url'] ?? '';
            $response = Http::withHeaders(['Accept' => 'application/json'])
                ->post($baseUrl.$tokenUrl, [
                    $config['username_field'] ?? 'email' => $username,
                    $config['password_field'] ?? 'password' => $password,
                ]);

            if ($response->successful()) {
                $token = data_get($response->json(), $tokenPath);
                if ($token) {
                    $headers[$headerName] = $headerPrefix.' '.$token;
                }
            }
        } catch (\Throwable $e) {
            // 认证失败，继续执行
        }

        return $headers;
    }

    /**
     * 添加 API Key 认证
     */
    protected function addApiKeyAuth(array $headers, array $config): array
    {
        $keyName = $config['key_name'] ?? 'X-API-Key';
        $keyValue = $config['key_value'] ?? null;
        $keyLocation = $config['key_location'] ?? 'header';

        if ($keyLocation === 'header' && $keyValue) {
            $headers[$keyName] = $keyValue;
        }

        return $headers;
    }

    /**
     * 执行断言
     */
    protected function runAssertions(
        ApiTestCase $testCase,
        int $responseStatus,
        mixed $responseBody,
        int $responseTime
    ): array {
        $results = [];

        // 状态码断言
        $results[] = [
            'type' => 'status',
            'expected' => $testCase->expected_status,
            'actual' => $responseStatus,
            'passed' => $testCase->expected_status === $responseStatus,
        ];

        // 响应时间断言
        if ($testCase->expected_response_time) {
            $results[] = [
                'type' => 'response_time',
                'expected' => '<= '.$testCase->expected_response_time,
                'actual' => $responseTime,
                'passed' => $responseTime <= $testCase->expected_response_time,
            ];
        }

        // 数据值断言
        if ($testCase->expected_data) {
            foreach ($testCase->expected_data as $assertion) {
                $path = $assertion['path'] ?? '';
                $operator = $assertion['operator'] ?? 'equals';
                $expected = $assertion['expected'] ?? null;

                $actual = data_get($responseBody, $path);
                $passed = $this->evaluateAssertion($operator, $actual, $expected);

                $results[] = [
                    'type' => 'data',
                    'path' => $path,
                    'operator' => $operator,
                    'expected' => $expected,
                    'actual' => $actual,
                    'passed' => $passed,
                ];
            }
        }

        return $results;
    }

    /**
     * 评估断言
     */
    protected function evaluateAssertion(string $operator, mixed $actual, mixed $expected): bool
    {
        return match ($operator) {
            'equals' => $actual == $expected,
            'not_equals' => $actual != $expected,
            'gt' => $actual > $expected,
            'gte' => $actual >= $expected,
            'lt' => $actual < $expected,
            'lte' => $actual <= $expected,
            'contains' => str_contains((string) $actual, (string) $expected),
            'exists' => $actual !== null,
            'not_exists' => $actual === null,
            'in' => in_array($actual, (array) $expected),
            'not_in' => ! in_array($actual, (array) $expected),
            'type' => gettype($actual) === $expected,
            default => false,
        };
    }
}
