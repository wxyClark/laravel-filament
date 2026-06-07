# 核心原则：测试驱动开发 (Test-Driven Development)

> **版本**: v3.0 | **层級**: L1 | **最后更新**: 2026-06-07

## 用途说明
引导 AI 采用"红-绿-重构"循环，确保业务逻辑的正确性和可维护性。

## 适用场景
- 开发核心业务逻辑（订单状态机、佣金计算）前
- 修复 Bug 并编写回归测试时
- 重构已有代码时

## 标准内容块
```markdown
## TDD 执行流程

### 三阶段循环
1. **Red (红)**：先编写一个会失败的测试用例，明确预期行为
2. **Green (绿)**：编写最少量的代码使测试通过
3. **Refactor (重构)**：优化代码结构，保持测试通过

### 覆盖率指标
- 核心服务层 (Services)：≥ 90%
- 领域模型 (Models)：≥ 80%
- 控制器 (Controllers)：≥ 70%
- 整体覆盖率：≥ 80%

### 测试命名规范
```php
// Pest 命名：方法名 = 场景 + 行为 + 预期结果
test_admin_can_login_with_valid_credentials();
test_customer_cannot_checkout_with_insufficient_stock();
test_discount_percentage_is_calculated_correctly();
```

### 工具建议
- 优先使用 **Pest PHP** 编写简洁的测试
- Feature 测试覆盖 HTTP / Filament 页面交互
- Unit 测试覆盖 Service / Value Object / 纯函数逻辑
- 使用 Factory + States 创建测试数据，避免硬编码

### 测试优先级
1. 业务规则正确性（最高）
2. 边界条件和异常路径
3. 并发安全（锁、幂等性）
4. 性能回归（不引入 N+1）
```
```
