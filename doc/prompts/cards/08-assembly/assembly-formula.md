# 提示词组装公式 (Prompt Assembly Formula)

> **版本**: v3.0 | **最后更新**: 2026-06-07

## 用途说明
定义结构化开发提示词的组装规则和层级模型，确保每次 AI 生成代码前都有完整的设计思考。

## 适用场景
- 手动组装结构化提示词
- 作为 `meta-prompt-generator.md` 的参考手册
- 新成员学习提示词组装方法

## 核心公式

```
完整 Prompt = L0 + L1 + L2 + L2.5(强制) + L3 + L4 + L5
```

| 层级 | 名称 | 来源目录 | 必需 | 说明 |
|------|------|---------|------|------|
| **L0** | 元数据感知 | 自动注入 | ✅ | 项目结构、技术栈、现有模型 |
| **L1** | 核心原则 | `00-core/` | ✅ | 类型安全、TDD、DI、异常处理 |
| **L2** | 上下文规范 | `02-context/` | ✅ | Laravel/Filament 规范 |
| **L2.5** | 设计原理解释 | `01-roles/tech-interviewer.md` | ✅ | **强制**：讲清楚为什么这样设计 |
| **L3** | 角色注入 | `01-roles/` | ✅ | 1-2 个专业角色 |
| **L2+** | 领域约束 | `03-domains/` | ❌ | 可选，按业务领域选择 |
| **L4** | 任务模板 | `04-tasks/` | ✅ | 具体实现指令 |
| **L5** | 验收标准 | 自动生成 | ✅ | 质量检查清单 |

## 组装规则

### 规则 1：L1 核心原则选择
| 任务复杂度 | 必选原则 | 可选原则 |
|-----------|---------|---------|
| 简单 | `type-safety-immutability` | - |
| 中等 | `type-safety-immutability` + `dependency-injection` | `error-handling` |
| 复杂 | 上述全部 | `event-driven`, `tdd-guidelines` |

### 规则 2：L3 角色选择
| 任务类型 | 主角色 | 辅助角色 |
|---------|--------|---------|
| 数据库/模型 | ProductArchitect | DBAExpert |
| 订单/支付 | TradeEngineer | - |
| Filament UI | FilamentUIDesigner | - |
| 资产/佣金 | AssetManager | - |
| 架构设计 | SystemArchitect | - |
| 安全相关 | SecurityExpert | - |
| 测试相关 | QAEngineer | - |
| 部署运维 | DevOpsEngineer | - |
| 前端组件 | FrontendDeveloper | FilamentUIDesigner |
| 代码审查 | CodeReviewer | QAEngineer |

### 规则 3：L4 任务模板选择
| 输出类型 | 任务模板 |
|---------|---------|
| 数据库表 | `template-migration-generation` |
| 业务逻辑 | `template-service-layer` |
| 数据传输 | `template-dto-conversion` |
| 后台页面 | `template-filament-resource` |
| 表单验证 | `template-form-request` |
| API 响应 | `template-api-resource` |
| 事件监听 | `template-event-listener` |
| 测试用例 | `template-test-coverage` |
| 需求分析 | `template-requirement-analysis` |

## 完整组装示例

### 输入需求
```
创建一个订单管理 Filament Resource，包含列表、创建、编辑功能，支持按状态筛选。
```

### 组装结果
```markdown
# 任务：创建订单管理 Filament Resource

## L0: 项目上下文
- 技术栈: Laravel 12 + Filament 3.x
- 现有模型: Customer, Admin
- 数据库: MySQL 8.0

## L1: 核心原则
### 类型安全
- `declare(strict_types=1)`
- 完整的参数和返回类型声明

### 依赖注入
- 构造函数注入
- 接口依赖

## L2: 上下文规范
### Laravel 12 标准
- 使用 `bootstrap/app.php` 配置中间件
- 使用 `bootstrap/providers.php` 注册 Provider

### Filament 3.x 规范
- Schema 链式调用
- Infolist 展示只读信息
- withCount 避免 N+1

## L2.5: 设计原理解释（强制）
请解释：
- 订单列表需要展示哪些字段？为什么？
- 状态筛选如何实现？使用 Enum 还是 Select？
- 删除操作是否需要二次确认？

## L3: 角色设定
### Filament UI 设计师 (FilamentUIDesigner)
专注 Filament 3.x 最佳实践和用户体验。

### 交易工程师 (TradeEngineer)
提供订单业务领域的专业知识。

## L4: 任务指令
请创建 `OrderResource` Filament 资源，实现：

### Table
- 列: 订单号、客户名称、状态、金额、创建时间
- 筛选: 按状态、按创建时间范围
- 操作: 编辑、删除（需确认）
- 批量操作: 批量删除

### Form
- 分组: 基本信息、商品明细、支付信息
- 字段: 客户选择、商品选择、金额自动计算

### Infolist
- 展示订单完整信息
- 状态使用 Badge

## L5: 验收标准
- [ ] Table 使用 withCount 避免 N+1
- [ ] Form 有分组 Layout
- [ ] 状态筛选器可用
- [ ] 删除操作有确认对话框
- [ ] Infolist 展示只读信息
- [ ] 所有设计决策都有解释
```

## 组装检查清单

### 组装前
- [ ] 明确任务类型和涉及的领域
- [ ] 选择合适的角色（1-2 个）
- [ ] 确认是否需要领域约束

### 组装后
- [ ] L0 层包含项目上下文
- [ ] L1 层包含核心原则
- [ ] L2 层包含上下文规范
- [ ] L2.5 层包含设计原理解释要求
- [ ] L3 层包含角色设定
- [ ] L4 层包含具体任务指令
- [ ] L5 层包含验收标准
- [ ] 整体格式清晰，无遗漏

---

**版本**: v3.0 | **最后更新**: 2026-06-07
