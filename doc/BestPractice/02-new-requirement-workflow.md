# 📘 最佳用法二：新需求的提示词设计与组装

> **新需求 → 需求分析 → PRD 生成 → 提示词组装 → 代码实现**  
> **适用场景**: 收到新需求后，如何快速定义需求并开发实现

---

## 📋 核心流程

```
新需求输入 → 需求拆解 → PRD 文档生成 → 提示词碎片选择 → 组装提示词 → AI 生成代码 → 验证迭代
```

**关键原则**: 先定义清楚需求（PRD），再组装提示词生成代码，避免"边想边写"。

---

## 🎯 完整工作流

### 阶段一：需求分析与拆解

#### 步骤 1：需求输入标准化

**收到新需求后，先用结构化格式记录：**

```markdown
# 新需求卡片

## 基本信息
- 需求名称: {名称}
- 提出人: {角色}
- 优先级: P0/P1/P2
- 期望上线: {日期}

## 需求描述
{用一句话描述需求}

## 用户场景
作为 {角色}，我想要 {功能}，以便 {价值}

## 验收标准
1. {标准 1}
2. {标准 2}
3. {标准 3}

## 技术要点
- 涉及模块: {模块列表}
- 数据模型: {新增/修改的实体}
- 外部依赖: {第三方服务}
```

**示例：优惠券需求**

```markdown
# 新需求卡片

## 基本信息
- 需求名称: 优惠券系统
- 提出人: 运营经理
- 优先级: P0
- 期望上线: 2周内

## 需求描述
支持创建和发放优惠券，用户下单时可使用优惠券抵扣金额

## 用户场景
作为 用户，我想要 使用优惠券，以便 享受优惠价格

## 验收标准
1. 管理员可以创建优惠券活动
2. 用户可以查看可用优惠券
3. 下单时可以应用优惠券
4. 优惠券使用后状态更新
5. 订单取消后优惠券可恢复

## 技术要点
- 涉及模块: 电商核心、财务
- 数据模型: Promotion, Coupon, OrderDiscount
- 外部依赖: 无
```

---

### 阶段二：PRD 文档生成

#### 步骤 2：生成模块概览（L2）

```markdown
# 提示词组装

## L3: 角色
@SystemArchitect

## L4: 任务
基于以下需求，生成电商促销模块的概览文档：

需求描述：
- 支持创建和发放优惠券
- 用户下单时可使用优惠券抵扣金额
- 优惠券有满减券、折扣券、免运费券三种类型

请输出：
1. 模块职责与边界
2. 核心功能清单
3. 领域模型概览（ER 图）
4. 核心业务流程
5. 子系统交互关系
6. 验收标准

输出格式：参考 doc/PRD/01-ecommerce/01-module-overview.md 的结构
```

#### 步骤 3：生成用户故事（L3）

```markdown
# 提示词组装

## 引用 PRD 文档
读取 doc/PRD/01-ecommerce/01-module-overview.md（模块概览）

## L3: 角色
@TradeEngineer

## L4: 任务
基于模块概览，生成优惠券系统的用户故事：

请为以下场景创建用户故事：
1. 管理员创建优惠券活动
2. 用户查看可用优惠券
3. 用户下单时应用优惠券
4. 订单取消后恢复优惠券

每个用户故事需包含：
- story_id: US-PRO-{序号}
- user_story: 标准三段式描述
- acceptance_criteria: 验收场景（given/when/then）
- business_rules: 业务规则
- technical_notes: 技术要点
- prompt_fragments: 引用的提示词组件

输出格式：参考 doc/PRD/01-ecommerce/stories/01-user-stories.md 的结构
```

#### 步骤 4：生成领域模型（L4）

```markdown
# 提示词组装

## 引用 PRD 文档
读取 doc/PRD/01-ecommerce/stories/01-user-stories.md（用户故事）

## L3: 角色
@ProductArchitect
@DBAExpert

## L4: 任务
@template-migration-generation

基于用户故事，生成优惠券系统的领域模型：

### 实体设计
1. Promotion - 促销活动
2. Coupon - 优惠券实例
3. OrderDiscount - 订单优惠明细

每个实体需包含：
- 字段定义（名称、类型、约束）
- 索引定义
- 外键关系
- 业务规则
- prompt_fragment（模型生成提示词）

输出格式：参考 doc/PRD/01-ecommerce/models/domain-models.md 的结构
```

