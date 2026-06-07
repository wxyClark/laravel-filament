# 核心原则：事件驱动设计 (Event-Driven)

> **版本**: v3.0 | **层級**: L1 | **最后更新**: 2026-06-07

## 用途说明
规范领域事件的定义和使用，实现模块解耦和副作用分离。

## 适用场景
- 状态变更需要通知其他模块时
- 需要触发异步任务时
- 需要记录审计日志时
- 跨模块通信时

## 标准内容块
```markdown
## 事件驱动规范

### 事件分类
| 类型 | 说明 | 示例 | 是否异步 |
|------|------|------|---------|
| 领域事件 | 业务状态变更 | `OrderCreated` | 同步 |
| 集成事件 | 跨限界上下文 | `PaymentCompleted` | 异步 |
| 通知事件 | 用户通知 | `OrderShippedNotification` | 异步 |

### 事件类定义
```php
<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly array $context = [],
    ) {}
}
```

### 事件监听器
```php
<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\InventoryService;
use App\Services\CommissionService;

class ProcessOrderCreated
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected CommissionService $commissionService,
    ) {}

    public function handle(OrderCreated $event): void
    {
        $this->inventoryService->decreaseStock($event->order);
        $this->commissionService->calculate($event->order);
    }
}
```

### 异步监听器
```php
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessOrderCreated implements ShouldQueue
{
    // 失败回调
    public function failed(OrderCreated $event, Throwable $exception): void
    {
        Log::error("订单事件处理失败: {$exception->getMessage()}");
    }
}
```

### 事件注册 (EventServiceProvider)
```php
protected $listen = [
    OrderCreated::class => [
        ProcessOrderCreated::class,
        SendOrderConfirmation::class,
        LogOrderActivity::class,
    ],
];
```

### 使用原则
- 事件命名使用过去式（`OrderCreated` 而非 `CreateOrder`）
- 监听器保持单一职责
- 避免在监听器中修改原始聚合
- 使用 `ShouldQueue` 实现异步处理
- 关键事件必须有失败重试机制
```
```
