# 核心原则：事件驱动设计 (Event-Driven)

## 用途说明
规范领域事件的定义和使用，实现模块解耦和副作用分离。

## 适用场景
- 状态变更需要通知其他模块时
- 需要触发异步任务时
- 需要记录审计日志时

## 标准内容块
```markdown
## 事件驱动规范

### 事件分类
1. **领域事件 (Domain Events)**: 业务状态变更，如 `OrderCreated`
2. **集成事件 (Integration Events)**: 跨限界上下文，如 `PaymentCompleted`
3. **事件通知 (Notifications)**: 用户通知，如 `OrderShippedNotification`

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
        // 扣减库存
        $this->inventoryService->decreaseStock($event->order);
        
        // 计算分销佣金
        $this->commissionService->calculate($event->order);
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
```
```
