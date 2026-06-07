# 任务模板：服务层实现 (Service Layer)

> **版本**: v3.0 | **层級**: L4 | **最后更新**: 2026-06-07

## 用途说明
规范业务逻辑层的代码结构，确保依赖注入和事务处理的正确性。

## 适用场景
- 编写复杂业务逻辑（订单创建、佣金计算）
- 实现跨模型的数据协调与状态流转

## 标准内容块
```markdown
# 任务：实现 {ServiceName} 服务类

## L3: 角色设定
### {Role}
{相关角色定义}

## 开发原则
1. **构造函数注入**：所有依赖必须通过 `__construct` 注入
2. **事务保护**：涉及多表变动的逻辑必须包裹在 `DB::transaction()` 中
3. **异常处理**：业务校验失败时抛出明确的自定义异常

## 🎯 设计方案（必须解释）

### 1. 需求理解
{描述服务的核心职责}

### 2. 整体架构
{画出数据流图}

### 3. 核心设计决策
| 决策点 | 选择方案 | 为什么选择 | 为什么不用其他方案 |
|--------|---------|-----------|-------------------|
| 事务策略 | | | |
| 异常处理 | | | |
| 事件触发 | | | |

### 4. 方法设计
| 方法名 | 职责 | 参数 | 返回值 | 异常 |
|--------|------|------|--------|------|
| | | | | |

### 5. 性能考虑
- 时间复杂度：O(?)
- 数据库查询次数：?
- 是否需要缓存：?

## 💻 代码实现
```php
<?php
declare(strict_types=1);

namespace App\Services;

readonly class OrderService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected Dispatcher $eventDispatcher,
    ) {}

    public function createOrder(CreateOrderData $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = $this->orderRepository->create($data);
            $this->eventDispatcher->dispatch(new OrderCreated($order));
            return $order;
        });
    }
}
```

## L5: 验收标准
- [ ] 设计方案完整且合理
- [ ] 所有设计决策都有解释
- [ ] 使用构造函数注入
- [ ] 事务保护正确
- [ ] 异常处理完善
- [ ] 代码可读性和可维护性良好
```
```
