# 任务模板：服务层实现 (Service Layer)

## 用途说明
规范业务逻辑层的代码结构，确保依赖注入和事务处理的正确性。

## 适用场景
- 编写复杂的业务逻辑（如订单创建、佣金计算）。
- 实现跨模型的数据协调与状态流转。

## 标准内容块
```markdown
# 任务：实现 {ServiceName} 服务类

## 角色
@{Role}

## 开发原则
1. **构造函数注入**：所有依赖（Model, Repository, EventDispatcher）必须通过 `__construct` 注入。
2. **事务保护**：涉及多表变动的逻辑必须包裹在 `DB::transaction()` 中。
3. **异常处理**：业务校验失败时抛出明确的自定义异常（如 `InsufficientStockException`）。

## 输出格式
请提供完整的 PHP 类代码，包含详细的 PHPDoc 注释和类型声明。
```
