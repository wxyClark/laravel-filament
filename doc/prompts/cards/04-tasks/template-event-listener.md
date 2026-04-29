# 任务模板：事件监听器实现

## 用途说明
规范领域事件的监听与处理逻辑，实现事件驱动的异步解耦。

## 适用场景
- 领域事件的监听与处理
- 跨模块的异步通信
- 副作用的异步执行（通知、日志、统计等）

## 标准内容块
```markdown
# 任务：为 {Event} 创建监听器

## 角色
@{Role}

## 开发原则
1. **单一职责**: 每个监听器只处理一个事件的一个方面
2. **幂等性**: 监听器必须支持重复执行而不产生副作用
3. **异常隔离**: 监听器内部异常不应影响事件触发者
4. **异步优先**: 优先使用队列监听器，避免阻塞主流程
5. **日志记录**: 关键操作必须记录日志

## 输出格式
```php
<?php

declare(strict_types=1);

namespace App\Listeners\{Domain};

use App\Events\{Domain}\{EventName};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class {ListenerName} implements ShouldQueue
{
    public int $tries = 3;
    
    public int $backoff = 60;

    public function handle({EventName} $event): void
    {
        // 处理逻辑
        Log::info('{ListenerName} 处理事件', [
            'event' => $event->getEventClass(),
            // 事件数据...
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('{ListenerName} 处理失败', [
            'exception' => $exception->getMessage(),
        ]);
    }
}
```

## 关联组件
- 上游: Domain Event
- 下游: Queue (RabbitMQ)
- 关联: @template-event-listener
```

## 关联组件
- 上游: Domain Event
- 下游: Queue/RabbitMQ
- 关联: @event-driven