#### 步骤 5：生成 API 契约（L4）

```markdown
# 提示词组装

## 引用 PRD 文档
读取 doc/PRD/01-ecommerce/stories/01-user-stories.md（用户故事）
读取 doc/PRD/01-ecommerce/models/domain-models.md（领域模型）

## L3: 角色
@TradeEngineer

## L4: 任务
基于用户故事和领域模型，生成优惠券系统的 API 接口：

### 接口列表
1. GET /api/v1/promotions - 获取活动列表
2. POST /api/v1/promotions - 创建活动
3. GET /api/v1/coupons/available - 获取可用优惠券
4. POST /api/v1/orders/{id}/apply-coupon - 应用优惠券

每个接口需包含：
- endpoint: 请求路径
- request: 请求参数（path_params, query_params, body）
- response: 响应格式（success, errors）
- prompt_fragment: 接口生成提示词

输出格式：参考 doc/PRD/01-ecommerce/apis/api-contracts.md 的结构
```

#### 步骤 6：生成领域事件（L4）

```markdown
# 提示词组装

## 引用 PRD 文档
读取 doc/PRD/01-ecommerce/stories/01-user-stories.md（用户故事）
读取 doc/PRD/01-ecommerce/models/domain-models.md（领域模型）

## L3: 角色
@TradeEngineer

## L4: 任务
基于用户故事和领域模型，生成优惠券系统的领域事件：

### 事件列表
1. CouponCreated - 优惠券创建
2. CouponUsed - 优惠券使用
3. CouponRestored - 优惠券恢复

每个事件需包含：
- event_name: 事件名称
- trigger: 触发时机
- payload: 事件负载
- consumers: 消费者列表

输出格式：参考 doc/PRD/01-ecommerce/events/domain-events.md 的结构
```

---

### 阶段三：提示词组装与代码生成

#### 步骤 7：组装提示词生成代码

**以生成优惠券服务为例：**

```markdown
# 提示词组装

## 引用 PRD 文档
读取 doc/PRD/01-ecommerce/scenarios/promotion-scenario.md

## L1: 核心原则
@type-safety-immutability
@dependency-injection
@event-driven
@error-handling

## L2: 上下文规范
读取 doc/prompts/cards/02-context/filament-best-practices.md

## L3: 角色
@TradeEngineer
@AssetManager

## L4: 任务
@template-service-layer
@template-dto-conversion
@template-event-listener

基于 PRD 中的促销场景，实现优惠券服务：

### 1. 创建 DTO
- PromotionCreateData (readonly class)
- CouponApplyData (readonly class)

### 2. 创建 PromotionService
- createPromotion(PromotionCreateData $data): Promotion
- applyCoupon(CouponApplyData $data): OrderDiscount
- restoreCoupon(int $couponId): void

### 3. 创建事件监听器
- HandleCouponUsedListener
- HandleCouponRestoredListener

### 4. 创建 Filament Resource
- PromotionResource - 活动管理
- CouponResource - 优惠券管理

## L5: 验收
- [ ] 管理员可以创建优惠券活动
- [ ] 用户可以查看可用优惠券
- [ ] 下单时可以应用优惠券
- [ ] 优惠券使用后状态更新
- [ ] 订单取消后优惠券可恢复
```

---

### 阶段四：验证与迭代

#### 步骤 8：验证循环

```markdown
# 验证检查清单

## 代码层面
- [ ] 迁移文件可正常执行
- [ ] 模型关联关系正确
- [ ] 服务层逻辑完整
- [ ] API 接口可正常调用
- [ ] 事件监听器正常工作

## 业务层面
- [ ] 满足用户故事验收标准
- [ ] 业务规则正确实现
- [ ] 边界情况处理完善

## 质量层面
- [ ] 代码符合类型安全规范
- [ ] 异常处理完善
- [ ] 日志记录完整
- [ ] 测试用例覆盖
```

