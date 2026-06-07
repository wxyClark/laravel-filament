# 任务模板：DTO 数据转换 (Data Transfer Object)

> **版本**: v3.0 | **层級**: L4 | **最后更新**: 2026-06-07

## 用途说明
规范从 HTTP Request 到业务逻辑层的数据传递方式，实现不可变性。

## 适用场景
- 处理复杂的表单提交数据
- 在 Service 层之间传递标准化数据结构

## 标准内容块
```markdown
# 任务：为 {Feature} 创建 DTO

## L3: 角色设定
系统架构师确保 DTO 设计符合 DDD 原则。

## 要求
1. **只读属性**：使用 `readonly class` 定义 DTO
2. **静态构造器**：提供 `fromRequest(StoreXxxRequest $request)` 静态方法
3. **类型映射**：确保所有属性有严格类型声明

## 🎯 设计方案（必须解释）
{描述 DTO 职责、属性设计、数据来源、使用场景、性能考虑}

## 💻 代码实现
```php
<?php
declare(strict_types=1);

namespace App\DTOs;

readonly class CreateOrderData
{
    public function __construct(
        public readonly int $customerId,
        public readonly string $customerName,
        public readonly float $totalAmount,
        public readonly array $items = [],
    ) {}

    public static function from(array $data): self
    {
        return new self(
            customerId: (int) $data['customer_id'],
            customerName: (string) $data['customer_name'],
            totalAmount: (float) $data['total_amount'],
            items: $data['items'] ?? [],
        );
    }
}
```

## L5: 验收标准
- [ ] DTO 使用 readonly class
- [ ] 有静态构造器 fromRequest
- [ ] 所有属性有严格类型
- [ ] 嵌套数据有对应 DTO 类
- [ ] 类型转换正确
```
```
