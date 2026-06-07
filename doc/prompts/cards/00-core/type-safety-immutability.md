# 核心原则：类型安全与不可变性 (Type Safety & Immutability)

> **版本**: v3.0 | **层級**: L1 | **最后更新**: 2026-06-07

## 用途说明
强制 AI 在生成 PHP 代码时遵循严格的类型声明和不可变对象设计，提升代码健壮性。

## 适用场景
- 创建新的 Service、DTO 或 Model 类时
- 进行代码审查或重构时
- 定义 API 请求/响应数据结构时

## 标准内容块
```markdown
## 类型安全与不可变性规范

### 强制要求
1. **严格类型**：所有 PHP 文件首行声明 `declare(strict_types=1);`
2. **完整签名**：所有方法必须显式声明参数类型和返回类型，严禁使用 `mixed`
3. **只读对象**：DTO 必须使用 `readonly class`，聚合根属性使用 `protected readonly`
4. **联合类型**：优先使用 Union Types（`int|string|null`）替代泛型
5. **空合类型**：Laravel 12 中可安全使用 `null` 联合类型替代 `@phpstan-var` 注释

### 禁止做法
- ❌ 省略返回类型声明（即使是 `void`）
- ❌ 使用 `@return` / `@param` PHPDoc 替代实际类型声明
- ❌ 在 DTO 中使用可变属性
- ❌ 使用 `array` 作为方法返回类型（改用具体 `string[]` / `int[]` 等）

### 正确示例
```php
<?php
declare(strict_types=1);

readonly class OrderData {
    public function __construct(
        public string $orderSn,
        public string $customerName,
        public float $totalAmount,
        public array $items = [],
    ) {}

    public function withItems(array $items): self {
        return new self(
            $this->orderSn,
            $this->customerName,
            $this->totalAmount,
            $items,
        );
    }
}
```

### 类型映射表
| 业务概念 | PHP 类型 | 数据库类型 |
|---------|---------|-----------|
| 金额 | `float` (存储用 `DECIMAL`) | `decimal(10,2)` |
| 数量 | `int` | `int UNSIGNED` |
| 状态 | PHP Enum | `tinyint` / `varchar` |
| UUID | `string` | `char(36)` / `binary(16)` |
| 时间 | `DateTimeImmutable` | `timestamp` |
| JSON 数据 | `array` / `json()` cast | `json` |
```
```
