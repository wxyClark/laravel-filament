<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Infrastructure\Support\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * 基础控制器
 *
 * 提供统一的控制器功能：
 * - API 响应标准化（ApiResponse Trait）
 * - 授权检查（AuthorizesRequests）
 * - 请求验证（ValidatesRequests）
 */
abstract class Controller extends BaseController
{
    use ApiResponse;
    use AuthorizesRequests;
    use ValidatesRequests;

    /**
     * 获取当前认证用户
     */
    protected function user()
    {
        return auth()->user();
    }

    /**
     * 获取当前认证用户 ID
     */
    protected function userId(): int
    {
        return $this->user()->id;
    }

    /**
     * 检查用户是否有指定权限
     */
    protected function authorizePermission(string $permission): void
    {
        if (! $this->user()->can($permission)) {
            $this->forbidden('无权执行此操作');
        }
    }

    /**
     * 获取分页参数
     */
    protected function getPaginationParams(): array
    {
        return [
            'per_page' => request()->input('per_page', 15),
            'page' => request()->input('page', 1),
        ];
    }

    /**
     * 获取排序参数
     */
    protected function getSortParams(string $defaultColumn = 'created_at', string $defaultDirection = 'desc'): array
    {
        return [
            'column' => request()->input('sort', $defaultColumn),
            'direction' => request()->input('direction', $defaultDirection),
        ];
    }

    /**
     * 获取搜索关键词
     */
    protected function getSearchKeyword(): ?string
    {
        return request()->input('keyword') ?: null;
    }
}
