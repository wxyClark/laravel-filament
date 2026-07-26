<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest as IlluminateFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * 基础表单请求类
 *
 * 提供统一的表单验证功能：
 * - 自定义错误消息格式
 * - 自动返回 JSON 响应
 * - 权限检查
 */
abstract class FormRequest extends IlluminateFormRequest
{
    /**
     * 是否需要授权
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 获取验证规则
     *
     * @return array<string, mixed>
     */
    abstract public function rules(): array;

    /**
     * 获取自定义错误消息
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * 获取自定义属性名称
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * 验证失败时的处理
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => '验证失败',
            'errors' => $validator->errors(),
        ], 422));
    }

    /**
     * 准备验证数据
     */
    protected function prepareForValidation(): void
    {
        // 子类可以覆盖此方法以预处理数据
    }

    /**
     * 获取所有验证后的数据
     *
     * @return array<string, mixed>
     */
    public function validatedData(): array
    {
        return $this->validated();
    }

    /**
     * 获取指定字段的值
     */
    public function getField(string $field, mixed $default = null): mixed
    {
        return $this->input($field, $default);
    }

    /**
     * 检查字段是否存在
     */
    public function hasField(string $field): bool
    {
        return $this->has($field);
    }

    /**
     * 获取所有字段名称
     *
     * @return array<int, string>
     */
    public function getFieldNames(): array
    {
        return array_keys($this->rules());
    }
}
