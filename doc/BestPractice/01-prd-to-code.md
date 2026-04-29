# 📘 最佳用法一：基于 PRD 文档快速开发可用系统

> **已有 PRD → 提示词组装 → 代码生成 → 可用系统**  
> **适用场景**: 项目启动初期，基于已生成的 PRD 文档快速开发

---

## 📋 核心思路

```
PRD 文档（已有） → 按模块拆解 → 选择提示词碎片 → 组装提示词 → AI 生成代码 → 人工验证 → 迭代优化
```

**关键原则**: 不要一次性生成整个系统，而是按 **模块 → 子模块 → 单个功能** 的粒度逐步生成。

---

## 🎯 开发路径（推荐顺序）

### 阶段一：基础设施搭建（Day 1-2）

#### 步骤 1：初始化项目骨架

```markdown
# 提示词组装

## L0: 项目上下文
- 技术栈: Laravel 12 + Filament 3.x + MySQL 8.0 + Redis 7.0
- DDD 分层架构: app/Domains/{Domain}/

## L1: 核心原则
@type-safety-immutability
@dependency-injection

## L2: 上下文规范
读取 doc/prompts/cards/02-context/filament-best-practices.md

## L3: 角色
@SystemArchitect

## L4: 任务
创建项目目录结构，包含：
1. app/Domains/User/ - 用户域
2. app/Domains/Product/ - 商品域
3. app/Domains/Trade/ - 交易域
4. app/Infrastructure/ - 基础设施层
5. 配置多认证体系（Admin + Customer）

## L5: 验收
- [ ] 目录结构符合 DDD 规范
- [ ] 多认证体系可正常工作
```

#### 步骤 2：创建数据库迁移

```markdown
# 提示词组装

## 引用 PRD 文档
读取 doc/PRD/01-ecommerce/models/domain-models.md

## L1: 核心原则
@type-safety-immutability

## L3: 角色
@ProductArchitect
@DBAExpert

## L4: 任务
@template-migration-generation

根据 PRD 中的领域模型定义，按顺序生成以下迁移文件：
1. categories - 商品分类
2. brands - 品牌
3. tags - 标签
4. spus - 标准产品单元
5. skus - 库存量单位
6. carts - 购物车
7. orders - 订单
8. order_items - 订单明细

要求：
- 主键使用 id() (BigInt, auto-increment)
- 金额字段使用 decimal(10,2)
- 所有字段添加 comment()
- 外键显式定义并设置 onDelete 策略

## L5: 验收
- [ ] 迁移文件可正常执行
- [ ] 字段定义与 PRD 一致
- [ ] 索引和外键正确
```

---

### 阶段二：核心模块开发（Day 3-7）

#### 步骤 3：开发商品模块（P0 优先级）

**按用户故事粒度开发，每次只做一个故事：**

```markdown
# 提示词组装（以 US-EC-001 浏览商品列表为例）

## 引用 PRD 文档
读取 doc/PRD/01-ecommerce/stories/01-user-stories.md#US-EC-001
读取 doc/PRD/01-ecommerce/models/domain-models.md#SPU

## L1: 核心原则
@type-safety-immutability
@dependency-injection

## L2: 上下文规范
读取 doc/prompts/cards/02-context/filament-best-practices.md

## L3: 角色
@ProductArchitect
@FilamentUIDesigner

## L4: 任务
实现 US-EC-001 浏览商品列表功能：

### 1. 创建 SPU 模型
- 定义 fillable、casts、关联关系
- 添加 Scopes: scopeActive(), scopeByCategory()

### 2. 创建 API 接口
- GET /api/v1/spus - 商品列表（分页、筛选、排序）
- GET /api/v1/spus/{id} - 商品详情

### 3. 创建 Filament Resource
- Table 列表页：显示商品名称、价格、销量、状态
- Form 表单页：创建/编辑商品
- Infolist 详情页：查看商品详情

## L5: 验收（来自 PRD）
- [ ] 用户可以浏览商品列表
- [ ] 支持按分类筛选
- [ ] 支持按价格排序
- [ ] 支持关键词搜索
- [ ] 商品列表页响应时间 < 500ms
```

#### 步骤 4：开发订单模块（核心流程）

