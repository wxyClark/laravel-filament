# 核心原则：异常处理规范 (Error Handling)

> **版本**: v3.0 | **层級**: L1 | **最后更新**: 2026-06-07

## 用途说明
规范异常处理策略，确保错误信息明确、可追踪，便于调试和用户反馈。

## 适用场景
- 业务逻辑校验失败时
- 外部服务调用失败时
- 数据库操作异常时
- 定义自定义异常类时

## 标准内容块
```markdown
## 异常处理规范

### 强制要求
1. **自定义异常**：为每个业务场景创建专用异常类，置于 `App\Exceptions\`
2. **异常消息**：包含明确的错误原因和修复建议
3. **异常代码**：使用常量定义异常代码，便于错误码管理
4. **上下文信息**：异常中包含相关数据（订单ID、用户ID等）

### 异常类结构
```php
<?php
declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public const CODE = 4001;

    public function __construct(
        public readonly int $productId,
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct(
            "产品 #{$productId} 库存不足：请求 {$requested} 件，可用 {$available} 件",
            self::CODE
        );
    }

    public function getContext(): array
    {
        return [
            'product_id' => $this->productId,
            'requested' => $this->requested,
            'available' => $this->available,
        ];
    }
}
```

### 使用示例
```php
public function decreaseStock(int $productId, int $quantity): void
{
    $product = Product::findOrFail($productId);

    if ($product->stock < $quantity) {
        throw new InsufficientStockException(
            productId: $productId,
            requested: $quantity,
            available: $product->stock,
        );
    }

    $product->decrement('stock', $quantity);
}
```

### 全局异常处理 (Handler.php)
```php
public function register(): void
{
    $this->renderable(function (InsufficientStockException $e) {
        return response()->json([
            'error' => [
                'code' => $e::CODE,
                'message' => $e->getMessage(),
                'context' => $e->getContext(),
            ],
        ], 422);
    });
}
```

### 禁止做法
- ❌ 使用裸 `throw new Exception('error')`
- ❌ catch 后静默吞掉异常（至少记录日志）
- ❌ 在循环中抛出异常（应使用批量校验）
```
```
