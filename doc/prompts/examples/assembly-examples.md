# 完整组装示例 (Assembly Examples)

> **版本**: v3.0 | **最后更新**: 2026-06-07

## 用途说明
提供完整的提示词组装示例，从需求到最终提示词的每一步。

## 适用场景
- 学习如何组装提示词
- 参考最佳实践
- 理解 L2.5 设计解释的价值

---

## 示例 1: 创建商品管理后台

### Step 1: 需求解析

**输入需求**：为后台添加商品管理功能，支持分类管理和库存查看

**分析**：
| 维度 | 结果 |
|------|------|
| 领域 | 电商域 (Commerce) |
| 任务类型 | 数据库建模 + Service + Filament Resource |
| 复杂度 | 中等 |
| 涉及文件 | Migration × 2, Model × 2, Service, Resource |

### Step 2: 选择卡片

| 层级 | 选择 | 理由 |
|------|------|------|
| L1 | type-safety + DI + error-handling | 中等复杂度，需要异常处理 |
| L2 | laravel-12-standards + filament-best-practices | 涉及后台管理 |
| L3 | ProductArchitect + FilamentUIDesigner | 商品建模 + 后台界面 |
| L4 | migration + service-layer + filament-resource | 三步生成 |
| L2+ | 无 | 不涉及特殊领域约束 |
| L2.5 | tech-interviewer | 强制设计解释 |

### Step 3: 组装完整提示词

```markdown
# 任务: 创建商品管理 Filament Resource

## L0: 项目上下文
- 技术栈: Laravel 12 + Filament 3.x + PHP 8.4
- 已有模型: Customer, Admin
- 数据库: MySQL 8.0
- 代码约定: App\Domains\Commerce\, DTO: App\DTOs\Commerce\

## L1: 核心原则

### 类型安全
- `declare(strict_types=1)`
- 完整的参数和返回类型声明
- DTO 使用 `readonly class`

### 依赖注入
- 构造函数注入
- 接口依赖
- 构造函数参数不超过 4 个

### 异常处理
- 自定义异常类
- 明确错误信息
- 异常包含上下文数据

## L2: 上下文规范

### Laravel 12 标准
- `bootstrap/app.php` 配置中间件
- `bootstrap/providers.php` 注册 Provider
- 不使用 `Kernel.php`

### Filament 3.x 规范
- Schema 链式调用
- Infolist 展示只读信息
- `withCount` 避免 N+1

## L2.5: 设计原理解释（强制）

请在编写代码前解释以下设计决策：

| 决策点 | 你的选择 | 为什么 |
|--------|---------|--------|
| 商品规格存储方式 | JSON vs 关联表 | ? |
| 库存字段类型 | unsignedInteger | ? |
| 分类关系 | 自关联 vs 普通关联 | ? |
| 状态字段 | Enum vs 字符串 | ? |
| 删除策略 | restrict vs cascade | ? |

## L3: 角色设定

### 商品域架构师 (ProductArchitect)
专注 SPU/SKU 解耦、库存一致性、值对象封装。
- 库存扣减使用 `lockForUpdate()`
- 金额使用 `DECIMAL(12,4)`
- 商品状态变更触发领域事件

### Filament UI 设计师 (FilamentUIDesigner)
专注 Filament 3.x 最佳实践。
- 列表页包含筛选器和批量操作
- 敏感操作有确认对话框
- 详情页使用 Infolist

## L4: 任务指令

### 第一部分: Migration

创建 `categories` 和 `products` 表：

**categories 表**：
- id (primary key)
- name (string, 255)
- parent_id (foreign key to categories, nullable)
- sort (unsignedInteger, default 0)
- timestamps
- softDeletes

**products 表**：
- id (primary key)
- category_id (foreign key to categories, restrictOnDelete)
- name (string, 255)
- price (decimal 12,4)
- stock (unsignedInteger, default 0)
- specifications (json, nullable)
- status (enum: draft, published, archived, default draft)
- timestamps
- softDeletes

**索引要求**：
- category_id 普通索引
- status + created_at 复合索引
- name 普通索引

### 第二部分: Service

创建 `ProductService`：

1. `createProduct(CreateProductData $data): Product`
   - 创建商品记录
   - 触发 ProductCreated 事件
   - 返回 Product 模型

2. `updateProduct(int $id, UpdateProductData $data): Product`
   - 验证商品存在
   - 更新商品数据
   - 返回 Product 模型

3. `decreaseStock(int $productId, int $quantity): void`
   - 使用 lockForUpdate 防止超卖
   - 库存不足抛 InsufficientStockException
   - 原子扣减

### 第三部分: Filament Resource

创建 `ProductResource`：

**Table**：
- 列: name, category.name, price(money), stock, status(badge), created_at
- 筛选: 按 status, 按 created_at 范围
- 搜索: name 字段
- 操作: edit, delete(确认)
- 批量: delete(确认)

**Form**：
- 分组: 基本信息, 价格库存, 规格
- 字段: name, category(relationship), price, stock, specifications(json)

**Infolist**：
- 展示完整商品信息
- status 使用 Badge
- price 使用 Money

## L5: 验收标准

### Migration
- [ ] 字段类型正确（金额 DECIMAL 12,4）
- [ ] 有软删除
- [ ] 有正确的外键约束
- [ ] 有合理的索引
- [ ] 字段有中文注释

### Service
- [ ] 构造函数注入
- [ ] 所有方法有类型声明
- [ ] 库存扣减使用 lockForUpdate
- [ ] 有异常处理
- [ ] 触发领域事件

### Filament Resource
- [ ] withCount 避免 N+1
- [ ] 筛选器可用
- [ ] 搜索可用
- [ ] 删除有确认对话框
- [ ] Infolist 正确展示

### 设计解释
- [ ] 所有设计决策都有解释
- [ ] 解释了为什么选择该方案
```

