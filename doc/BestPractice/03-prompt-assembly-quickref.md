# 📘 最佳用法三：提示词组装速查手册

> **快速参考** | **场景→角色→模板 映射** | **复制即用**

---

## 🎯 场景速查表

### 按任务类型选择提示词碎片

| 场景 | L3 角色 | L1 原则 | L4 模板 | L4+ 约束 |
|------|---------|---------|---------|----------|
| **创建数据库表** | @ProductArchitect | @type-safety | @template-migration-generation | - |
| **实现服务层** | @TradeEngineer | @type-safety @di @event-driven | @template-service-layer | - |
| **创建 DTO** | @TradeEngineer | @type-safety | @template-dto-conversion | - |
| **创建 FormRequest** | @TradeEngineer | @type-safety | @template-form-request | - |
| **创建 API Resource** | @TradeEngineer | @type-safety | @template-api-resource | - |
| **创建 Filament 页面** | @FilamentUIDesigner | @type-safety | @template-filament-resource | - |
| **实现事件监听** | @TradeEngineer | @event-driven | @template-event-listener | - |
| **编写测试** | @QAEngineer | @tdd | @pest-feature-test | - |
| **订单/支付流程** | @TradeEngineer | @type-safety @di @event-driven | @template-service-layer | - |
| **佣金/积分计算** | @AssetManager | @type-safety @di | @template-service-layer | @constraint-distribution-commission |
| **库存并发控制** | @ProductArchitect | @type-safety @di | @template-service-layer | @constraint-inventory-concurrency |
| **预约时间片** | @TradeEngineer | @type-safety @di | @template-service-layer | @constraint-o2o-timeslot-locking |

---

## 📝 标准组装模板

### 模板 1：创建模型 + 迁移

```markdown
# 引用 PRD
读取 doc/PRD/{module}/models/domain-models.md#{entity}

## L1: 核心原则
@type-safety-immutability

## L3: 角色
@ProductArchitect
@DBAExpert

## L4: 任务
@template-migration-generation

创建 {Entity} 的迁移文件和 Eloquent 模型。

要求：
- 主键使用 id() (BigInt)
- 金额使用 decimal(10,2)
- 所有字段添加 comment()
- 外键设置 onDelete 策略

## L5: 验收
- [ ] 迁移可正常执行
- [ ] 模型关联正确
- [ ] 字段定义与 PRD 一致
```

### 模板 2：实现服务层

```markdown
# 引用 PRD
读取 doc/PRD/{module}/stories/01-user-stories.md#{story_id}
读取 doc/PRD/{module}/models/domain-models.md#{entity}

## L1: 核心原则
@type-safety-immutability
@dependency-injection
@event-driven
@error-handling

## L3: 角色
@{Role}

## L4: 任务
@template-service-layer
@template-dto-conversion

实现 {ServiceName} 服务类，包含以下方法：
1. {method1} - {描述}
2. {method2} - {描述}

要求：
- 使用 DB::transaction 保证原子性
- 使用 DTO 传递数据
- 触发领域事件

## L5: 验收
- [ ] 所有方法有完整类型声明
- [ ] 使用构造函数注入
- [ ] 资金操作在事务中
- [ ] 状态变更触发事件
```

### 模板 3：创建 Filament 资源

```markdown
# 引用 PRD
读取 doc/PRD/{module}/stories/01-user-stories.md#{story_id}

## L1: 核心原则
@type-safety-immutability

## L2: 上下文规范
@filament-best-practices

## L3: 角色
@FilamentUIDesigner

## L4: 任务
@template-filament-resource

为 {Model} 创建 Filament Resource，包含：
1. Table 列表页 - 显示 {字段}
2. Form 表单页 - 编辑 {字段}
3. Infolist 详情页 - 查看详情

## L5: 验收
- [ ] 使用 Schema 语法
- [ ] 列表支持筛选排序
- [ ] 敏感操作有确认框
- [ ] 权限检查使用 Gate
```

### 模板 4：创建 API 接口

