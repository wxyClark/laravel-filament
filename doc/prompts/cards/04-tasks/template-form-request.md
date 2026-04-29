# 任务模板：FormRequest 验证层

## 用途说明
规范 HTTP 请求的参数校验逻辑，实现请求数据的验证与标准化。

## 适用场景
- 所有需要参数验证的 API 接口
- 表单提交的数据校验
- 复杂的验证规则（条件验证、自定义规则）

## 标准内容块
```markdown
# 任务：为 {Feature} 创建 FormRequest

## 角色
@{Role}

## 开发原则
1. **单一职责**: 每个 FormRequest 只处理一个接口的验证
2. **规则清晰**: 使用 Laravel 验证规则链，规则顺序：类型 → 范围 → 唯一性 → 自定义
3. **错误消息**: 提供友好的中文错误消息
4. **授权逻辑**: 在 authorize() 方法中处理权限校验
5. **数据清理**: 使用 validated() 获取已清洗的数据

## 输出格式
```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\{Domain};

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class {Feature}Request extends FormRequest
{
    public function authorize(): bool
    {
        // 权限校验
        return $this->user()->can('{permission}');
    }

    public function rules(): array
    {
        return [
            // 验证规则
        ];
    }

    public function messages(): array
    {
        return [
            // 中文错误消息
        ];
    }

    /**
     * 获取已验证的数据（强类型）
     */
    public function validatedData(): array
    {
        return $this->validated();
    }
}
```

## 示例参考
参考项目中的 `app/Http/Requests/StoreOrderRequest.php`
```

## 关联组件
- 上游: Controller
- 下游: DTO (Data Transfer Object)
- 关联: @template-dto-conversion
