# 核心原则：异常处理规范 (Error Handling)

## 用途说明
规范异常处理策略，确保错误信息明确、可追踪，便于调试和用户反馈。

## 适用场景
- 业务逻辑校验失败时
- 外部服务调用失败时
- 数据库操作异常时

## 标准内容块
```markdown
## 异常处理规范

### 强制要求
1. **自定义异常**: 为每个业务场景创建专用异常类
2. **异常消息**: 包含明确的错误原因和修复建议
3. **异常代码**: 使用常量定义异常代码，便于国际化
4. **上下文信息**: 异常中包含相关数据（如订单ID、用户ID）

### 异常类结构
```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly int $productId,
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct(
            "产品 #{$productId} 库存不足：请求 {$requested} 件，可用 {$available} 件",
            4001
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
    $product = Product::find($productId);
    
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
    $this->renderable(function (InsufficientStockException $e, $request) {
        return response()->json([
            'error' => [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'context' => $e->getContext(),
            ],
        ], 422);
    });
}
```
```
```
