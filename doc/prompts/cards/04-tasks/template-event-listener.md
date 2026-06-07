# 任务模板：事件监听器实现 (Event Listener)

> **版本**: v3.0 | **层級**: L4 | **最后更新**: 2026-06-07

## 用途说明
规范领域事件的监听与处理逻辑，实现事件驱动的异步解耦。

## 适用场景
- 领域事件的监听与处理
- 跨模块的异步通信
- 副作用的异步执行（通知、日志、统计等）

## 标准内容块
```markdown
# 任务：为 {Event} 创建监听器

## L3: 角色设定
系统架构师确保事件监听器符合事件驱动设计原则。

## 要求
1. **单一职责**：每个监听器只处理一个事件的一个方面
2. **幂等性**：监听器必须支持重复执行而不产生副作用
3. **异常隔离**：监听器内部异常不应影响事件触发者
4. **异步优先**：优先使用队列监听器，避免阻塞主流程
5. **日志记录**：关键操作必须记录日志

## 🎯 设计方案（必须解释）
{监听器职责、处理逻辑、异常处理、幂等性设计、性能考虑}

## 💻 代码实现
```php
<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderConfirmation implements ShouldQueue
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function handle(OrderCreated $event): void
    {
        try {
            $this->notificationService->sendOrderConfirmation(
                $event->order,
                $event->context
            );
        } catch (NotificationFailedException $e) {
            Log::error("订单确认发送失败: {$e->getMessage()}", [
                'order_id' => $event->order->id,
            ]);
        }
    }

    public function failed(OrderCreated $event, Throwable $exception): void
    {
        Log::error("队列监听器执行失败: {$exception->getMessage()}");
    }
}
```

## L5: 验收标准
- [ ] 监听器单一职责
- [ ] 实现 ShouldQueue 异步执行
- [ ] 有异常隔离处理
- [ ] 有 failed() 方法
- [ ] 有日志记录
- [ ] 幂等性设计正确
```
```
