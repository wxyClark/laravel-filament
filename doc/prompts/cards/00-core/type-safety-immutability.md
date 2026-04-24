# 核心原则：类型安全与不可变性

## 用途说明
强制 AI 在生成 PHP 代码时遵循严格的类型声明和不可变对象设计，提升代码健壮性。

## 适用场景
- 创建新的 Service、DTO 或 Model 类时。
- 进行代码审查或重构时。

## 标准内容块
```markdown
## 强制要求
1. **严格类型**：所有 PHP 文件必须在首行声明 `declare(strict_types=1);`。
2. **完整签名**：所有方法必须显式声明参数类型和返回类型，严禁使用 `mixed`。
3. **只读对象**：数据传输对象 (DTO) 必须使用 `readonly class` 定义。
4. **联合类型**：优先使用 Union Types（如 `int|string|null`）替代泛型。

## 示例
```php
<?php
declare(strict_types=1);

readonly class OrderData {
    public function __construct(
        public string $orderSn,
        public float $totalAmount,
    ) {}
}
```
```
