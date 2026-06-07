# 📚 提示词碎片库 (Prompt Cards Library)

> **版本**: v3.0 | **最后更新**: 2026-06-07  
> **说明**: 基于 v2.0 评估报告优化，统一卡片格式，扩充领域约束，新增 Laravel 12 规范

---

## 📁 目录结构

```
cards/
├── 00-core/                    # 核心原则层 (L1) — 5 张
│   ├── type-safety-immutability.md    # 类型安全与不可变性
│   ├── tdd-guidelines.md              # 测试驱动开发
│   ├── dependency-injection.md         # 依赖注入
│   ├── error-handling.md              # 异常处理规范
│   └── event-driven.md                # 事件驱动设计
│
├── 02-context/               # 上下文注入层 (L2) — 3 张
│   ├── laravel-12-standards.md         # Laravel 12 最佳实践 [新增]
│   ├── project-metadata-injection.md  # 项目元数据感知
│   └── filament-best-practices.md     # Filament 3.x 最佳实践
│
├── 01-roles/                 # 角色定义层 (L3) — 12 张
│   ├── system-architect.md              # 系统架构师 [新增]
│   ├── product-architect.md             # 商品域架构师
│   ├── trade-engineer.md                # 交易工程师
│   ├── asset-manager.md                 # 资产管家
│   ├── filament-ui-designer.md          # Filament UI 设计师
│   ├── dba-expert.md                    # DBA 专家
│   ├── qa-engineer.md                   # QA 工程师
│   ├── devops-engineer.md               # DevOps 工程师
│   ├── security-expert.md               # 安全专家
│   ├── frontend-developer.md            # 前端开发工程师
│   ├── code-reviewer.md                 # Code Review 专家
│   └── tech-interviewer.md              # P9 技术面试官（L2.5 强制层）
│
├── 03-domains/               # 领域约束层 (L2+) — 4 张
│   ├── constraint-distribution-commission.md  # 分销佣金递归计算
│   ├── constraint-o2o-timeslot-locking.md     # O2O 时间片冲突检测
│   ├── constraint-inventory-concurrency.md    # 电商库存并发扣减 [新增]
│   └── constraint-rbac-hierarchy.md           # RBAC 权限层级控制 [新增]
│
├── 04-tasks/                 # 任务模板层 (L4) — 15 张
│   ├── template-migration-generation.md         # 数据库迁移生成
│   ├── template-service-layer.md                # 服务层实现
│   ├── template-dto-conversion.md               # DTO 数据转换
│   ├── template-filament-resource.md            # Filament 资源页面
│   ├── template-form-request.md                 # FormRequest 验证层
│   ├── template-api-resource.md                 # API Resource 响应格式化
│   ├── template-event-listener.md               # 事件监听器实现
│   ├── template-test-coverage.md                # 测试用例编写
│   ├── template-technical-interview.md          # 技术面试引导
│   ├── template-code-review.md                  # Code Review 审查
│   ├── template-requirement-analysis.md         # 需求分析与定义
│   ├── template-test-design.md                  # 软件测试设计
│   ├── template-architecture-design.md          # 架构设计
│   ├── template-database-design.md              # 数据库设计
│   └── template-php-development.md              # PHP 开发规范
│
├── 05-ops/                   # 运维层 (L4+) — 3 张
│   ├── deployment-checklist.md              # 部署检查清单
│   ├── monitoring-telescope.md              # Telescope 监控配置
│   └── queue-horizon.md                     # Horizon 队列监控
│
├── 06-security/              # 安全层 (L4+) — 3 张
│   ├── authorization-gate.md                # 权限控制 (Gates & Policies)
│   ├── auth-sanctum.md                      # API 认证配置 (Sanctum)
│   └── sql-injection-prevention.md          # SQL 注入防护
│
├── 07-testing/               # 测试层 (L4+) — 3 张
│   ├── pest-feature-test.md                 # Pest 功能测试
│   ├── pest-unit-test.md                    # Pest 单元测试
│   └── test-data-factory.md                 # 数据工厂 (Model Factories)
│
├── 08-assembly/              # 组装模板 — 3 张
│   ├── assembly-formula.md                  # 组装公式 v3.0
│   ├── meta-prompt-generator.md             # 母提示词模板
│   └── prompt-composer.md                   # 组合器指南
│
├── 09-contracts/             # 事件契约 — 1 张
│   └── domain-event-contracts.md            # 跨模块事件契约
│
├── 10-scenarios/             # 业务场景 — 3 张
│   ├── ecommerce-promotion.md               # 电商促销
│   ├── ecommerce-shipping.md                # 电商物流
│   └── ecommerce-return-refund.md           # 电商退货退款
│
└── README.md                   # 本文件
```

