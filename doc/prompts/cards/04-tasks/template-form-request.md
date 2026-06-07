# 任务模板：FormRequest 验证层 (Form Request Validation)

> **版本**: v3.0 | **层級**: L4 | **最后更新**: 2026-06-07

## 用途说明
规范 HTTP 请求的参数校验逻辑，实现请求数据的验证与标准化。

## 适用场景
- 所有需要参数验证的 API 接口
- 表单提交的数据校验
- 复杂的验证规则（条件验证、自定义规则）

## 标准内容块
```markdown
# 任务：为 {Feature} 创建 FormRequest

## L3: 角色设定
系统架构师确保验证逻辑符合业务规则。

## 要求
1. **单一职责**：每个 FormRequest 只处理一个接口的验证
2. **规则清晰**：使用 Laravel 验证规则链，顺序：类型 → 范围 → 唯一性 → 自定义
3. **错误消息**：提供友好的中文错误消息
4. **授权逻辑**：在 authorize() 方法中处理权限校验
5. **数据清理**：使用 validated() 获取已清洗的数据

## 🎯 设计方案（必须解释）
{验证规则设计、条件验证、自定义验证、授权逻辑、性能考虑}

## 💻 代码实现
```php
<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.*.product_id.exists' => '请选择有效的商品',
            'items.*.quantity.min' => '商品数量至少为 1',
        ];
    }
}
```

## L5: 验收标准
- [ ] 验证规则完整覆盖所有字段
- [ ] 有中文错误消息
- [ ] authorize() 权限校验正确
- [ ] 使用 validated() 获取清洗数据
- [ ] 条件验证逻辑正确
```
```
