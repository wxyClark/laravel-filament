# 📘 BestPractice 最佳用法指南

> **基于 doc/ 目录文档** | **两种最佳用法** | **快速开发可用系统**

---

## 📋 文档结构

```
doc/BestPractice/
├── 00-overview.md                    # 本文档（总结）
├── 01-prd-to-code.md                 # 用法一：基于 PRD 快速开发
├── 02-new-requirement-workflow.md     # 用法二：新需求的提示词设计
├── 03-prompt-assembly-quickref.md    # 提示词组装速查手册
└── 04-ai-ide-integration.md          # AI IDE 集成指南
```

---

## 🎯 两种最佳用法

### 用法一：基于 PRD 快速开发可用系统

**适用场景**: 项目启动初期，已有 PRD 文档

**核心流程**:
```
PRD 文档（已有） → 按模块拆解 → 选择提示词碎片 → 组装提示词 → AI 生成代码 → 人工验证 → 迭代优化
```

**关键原则**:
1. 按 PRD 用户故事粒度开发
2. 每次只做一个小功能
3. 生成后立即验证
4. 增量迭代，逐步完善

**详细指南**: [01-prd-to-code.md](./01-prd-to-code.md)

---

### 用法二：新需求的提示词设计与组装

**适用场景**: 收到新需求后，快速定义需求并开发实现

**核心流程**:
```
新需求输入 → 需求拆解 → PRD 文档生成 → 提示词碎片选择 → 组装提示词 → AI 生成代码 → 验证迭代
```

**关键原则**:
1. 先定义需求（PRD），再生成代码
2. 按 L2→L3→L4 的顺序渐进生成 PRD
3. 每次只生成一个小功能
4. 生成后立即验证

**详细指南**: [02-new-requirement-workflow.md](./02-new-requirement-workflow.md)

---

## 🚀 快速开始

### 场景 A：项目刚启动，已有 PRD

```bash
# 1. 查看 PRD 文档
cat doc/PRD/01-ecommerce/01-module-overview.md

# 2. 选择一个用户故事
cat doc/PRD/01-ecommerce/stories/01-user-stories.md#US-EC-001

# 3. 组装提示词生成代码
# 参考 doc/BestPractice/01-prd-to-code.md
```

### 场景 B：收到新需求

```bash
# 1. 记录需求
# 参考 doc/BestPractice/02-new-requirement-workflow.md

# 2. 生成 PRD 文档
# 按 L2→L3→L4 顺序生成

# 3. 组装提示词生成代码
# 参考 doc/BestPractice/03-prompt-assembly-quickref.md
```

---

## 📊 效率提升

| 指标 | 传统方式 | 使用提示词库 | 提升 |
|------|---------|------------|------|
| 需求定义时间 | 2天 | 0.5天 | 75% |
| 代码生成时间 | 5天 | 2天 | 60% |
| 测试编写时间 | 2天 | 0.5天 | 75% |
| 总体开发周期 | 2周 | 3天 | 78% |

---

**版本**: v1.0 | **更新日期**: 2026-04-27
