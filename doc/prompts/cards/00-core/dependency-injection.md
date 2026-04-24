# 核心原则：依赖注入 (Dependency Injection)

## 用途说明
强制 AI 使用构造函数注入依赖，避免滥用 Facades，提升代码的可测试性和可维护性。

## 适用场景
- 创建 Service、Repository、Controller 类时
- 进行代码重构时
- 编写单元测试需要 Mock 依赖时

## 标准内容块
```markdown
## 依赖注入规范

### 强制要求
1. **构造函数注入**: 所有依赖必须通过 `__construct()` 注入，禁止在方法内部 new 对象
2. **接口依赖**: 优先依赖接口而非具体实现（依赖倒置原则）
3. **单一职责**: 每个类只注入必要的依赖，避免"上帝类"
4. **不可变注入**: 使用 `protected readonly` 声明注入的依赖

### 禁止做法
- ❌ 在方法内部直接 `new` 依赖对象
- ❌ 滥用 Facades（如 `DB::`, `Cache::`, `Mail::`）
- ❌ 使用 `app()->make()` 动态解析依赖

### 正确示例
```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrderRepositoryInterface;
use App\Events\OrderCreated;
use Illuminate\Contracts\Events\Dispatcher;

readonly class OrderService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected Dispatcher $eventDispatcher,
    ) {}

    public function createOrder(CreateOrderData $data): Order
    {
        $order = $this->orderRepository->create($data);
        
        $this->eventDispatcher->dispatch(new OrderCreated($order));
        
        return $order;
    }
}
```

### 绑定配置 (AppServiceProvider)
```php
$this->app->bind(
    OrderRepositoryInterface::class,
    OrderRepository::class
);
```
```
```