---

## 📊 碎片统计

| 目录 | 文件数 | 层级 | 用途 |
|------|--------|------|------|
| 00-core/ | 5 | L1 | 核心原则 |
| 02-context/ | 3 | L2 | 上下文规范 |
| 01-roles/ | 12 | L3 | 角色定义 |
| 03-domains/ | 4 | L2+ | 领域约束 |
| 04-tasks/ | 15 | L4 | 任务模板 |
| 05-ops/ | 3 | L4+ | 运维规范 |
| 06-security/ | 3 | L4+ | 安全规范 |
| 07-testing/ | 3 | L4+ | 测试模板 |
| 08-assembly/ | 3 | Meta | 组装指南 |
| 09-contracts/ | 1 | L2+ | 事件契约 |
| 10-scenarios/ | 3 | L5+ | 业务场景 |
| **总计** | **58** | | |

---

## 🎯 五层组装模型 (v3.0)

```
完整 Prompt = L0 + L1 + L2 + L2.5(强制) + L3 + L4 + L5
```

| 层级 | 名称 | 来源 | 必需 |
|------|------|------|------|
| L0 | 元数据感知 | 自动注入 | ✅ |
| L1 | 核心原则 | 00-core/ | ✅ |
| L2 | 上下文规范 | 02-context/ | ✅ |
| L2.5 | 设计原理解释 | tech-interviewer | ✅ |
| L3 | 角色注入 | 01-roles/ | ✅ |
| L2+ | 领域约束 | 03-domains/ | ❌ |
| L4 | 任务模板 | 04-tasks/ | ✅ |
| L5 | 验收标准 | 自动生成 | ✅ |

---

## 🔄 组装公式

```
完整 Prompt = L0 + L1 + L2 + L2.5(强制) + L3 + [L2+] + L4 + L5
```

---

## 📋 角色选择指南

| 任务类型 | 推荐角色 | 备选角色 |
|---------|---------|---------|
| 数据库/模型设计 | ProductArchitect | DBAExpert |
| 订单/支付/状态机 | TradeEngineer | - |
| Filament 后台页面 | FilamentUIDesigner | FrontendDeveloper |
| 余额/积分/佣金 | AssetManager | - |
| 系统架构/模块设计 | SystemArchitect | - |
| API 接口开发 | TradeEngineer | SystemArchitect |
| 安全/权限/认证 | SecurityExpert | - |
| 测试用例编写 | QAEngineer | - |
| 部署/监控/CI/CD | DevOpsEngineer | - |
| Livewire 组件 | FrontendDeveloper | FilamentUIDesigner |
| 代码审查 | CodeReviewer | QAEngineer |

---

## 🚀 快速开始

### 手动组装

1. 阅读 `assembly-formula.md` 了解组装公式
2. 根据需求从各层选择对应卡片
3. 按 L0→L5 顺序组合
4. 使用 `prompt-composer.md` 矩阵快速查找

### 自动组装

1. 将 `meta-prompt-generator.md` 发送给 AI
2. 输入自然语言需求
3. AI 自动检索并组装最优提示词

---

## 📈 v2.0 → v3.0 变更日志

| 变更 | 说明 |
|------|------|
| **新增** | `laravel-12-standards.md` 上下文卡 |
| **新增** | `system-architect.md` 角色卡 |
| **新增** | `constraint-inventory-concurrency.md` 领域约束 |
| **新增** | `constraint-rbac-hierarchy.md` 领域约束 |
| **新增** | `code-reviewer.md` 角色卡（从任务卡提升为角色卡） |
| **新增** | `tech-interviewer.md` 作为 L2.5 强制层 |
| **新增** | `template-api-resource.md` 任务模板 |
| **新增** | `template-event-listener.md` 任务模板 |
| **新增** | `template-form-request.md` 任务模板 |
| **新增** | `template-test-coverage.md` 任务模板 |
| **新增** | `template-technical-interview.md` 任务模板 |
| **统一** | 所有卡片增加 `> 版本/层级/更新` 行 |
| **统一** | 标准内容块格式统一 |
| **统一** | 组装公式 v3.0 引入 L2.5 强制设计解释 |
| **更新** | 母提示词模板 v3.0 索引更新 |

---

**版本**: v3.0 | **最后更新**: 2026-06-07
