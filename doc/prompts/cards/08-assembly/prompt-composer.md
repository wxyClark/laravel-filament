# 提示词组合器 (Prompt Composer)

> **版本**: v3.0 | **最后更新**: 2026-06-07

## 用途说明
提供可视化的提示词组合指南，帮助快速理解如何组合碎片成完整提示词。

## 适用场景
- 新团队成员培训
- 提示词组装参考
- 质量检查清单

## 碎片组合矩阵

| 需求关键词 | L3 角色 | L1 原则 | L2+ 领域 | L4 模板 |
|-----------|---------|---------|----------|---------|
| 创建表、迁移、模型 | ProductArchitect + DBAExpert | type-safety | - | migration |
| 订单、支付、状态 | TradeEngineer | type-safety + di + error-handling | - | service-layer |
| Filament、后台、管理 | FilamentUIDesigner | type-safety | - | filament-resource |
| 预约、时间片、核销 | TradeEngineer | type-safety + di + error-handling | o2o-timeslot | service-layer |
| 分销、佣金、提现 | AssetManager | type-safety + di + error-handling | distribution | service-layer |
| 库存、秒杀、抢购 | TradeEngineer | type-safety + di + error-handling | inventory-concurrency | service-layer |
| 角色、权限、RBAC | SecurityExpert | type-safety + di | rbac-hierarchy | - |
| API、接口、路由 | TradeEngineer | type-safety + di | - | api-resource |
| 测试、用例、覆盖 | QAEngineer | tdd | - | test-coverage |
| 部署、上线、CI/CD | DevOpsEngineer | - | - | deployment |
| 事件、异步、通知 | TradeEngineer | event-driven | - | event-listener |
| 表单、验证 | SystemArchitect | type-safety + di | - | form-request |

## 快速组装指南

### 场景 1: 创建新功能模块

**组装结果**：
```
L0: 项目元数据
L1: type-safety + di + error-handling
L2: laravel-12-standards + filament-practices
L2.5: tech-interviewer（强制设计解释）
L3: SystemArchitect + ProductArchitect
L4: migration → service → resource
L5: 验收清单
```

### 场景 2: 修复 Bug

**组装结果**：
```
L0: 项目元数据
L1: type-safety + error-handling
L2: 问题相关上下文
L2.5: tech-interviewer
L3: 相关角色
L4: 具体修复指令
L5: 验收清单
```

### 场景 3: 代码审查

**组装结果**：
```
L0: 项目元数据
L1: type-safety + tdd
L2: 相关规范
L3: CodeReviewer + QAEngineer
L4: 审查指令
L5: 审查清单
```

## 组装检查清单

### 组装前
- [ ] 明确任务目标
- [ ] 识别任务类型
- [ ] 选择合适的角色（1-2 个）

### 组装中
- [ ] L0: 注入项目上下文
- [ ] L1: 选择 1-3 个核心原则
- [ ] L2: 加载相关规范
- [ ] L2.5: 包含设计原理解释（**强制**）
- [ ] L3: 定义 1-2 个角色
- [ ] L4: 编写具体任务指令
- [ ] L5: 生成验收标准

---

**版本**: v3.0 | **最后更新**: 2026-06-07