---

## 🔧 实用技巧

### 技巧 1：需求拆解的粒度控制

```
❌ 太粗：
"实现优惠券系统"

✅ 适中：
"实现优惠券的创建、发放、使用、恢复功能"

✅ 更细：
"实现优惠券创建功能，包含活动管理和优惠券实例管理"
```

### 技巧 2：PRD 文档的渐进式生成

```markdown
# 第一轮：概览
生成模块概览文档（L2）

# 第二轮：故事
基于概览生成用户故事（L3）

# 第三轮：模型
基于故事生成领域模型（L4）

# 第四轮：API
基于模型生成 API 契约（L4）

# 第五轮：事件
基于模型生成领域事件（L4）
```

### 技巧 3：提示词碎片的选择策略

| 需求类型 | 推荐角色 | 推荐模板 | 推荐约束 |
|---------|---------|---------|---------|
| 新功能模块 | SystemArchitect + ProductArchitect | migration → service → resource | - |
| 订单/支付 | TradeEngineer | service-layer → dto-conversion | - |
| 佣金/积分 | AssetManager | service-layer → event-listener | distribution-commission |
| 预约/核销 | TradeEngineer | service-layer → event-listener | o2o-timeslot-locking |
| 库存管理 | ProductArchitect | service-layer → event-listener | inventory-concurrency |

### 技巧 4：PRD 文档模板（快速复制）

```markdown
# 模块概览模板

## 📋 元数据
```yaml
module_id: "{module_id}"
module_name: "{module_name}"
version: "1.0"
domain: "{domain}"
priority: "{priority}"
dependencies: [{dependencies}]
dependents: [{dependents}]
```

## 🎯 模块职责
### 核心功能
1. {功能 1}
2. {功能 2}

### 边界定义
- **负责**: {职责}
- **不负责**: {边界}

## 📊 领域模型概览
{ER 图}

## 🔄 核心业务流程
{流程图}

## 🔗 子系统交互
{交互表}

## ✅ 验收标准
{验收清单}
```

```markdown
# 用户故事模板

### {story_id}: {title}

```yaml
story_id: "{story_id}"
title: "{title}"
priority: "{priority}"
actor: "{actor}"
module: "{module}"

user_story: |
  作为一名 {actor}，
  我想要 {功能}，
  以便 {价值}。

acceptance_criteria:
  - scenario: "{场景}"
    given: "{前置条件}"
    when: "{操作}"
    then: "{结果}"

business_rules:
  - "{规则 1}"

prompt_fragments:
  - "@{Role}"
  - "@{template}"
```
```

---

## 📊 完整示例：优惠券需求

### 需求输入
```markdown
支持创建和发放优惠券，用户下单时可使用优惠券抵扣金额
```

### PRD 生成输出
```
1. 模块概览 → promotion-scenario.md
2. 用户故事 → 4 个故事
3. 领域模型 → 3 个实体
4. API 契约 → 4 个接口
5. 领域事件 → 3 个事件
```

### 代码生成输出
```
1. 数据库迁移 → promotions, coupons, order_discounts
2. Eloquent 模型 → Promotion, Coupon, OrderDiscount
3. 服务层 → PromotionService
4. API 接口 → 4 个控制器
5. 事件监听器 → 2 个监听器
6. Filament 资源 → 2 个资源
```

---

## 🎯 总结

| 阶段 | 任务 | 关键输出 | 耗时 |
|------|------|---------|------|
| 需求分析 | 需求拆解、标准化 | 需求卡片 | 0.5天 |
| PRD 生成 | 概览→故事→模型→API→事件 | 完整 PRD 文档 | 1天 |
| 代码生成 | 组装提示词、生成代码 | 可运行代码 | 2天 |
| 验证迭代 | 测试、修复、优化 | 可用功能 | 1天 |

**核心原则：**
1. 先定义需求（PRD），再生成代码
2. 按 L2→L3→L4 的顺序渐进生成 PRD
3. 每次只生成一个小功能
4. 生成后立即验证

---

**版本**: v1.0 | **更新日期**: 2026-04-27