```markdown
# 引用 PRD
读取 doc/PRD/{module}/apis/api-contracts.md#{api_name}

## L1: 核心原则
@type-safety-immutability
@dependency-injection

## L3: 角色
@TradeEngineer

## L4: 任务
@template-form-request
@template-api-resource

创建 {API 名称} 接口：
- 路由: {METHOD} {path}
- 功能: {描述}

## L5: 验收
- [ ] 参数验证完整
- [ ] 响应格式规范
- [ ] 错误处理完善
```

### 模板 5：实现事件监听

```markdown
# 引用 PRD
读取 doc/PRD/{module}/events/domain-events.md#{event_name}

## L1: 核心原则
@event-driven
@error-handling

## L3: 角色
@TradeEngineer

## L4: 任务
@template-event-listener

为 {EventName} 事件创建监听器 {ListenerName}。

处理逻辑：
1. {处理步骤 1}
2. {处理步骤 2}

## L5: 验收
- [ ] 实现 ShouldQueue
- [ ] 包含 try/catch
- [ ] 记录处理日志
- [ ] 失败重试机制
```

---

## 🔗 PRD 文档引用速查

| 需要什么 | 引用路径 | 示例 |
|---------|---------|------|
| 模块概览 | `doc/PRD/{module}/01-module-overview.md` | 读取 doc/PRD/01-ecommerce/01-module-overview.md |
| 用户故事 | `doc/PRD/{module}/stories/01-user-stories.md#{story_id}` | 读取 doc/PRD/01-ecommerce/stories/01-user-stories.md#US-EC-001 |
| 领域模型 | `doc/PRD/{module}/models/domain-models.md#{entity}` | 读取 doc/PRD/01-ecommerce/models/domain-models.md#SPU |
| API 契约 | `doc/PRD/{module}/apis/api-contracts.md#{api}` | 读取 doc/PRD/01-ecommerce/apis/api-contracts.md#商品接口 |
| 状态机 | `doc/PRD/{module}/states/state-machines.md#{entity}` | 读取 doc/PRD/01-ecommerce/states/state-machines.md#Order |
| 领域事件 | `doc/PRD/{module}/events/domain-events.md#{event}` | 读取 doc/PRD/01-ecommerce/events/domain-events.md#OrderPaid |
| 业务场景 | `doc/PRD/{module}/scenarios/{scenario}.md` | 读取 doc/PRD/01-ecommerce/scenarios/promotion-scenario.md |
| 测试模板 | `doc/PRD/08-tests/pest-test-templates.md` | 读取 doc/PRD/08-tests/pest-test-templates.md |

---

## 📊 模块开发顺序

### 推荐开发路径

```
Day 1-2: 基础设施
  ├── 项目骨架
  ├── 多认证体系
  └── 数据库迁移

Day 3-5: 电商核心（P0）
  ├── 商品管理（SPU/SKU）
  ├── 购物车
  ├── 订单流程
  └── 支付集成

Day 6-7: RBAC 权限（P0）
  ├── 角色管理
  ├── 权限管理
  └── 认证授权

Day 8-10: O2O 预约（P1）
  ├── 服务管理
  ├── 时间片管理
  ├── 预约流程
  └── 核销系统

Day 11-13: 进销存（P1）
  ├── 仓库管理
  ├── 库存管理
  └── 出入库管理

Day 14-15: 分销（P1）
  ├── 分销关系
  ├── 佣金计算
  └── 提现管理

Day 16-17: CRM（P2）
  ├── 客户管理
  ├── 跟进记录
  └── 机会管理

Day 18-20: 财务（P2）
  ├── 付款管理
  ├── 发票管理
  └── 财务报表
```

---

## 🚀 快速启动命令

### 创建新模块

```bash
# 1. 创建模块目录
mkdir -p doc/PRD/{module_name}/{stories,models,apis,states,events,scenarios}

# 2. 生成模块概览
# 使用模板 1 生成 01-module-overview.md

# 3. 生成用户故事
# 使用模板 2 生成 stories/01-user-stories.md

# 4. 生成领域模型
# 使用模板 3 生成 models/domain-models.md

# 5. 生成 API 契约
# 使用模板 4 生成 apis/api-contracts.md

# 6. 生成领域事件
# 使用模板 5 生成 events/domain-events.md
```

---

**版本**: v1.0 | **更新日期**: 2026-04-27