```markdown
# 提示词组装（以 US-EC-004 提交订单为例）

## 引用 PRD 文档
读取 doc/PRD/01-ecommerce/stories/01-user-stories.md#US-EC-004
读取 doc/PRD/01-ecommerce/models/domain-models.md#Order
读取 doc/PRD/01-ecommerce/states/state-machines.md#Order
读取 doc/PRD/01-ecommerce/events/domain-events.md#OrderCreated

## L1: 核心原则
@type-safety-immutability
@dependency-injection
@event-driven
@error-handling

## L2: 上下文规范
读取 doc/prompts/cards/02-context/filament-best-practices.md

## L3: 角色
@TradeEngineer

## L4: 任务
@template-service-layer
@template-dto-conversion

实现 US-EC-004 提交订单功能：

### 1. 创建 DTO
- OrderCreateData (readonly class)
- 包含: user_id, items, shipping_info, remark

### 2. 创建 OrderService
- createOrder(OrderCreateData $data): Order
- 使用 DB::transaction 保证原子性
- 锁定库存 (调用 DRP 服务)
- 生成订单号 (Redis 原子递增)
- 触发 OrderCreated 事件

### 3. 创建 FormRequest
- StoreOrderRequest - 参数验证

### 4. 创建 API 接口
- POST /api/v1/orders

### 5. 实现状态机
- Order 状态: pending → paid → shipped → completed
- 使用 spatie/laravel-model-states

## L4+ 领域约束
@constraint-inventory-concurrency

## L5: 验收（来自 PRD）
- [ ] 生成唯一订单号
- [ ] 创建订单记录（status=pending）
- [ ] 锁定库存
- [ ] 清空已选中的购物车商品
- [ ] 订单金额 = 商品金额 - 优惠金额 + 运费
- [ ] 库存扣减并发安全
```

---

### 阶段三：扩展模块开发（Day 8-14）

#### 步骤 5：按优先级开发其他模块

**开发顺序建议：**

| 优先级 | 模块 | 开发顺序 | 预估时间 |
|--------|------|---------|---------|
| P0 | 电商核心 | 商品 → 购物车 → 订单 → 支付 | 5天 |
| P0 | RBAC 权限 | 角色 → 权限 → 认证 | 2天 |
| P1 | O2O 预约 | 服务 → 时间片 → 预约 → 核销 | 3天 |
| P1 | 进销存 | 仓库 → 库存 → 出入库 | 3天 |
| P1 | 分销 | 关系 → 佣金 → 提现 | 2天 |
| P2 | CRM | 客户 → 跟进 → 机会 | 2天 |
| P2 | 财务 | 付款 → 发票 → 报表 | 3天 |

---

## 🔧 实用技巧

### 技巧 1：引用 PRD 文档的标准方式

```markdown
# 在提示词中引用 PRD 文档

## 引用方式
1. 引用用户故事:
   读取 doc/PRD/01-ecommerce/stories/01-user-stories.md#US-EC-001

2. 引用领域模型:
   读取 doc/PRD/01-ecommerce/models/domain-models.md#SPU

3. 引用状态机:
   读取 doc/PRD/01-ecommerce/states/state-machines.md#Order

4. 引用领域事件:
   读取 doc/PRD/01-ecommerce/events/domain-events.md#OrderPaid

5. 引用业务场景:
   读取 doc/PRD/01-ecommerce/scenarios/promotion-scenario.md
```

### 技巧 2：分层组装模板

```markdown
# 标准组装模板（复制后修改）

## 引用 PRD 文档
读取 doc/PRD/{module}/stories/01-user-stories.md#{story_id}
读取 doc/PRD/{module}/models/domain-models.md#{entity}

## L1: 核心原则
@type-safety-immutability
@dependency-injection
@event-driven

## L2: 上下文规范
@laravel-12-standards

## L3: 角色
@{Role}

## L4: 任务
@template-service-layer

{具体任务描述}

## L5: 验收
- [ ] {验收标准 1}
- [ ] {验收标准 2}
```

### 技巧 3：增量开发模式

```markdown
# 每次只做一个小功能

❌ 错误方式：
"创建整个电商系统"

✅ 正确方式：
"基于 US-EC-001 用户故事，创建商品列表 API 接口"
```

### 技巧 4：验证循环

```markdown
# 每次生成代码后执行验证

1. 运行迁移: php artisan migrate
2. 运行测试: php artisan test
3. 手动测试: 访问接口/页面
4. 代码审查: 检查是否符合规范
5. 修复问题: 针对性修改
```

---

## 📊 开发进度跟踪

### 使用 PRD 验收标准作为检查清单

```markdown
## 电商模块开发进度

### 商品管理
- [x] US-EC-001: 浏览商品列表
- [x] US-EC-002: 查看商品详情
- [ ] 分类管理
- [ ] 品牌管理

### 购物车
- [ ] US-EC-003: 添加购物车

### 订单流程
- [ ] US-EC-004: 提交订单
- [ ] US-EC-005: 支付订单
- [ ] US-EC-006: 查看订单列表
- [ ] US-EC-007: 确认收货
- [ ] US-EC-008: 申请退款
```

---

## 🎯 总结

| 阶段 | 任务 | 关键输出 |
|------|------|---------|
| 基础设施 | 项目骨架、数据库迁移 | 可运行的空项目 |
| 核心模块 | 商品、购物车、订单、支付 | 核心业务可用 |
| 扩展模块 | O2O、进销存、分销、CRM、财务 | 完整系统 |

**核心原则：**
1. 按 PRD 用户故事粒度开发
2. 每次只做一个小功能
3. 生成后立即验证
4. 增量迭代，逐步完善

---

**版本**: v1.0 | **更新日期**: 2026-04-27
