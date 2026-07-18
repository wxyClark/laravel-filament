<?php

declare(strict_types=1);

use App\Domains\Logging\Services\LogService;

if (! function_exists('log_info')) {
    /**
     * 记录信息日志
     */
    function log_info(string $message, array $context = []): \App\Domains\Logging\Models\BusinessLog
    {
        return LogService::info($message, $context);
    }
}

if (! function_exists('log_warning')) {
    /**
     * 记录警告日志
     */
    function log_warning(string $message, array $context = []): \App\Domains\Logging\Models\BusinessLog
    {
        return LogService::warning($message, $context);
    }
}

if (! function_exists('log_error')) {
    /**
     * 记录错误日志
     */
    function log_error(string $message, array $context = []): \App\Domains\Logging\Models\BusinessLog
    {
        return LogService::error($message, $context);
    }
}

if (! function_exists('log_critical')) {
    /**
     * 记录严重错误日志
     */
    function log_critical(string $message, array $context = []): \App\Domains\Logging\Models\BusinessLog
    {
        return LogService::critical($message, $context);
    }
}

if (! function_exists('log_debug')) {
    /**
     * 记录调试日志
     */
    function log_debug(string $message, array $context = []): \App\Domains\Logging\Models\BusinessLog
    {
        return LogService::debug($message, $context);
    }
}

if (! function_exists('get_request_id')) {
    /**
     * 获取当前请求 ID
     */
    function get_request_id(): ?string
    {
        return request()->attributes->get('request_id')
            ?? request()->header('X-Request-ID');
    }
}
