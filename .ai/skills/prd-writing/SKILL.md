---
name: prd-writing
description: "Apply this skill for writing and reviewing Product Requirements Documents (PRDs) in this Laravel + Filament project. Covers: requirement analysis, user story writing, domain modeling, API contract definition, state machine design, and PRD documentation standards. Use whenever creating or updating PRD documents, analyzing business requirements, or designing new feature modules."
license: MIT
metadata:
  author: laravel-filament
---

# PRD Writing — 产品需求文档编写

> **技术栈**: Laravel 12 + Filament 3.x + PHP 8.5
> **文档格式**: RAG 友好，金字塔结构
> **PRD 目录**: `doc/PRD/{模块}/`

---

## PRD 文档结构

每个模块的 PRD 应包含以下文件：

```
doc/PRD/{模块}/
├── 01-module-overview.md    # 模块概述
├── apis/
│   └── api-contracts.md     # API 契约定义
├── models/
│   └── domain-models.md     # 领域模型定义
├── states/
│   └── state-machines.md    # 状态机设计（如适用）
├── events/
│   └── domain-events.md     # 领域事件定义（如适用）
└── stories/
    └── 01-user-stories.md   # 用户故事
```

## 编写流程

### Step 1: 需求收集与分析
- 理解业务目标和用户需求
- 识别核心用户角色和使用场景
- 分析现有系统约束和技术限制

### Step 2: 模块概述
- 定义模块名称和定位
- 描述业务价值和目标
- 列出关键功能点

### Step 3: 用户故事编写
遵循标准用户故事格式：
```
作为 [角色]，我希望 [功能]，以便 [价值]
```

### Step 4: 领域模型设计
- 识别核心实体和聚合根
- 定义实体属性和关系
- 设计实体生命周期

### Step 5: API 契约定义
- 定义 RESTful API 端点
- 描述请求/响应结构
- 定义错误码和状态码

### Step 6: 状态机设计（如适用）
- 定义状态枚举
- 描述状态流转规则
- 定义触发条件

## 文档规范

- 使用 Markdown 格式
- 保持文档简洁清晰
- 使用代码块展示示例
- 保持术语一致
- 在相关文档间建立交叉引用

---

## 模块概述模板

```markdown
# {模块名称} 模块

## 1. 模块概述

### 1.1 业务背景
{描述该模块的业务背景和产生的原因}

### 1.2 业务目标
- 目标1: {具体目标}
- 目标2: {具体目标}
- 目标3: {具体目标}

### 1.3 用户角色
| 角色 | 描述 | 权限 |
|------|------|------|
| {角色1} | {描述} | {权限列表} |
| {角色2} | {描述} | {权限列表} |

## 2. 功能清单

| 功能 | 优先级 | 描述 |
|------|--------|------|
| {功能1} | P0 | {描述} |
| {功能2} | P1 | {描述} |

## 3. 业务流程
{描述核心业务流程，可使用流程图}

## 4. 数据模型
{列出核心实体和关系}
```

## 用户故事模板

```markdown
# 用户故事

## 故事 1: {标题}

**作为** {用户角色}
**我想要** {功能描述}
**以便** {业务价值}

### 验收标准
- [ ] {标准1}
- [ ] {标准2}
- [ ] {标准3}

### 技术要点
- {要点1}
- {要点2}

### 依赖关系
- {依赖1}
- {依赖2}

### 优先级
P0 | P1 | P2

### 估算
{故事点}
```

## API 契约模板

```markdown
# API 契约

## 接口列表

| 方法 | 路径 | 描述 | 认证 | 权限 |
|------|------|------|------|------|
| GET | /api/{resource} | 获取列表 | 是 | {permission} |
| POST | /api/{resource} | 创建资源 | 是 | {permission} |
| GET | /api/{resource}/{id} | 获取详情 | 是 | {permission} |
| PUT | /api/{resource}/{id} | 更新资源 | 是 | {permission} |
| DELETE | /api/{resource}/{id} | 删除资源 | 是 | {permission} |

## 详细定义

### 获取列表

**请求**
- 方法: GET
- 路径: /api/{resource}
- 参数:
  - page: 页码 (默认 1)
  - per_page: 每页数量 (默认 20)
  - search: 搜索关键词
  - sort_by: 排序字段
  - sort_order: 排序方向 (asc/desc)

**响应**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "name": "示例",
      "created_at": "2026-07-18T10:00:00Z"
    }
  ],
  "meta": {
    "total": 100,
    "per_page": 20,
    "current_page": 1,
    "last_page": 5
  }
}
```

**错误码**
| 错误码 | 描述 |
|--------|------|
| 401 | 未认证 |
| 403 | 无权限 |
```

## 领域模型模板

```markdown
# 领域模型

## 实体列表

| 实体 | 描述 | 聚合根 |
|------|------|--------|
| {Entity} | {描述} | 是/否 |

## 实体定义

### {Entity}

**属性**
| 属性 | 类型 | 约束 | 描述 |
|------|------|------|------|
| id | bigint | PK | 主键 |
| name | string | required | 名称 |
| status | enum | required | 状态 |
| amount | decimal(10,2) | required | 金额 |

**关系**
| 关系 | 类型 | 关联实体 | 描述 |
|------|------|----------|------|
| {relation} | BelongsTo | {Entity} | {描述} |

**状态机**
```
{状态1} → {状态2}: {触发条件}
{状态2} → {状态3}: {触发条件}
```
```

## 状态机模板

```markdown
# 状态机设计

## {实体} 状态

### 状态定义
| 状态 | 值 | 描述 | 颜色 |
|------|-----|------|------|
| {状态1} | {值} | {描述} | {颜色} |
| {状态2} | {值} | {描述} | {颜色} |

### 状态流转
```
[{状态1}] --{触发条件}--> [{状态2}]
[{状态2}] --{触发条件}--> [{状态3}]
[{状态2}] --{触发条件}--> [{状态4}]
```

### 流转规则
| 当前状态 | 目标状态 | 触发条件 | 操作 |
|----------|----------|----------|------|
| {状态1} | {状态2} | {条件} | {操作} |
| {状态2} | {状态3} | {条件} | {操作} |
```