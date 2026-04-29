# 📝 PRD 文档变更日志

> **版本管理** | **变更追踪** | **RAG 友好格式**

---

## 📋 元数据

```yaml
document: "PRD Documentation"
versioning_scheme: "Semantic Versioning (Major.Minor.Patch)"
current_version: "2.0.0"
```

---

## 📌 版本号规范

### 语义化版本 (SemVer)

```
MAJOR.MINOR.PATCH

MAJOR: 重大需求变更，架构调整
MINOR: 新增功能模块，新增用户故事
PATCH: 文档修正，细节优化
```

---

## 📜 变更日志

### [2.0.0] - 2026-04-27

#### 重大变更 (Breaking Changes)
- 🔄 **重构**: PRD 目录结构与提示词库完全对齐
- 🔄 **重构**: 统一 L0-L5 层级映射关系
- 🔄 **重构**: 事件驱动架构标准化

#### 新增 (Added)
- ✅ **统一架构规范**: `00-overview/00-unified-architecture.md`
- ✅ **领域事件文档**: 所有子系统新增 `events/domain-events.md`
- ✅ **业务场景文档**: 电商模块新增 `scenarios/` 目录
  - `promotion-scenario.md` - 促销场景
  - `shipping-scenario.md` - 物流场景
  - `return-refund-scenario.md` - 退货退款场景
- ✅ **提示词模板**: 新增缺失的模板
  - `template-form-request.md` - FormRequest 验证层
  - `template-api-resource.md` - API Resource 响应格式化
  - `template-event-listener.md` - 事件监听器实现
- ✅ **跨模块契约**: 新增 `09-contracts/domain-event-contracts.md`
- ✅ **电商场景模板**: 新增 `10-scenarios/` 目录
  - `ecommerce-promotion.md` - 促销场景模板
  - `ecommerce-shipping.md` - 物流场景模板
  - `ecommerce-return-refund.md` - 退货退款场景模板

#### 变更 (Changed)
- 🔄 优化 PRD 索引结构，增加领域事件和业务场景统计
- 🔄 优化 PRD 汇总文档，增加 v2.0 完成度统计
- 🔄 统一文档元数据格式，增加 prompt_fragments 结构

#### 修复 (Fixed)
- 🐛 修复 PRD 目录结构与 prompts 目录不一致的问题
- 🐛 修复事件命名不规范的问题
- 🐛 修复跨模块事件契约缺失的问题

---

### [1.0.0] - 2026-04-24

#### 新增 (Added)
- ✅ 完整的 PRD 文档金字塔结构
- ✅ 七大子系统模块概览文档
- ✅ 用户故事文档 (L3)
- ✅ 领域模型文档 (L4)
- ✅ API 接口契约文档 (L4)
- ✅ 状态机定义文档 (L4)
- ✅ Pest 测试用例模板
- ✅ 文档索引和汇总

---

## 📁 文档结构

```
doc/PRD/
├── CHANGELOG.md                    # 本文档
├── VERSION                         # 版本号文件
├── 00-PRD-INDEX.md                 # 文档索引
├── 00-PRD-SUMMARY.md               # 生成总结
├── 00-overview/                    # 系统总览
│   ├── 00-unified-architecture.md  # 统一架构规范
│   ├── 01-domain-map.md            # 领域边界图
│   └── 02-event-catalog.md         # 事件目录
├── 08-tests/
│   └── pest-test-templates.md      # 测试模板
├── 09-versioning/
│   └── version-management.md       # 版本规范
├── 01-ecommerce/
│   ├── 01-module-overview.md       # L2: 模块概览
│   ├── stories/01-user-stories.md  # L3: 用户故事
│   ├── models/domain-models.md     # L4: 领域模型
│   ├── apis/api-contracts.md       # L4: API 契约
│   ├── states/state-machines.md    # L4: 状态机
│   ├── events/domain-events.md     # L4: 领域事件
│   └── scenarios/                  # L4+: 业务场景
│       ├── promotion-scenario.md
│       ├── shipping-scenario.md
│       └── return-refund-scenario.md
├── 02-o2o/ ... 07-finance/         # 其他子系统
```

---

## 📊 统计信息

### 文档数量

| 版本 | 总文档数 | 新增 | 变更 |
|------|---------|------|------|
| v2.0.0 | 54 | 15 | 3 |
| v1.0.0 | 39 | 39 | 0 |

### v2.0 新增内容

| 类型 | 数量 | 说明 |
|------|------|------|
| 统一架构规范 | 1 | L0-L5 映射关系 |
| 领域事件文档 | 5 | 电商、O2O、分销、财务 |
| 业务场景文档 | 3 | 促销、物流、退货退款 |
| 提示词模板 | 3 | FormRequest、APIResource、EventListener |
| 跨模块契约 | 1 | 事件契约规范 |
| 场景模板 | 3 | 电商场景模板 |

---

## 🔄 变更流程

### 提出变更

1. 在 CHANGELOG.md 中记录变更
2. 更新相关文档
3. 更新文档索引 (00-PRD-INDEX.md)
4. 更新版本号

### 变更类型

| 类型 | 说明 | 版本号变更 |
|------|------|-----------|
| Added | 新增功能/文档 | MINOR |
| Changed | 变更现有内容 | MINOR |
| Deprecated | 即将废弃 | MINOR |
| Removed | 移除功能/文档 | MAJOR |
| Fixed | 修复错误 | PATCH |
| Security | 安全修复 | PATCH |

---

**版本**: v2.0.0 | **更新日期**: 2026-04-27