---

## 示例 2: 实现分销佣金计算

### 需求

> 实现三级分销佣金自动计算，佣金基于订单利润计算，冻结 7 天后自动可提现。

### 组装

```
L1: type-safety + DI + error-handling
L2: laravel-12-standards
L3: AssetManager + TradeEngineer
L2+: constraint-distribution-commission
L4: template-service-layer + template-event-listener
L2.5: tech-interviewer
```

### 完整提示词

```markdown
# 任务: 实现分销佣金计算服务

## L0: 项目上下文
（同示例 1，省略）

## L1: 核心原则
（同示例 1，省略）

## L2: 上下文规范
（同示例 1，省略）

## L2.5: 设计原理解释（强制）

请解释：
- 为什么使用递归 CTE 获取分销关系链？
- 佣金为什么基于利润而非总额计算？
- 冻结期如何自动化解冻？
- 并发下单时如何防止重复计算佣金？

## L3: 角色设定

### 资产管家 (AssetManager)
- 复式记账：任何资产变动必须有流水记录
- 并发安全：`lockForUpdate()` 或原子操作
- 精度控制：`bcmath` 或 Money 值对象

### 交易工程师 (TradeEngineer)
- 状态机管理
- 幂等性设计
- 事务一致性

## L2+: 领域约束

### 分销佣金递归计算
1. 使用 MySQL 8.0+ `WITH RECURSIVE` 获取 3 级上级
2. 佣金基于 `profit_margin` 而非 `total_amount`
3. 初始状态 `frozen`，7 天后转为 `available`

## L4: 任务指令

### 1. 模型
- DistributionUser: 分销商关系 (id, upline_id, level, commission_rate)
- CommissionLog: 佣金流水 (id, user_id, order_id, amount, status, frozen_at, released_at)

### 2. Service
- CommissionService::calculate(Order $order): void
  - 基于利润计算各级佣金
  - 创建佣金流水记录
  - 事务内执行

### 3. Event Listener
- CommissionCreated 监听器：
  - 定时任务扫描冻结到期的佣金
  - 状态从 frozen → available
  - 记录审计日志

## L5: 验收标准
- [ ] 递归 CTE 正确获取 3 级关系
- [ ] 基于利润计算
- [ ] 冻结/解冻流程完整
- [ ] 并发安全
- [ ] 精度控制使用 bcmath
- [ ] 有审计日志
- [ ] 设计解释完整
```

---

## 示例 3: 实现 O2O 预约功能

### 需求

> 实现 O2O 服务预约，包含时间片管理、预约下单、取消退款，防止超卖。

### 组装

```
L1: type-safety + DI + error-handling
L2: laravel-12-standards
L3: TradeEngineer + FilamentUIDesigner
L2+: constraint-o2o-timeslot-locking
L4: template-migration + template-service-layer + template-filament-resource + template-test-coverage
L2.5: tech-interviewer
```

### 关键设计问题

```markdown
## L2.5: 设计原理解释

### 并发冲突检测
| 方案 | 优点 | 缺点 | 选择 |
|------|------|------|------|
| lockForUpdate | 简单直接 | 可能阻塞 | ✅ |
| 乐观锁 (version) | 无阻塞 | 重试逻辑复杂 | ❌ |
| Redis 锁 | 高性能 | 额外依赖 | ❌ |

选择 lockForUpdate 的理由：
- 预约场景并发量中等
- 逻辑简单，不易出错
- 与业务事务天然整合

### 时间片重叠检测
SQL 条件：
WHERE NOT (start_time >= ? AND end_time <= ?)
覆盖所有重叠情况。
```

---

## 示例 4: 添加支付回调处理

### 需求

> 实现微信支付回调处理，包含签名验证、幂等性、订单状态更新。

### 组装

```
L1: type-safety + DI + error-handling + tdd
L2: laravel-12-standards
L3: TradeEngineer + SecurityExpert
L4: template-service-layer + template-event-listener + template-test-coverage
L2.5: tech-interviewer
```

### 关键设计问题

```markdown
## L2.5: 设计原理解释

### 幂等性设计
| 方案 | 优点 | 缺点 | 选择 |
|------|------|------|------|
| Redis Key 锁 | 快速 | 分布式一致性问题 | ❌ |
| 数据库唯一索引 | 简单可靠 | 需建索引 | ✅ |

选择数据库唯一索引的理由：
- 回调可能并发到达
- 保证最终一致性
- 不需要额外依赖

### 签名验证流程
```
客户端 → 签名数据 → 验证签名 → 查询订单 → 状态校验 → 更新状态 → 返回 200
                                    │
                              订单不存在 → 记录日志 → 返回 200
                              状态非待支付 → 记录日志 → 返回 200
```
```

---

## 组装检查清单

每次组装完成后，检查以下各项：

### 完整性
- [ ] L0: 项目上下文已注入
- [ ] L1: 核心原则已选择
- [ ] L2: 上下文规范已选择
- [ ] L2.5: 强制设计解释已添加
- [ ] L3: 角色已选择（1-2 个）
- [ ] L4: 任务模板已选择
- [ ] L5: 验收标准已生成

### 质量
- [ ] 设计解释要求具体（不是笼统的"解释一下"）
- [ ] 验收标准可检查（不是模糊描述）
- [ ] 角色选择与任务匹配
- [ ] 没有遗漏关键业务约束

### 简洁性
- [ ] 没有重复信息
- [ ] 每个原则只选必要的
- [ ] 任务指令清晰不冗余

---

**版本**: v3.0 | **最后更新**: 2026-06-07
