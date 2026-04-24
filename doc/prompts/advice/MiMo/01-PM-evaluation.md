# 01-PM.md 提示词模板评估报告

> **评估日期**: 2026-04-24  
> **评估者**: MiMo  
> **评估对象**: `doc/prompts/test/01-PM.md` — AI 产品经理角色提示词模板  
> **评估版本**: v1.0

---

## 一、评估摘要

| 维度 | 评分 (1-5) | 评级 |
|------|-----------|------|
| 可行性 | 4.0 | ★★★★☆ |
| 有效性 | 3.5 | ★★★☆☆ |
| 完整性 | 3.0 | ★★★☆☆ |
| 提示质量 | 4.0 | ★★★★☆ |
| **综合评分** | **3.6** | **★★★☆☆** |

---

## 二、分维度详细评估

### 2.1 可行性评估 (Feasibility) — 4.0/5

**优势**:
- ✅ **分层架构清晰**: L0-L4 的层级设计符合认知逻辑，从元数据感知到任务指令层层递进
- ✅ **技术栈明确**: Laravel 12 + Filament 3.x + MySQL 8.0 的技术选型具体且可执行
- ✅ **引用机制合理**: `@list_dir()` 和 `@doc/prompts/cards/...` 的引用语法为 RAG 系统提供了明确的检索锚点
- ✅ **输出结构化**: Markdown 格式要求明确，便于后续自动化处理

**不足**:
- ⚠️ **引用语法模糊**: `@list_dir()` 和 `@template-xxx.md` 的具体解析规则未定义，依赖下游系统实现
- ⚠️ **缺少错误处理**: 若引用的卡片文件不存在，未定义降级策略

**可行性评分说明**: 整体可行，但引用机制需要明确的解析规范支撑。

---

### 2.2 有效性评估 (Effectiveness) — 3.5/5

**优势**:
- ✅ **目标导向明确**: 明确指向"生成 Laravel 博客系统需求文档"这一具体产出
- ✅ **下游衔接设计**: 每个文档结构部分都标注了对应的目的模板（如 `@template-migration-generation.md`），体现了管道化思维
- ✅ **核心原则覆盖**: 类型安全、DDD、TDD 三大原则对齐了工程实践

**不足**:
- ⚠️ **缺乏示例输出**: 没有提供任何示例片段，模型需要完全自主推断输出格式
- ⚠️ **角色能力边界模糊**: "精通 DDD 的产品经理"这一角色设定过于宽泛，缺少具体能力约束
- ⚠️ **状态机描述不足**: 仅提及"草稿 -> 审核 -> 发布 -> 归档"流程，未定义状态流转的具体触发条件格式
- ⚠️ **缺少成功标准**: 未定义"什么样的输出算合格"，难以进行自动化质量评估

**有效性评分说明**: 方向正确，但缺乏具体的输出规范和示例，导致模型输出可能存在较大方差。

---

### 2.3 完整性评估 (Completeness) — 3.0/5

**优势**:
- ✅ **核心要素覆盖**: 领域模型、业务流程、API 契约、Admin UI 四大模块基本完整
- ✅ **规范引用**: 数据库规范、Filament 规范、API 规范均有涉及

**不足**:
- ❌ **缺少非功能性需求**: 性能、安全、可观测性、审计日志等未涉及
- ❌ **缺少验收标准**: 每个模块未定义验收准则，无法直接转化为测试用例
- ❌ **缺少错误处理**: API 错误响应格式、异常处理策略未定义
- ❌ **缺少权限模型**: 用户角色、权限控制策略未涉及
- ❌ **缺少数据迁移策略**: 新旧数据迁移、种子数据未涉及
- ❌ **缺少版本控制**: 文档版本、变更日志机制未定义
- ❌ **缺少约束条件**: 字段的唯一性、索引、外键约束等细节未明确要求

**完整性评分说明**: 覆盖了核心功能需求，但系统级需求（非功能性、安全性、运维性）严重缺失。

---

### 2.4 提示质量评估 (Prompt Quality) — 4.0/5

**优势**:
- ✅ **结构化程度高**: Markdown 层级清晰，信息密度适中
- ✅ **指令明确**: 任务指令部分使用了祈使句，动作指向明确
- ✅ **约束条件清晰**: 核心原则部分提供了明确的"必须遵循"约束
- ✅ **目的标注**: 每个文档结构部分都标注了目的，增强了可解释性

**不足**:
- ⚠️ **缺少 Few-shot 示例**: 没有提供任何示例片段作为参考
- ⚠️ **缺少输出格式 Schema**: 字段字典、API 契约等缺少明确的 JSON/结构化格式定义
- ⚠️ **缺少负面约束**: 未明确"不要做什么"，可能导致模型生成冗余内容
- ⚠️ **元数据注入不完整**: L0 仅注入技术栈和领域，缺少项目规模、团队信息等上下文

**提示质量评分说明**: 结构良好，但缺乏示例和精确的输出格式定义，属于"中等偏上"水平。

---

## 三、关键问题清单

### 3.1 高优先级问题

| # | 问题 | 影响 | 建议修复方案 |
|---|------|------|-------------|
| 1 | **引用语法未定义** | `@list_dir()`、`@template-xxx.md` 等引用语法无解析规范 | 定义引用语法规范文档，明确解析规则和降级策略 |
| 2 | **输出格式无 Schema** | 字段字典、API 契约等无结构化格式定义，输出方差大 | 为每个输出模块定义 JSON Schema 或结构化模板 |
| 3 | **缺少验收标准** | 无法自动化验证输出质量 | 为每个模块定义验收检查清单（Checklist） |

### 3.2 中优先级问题

| # | 问题 | 影响 | 建议修复方案 |
|---|------|------|-------------|
| 4 | **缺少非功能性需求** | 系统级需求缺失，产出文档不完整 | 在 L4 中增加非功能性需求章节 |
| 5 | **状态机描述不精确** | 状态流转条件模糊，难以转化为代码 | 定义状态机描述模板（状态、事件、条件、动作） |
| 6 | **缺少示例输出** | 模型需完全自主推断，一致性差 | 提供至少一个实体的完整示例输出 |

### 3.3 低优先级问题

| # | 问题 | 影响 | 建议修复方案 |
|---|------|------|-------------|
| 7 | **角色能力边界模糊** | 模型可能生成超出角色范围的内容 | 明确角色的能力边界和职责范围 |
| 8 | **缺少版本控制** | 文档迭代缺乏追踪机制 | 在 L0 中增加版本、作者、日期元数据 |
| 9 | **缺少负面约束** | 可能生成冗余或不相关内容 | 增加"不要做什么"的约束说明 |

---

## 四、优化建议

### 4.1 结构优化建议

#### 4.1.1 增加 L0 元数据头部 Schema

建议在 L0 中增加结构化元数据头部：

```markdown
## 1. L0: 项目元数据感知 (Meta-Data Injection)

### 1.1 项目基础信息
- **项目名称**: {project_name}
- **技术栈**: Laravel 12 + Filament 3.x + MySQL 8.0
- **项目规模**: {小型|中型|大型}
- **团队规模**: {1-3人|4-10人|10+人}

### 1.2 上下文注入
- **现有领域**: @list_dir('app/Models')
- **规范参考**: @doc/prompts/cards/02-context/filament-best-practices.md
- **领域卡片**: @doc/prompts/cards/03-domains/*.md

### 1.3 元数据头（输出时生成）
```yaml
version: "1.0"
author: "{author_name}"
date: "{YYYY-MM-DD}"
project: "{project_name}"
status: "{draft|review|approved}"
```
```

#### 4.1.2 增加 L5: 输出质量标准

建议新增 L5 层级，定义输出质量标准：

```markdown
## 6. L5: 输出质量标准 (Output Quality Standards)

### 6.1 验证清单
- [ ] 所有实体字段都有明确的 PHP 类型声明
- [ ] 所有 API 接口都有请求/响应示例
- [ ] 所有状态流转都有明确的触发条件
- [ ] 所有 Filament 资源都有列、筛选器、操作定义

### 6.2 质量指标
- **字段覆盖率**: ≥ 95% 的实体字段有类型、约束、默认值定义
- **API 覆盖率**: 100% 的核心实体都有 CRUD 接口
- **状态机完整性**: 所有状态流转路径都有明确定义

### 6.3 负面约束
- 不要生成伪代码或占位符
- 不要省略任何字段的类型定义
- 不要使用模糊的业务描述（如"适当的验证"）
```

### 4.2 内容优化建议

#### 4.2.1 领域模型定义优化

建议增加字段字典的结构化模板：

```markdown
### 字段字典格式要求

每个字段需包含以下属性：

| 属性 | 类型 | 必填 | 说明 |
|------|------|------|------|
| name | string | ✅ | 字段名（snake_case） |
| type | string | ✅ | PHP 类型（string/int/bool/array/float/Carbon） |
| db_type | string | ✅ | 数据库类型（varchar/int/boolean/json/decimal/datetime） |
| nullable | bool | ✅ | 是否允许 NULL |
| unique | bool | ✅ | 是否唯一 |
| indexed | bool | ✅ | 是否需要索引 |
| default | any | ❌ | 默认值 |
| constraints | array | ❌ | 约束条件（如 min/max/regex） |
| foreign_key | string | ❌ | 外键关联（格式：table.column） |
| comment | string | ✅ | 中文注释 |
```

#### 4.2.2 API 契约优化

建议增加 API 契约的结构化模板：

```markdown
### API 契约格式要求

每个接口需包含：

```yaml
method: GET|POST|PUT|PATCH|DELETE
path: /api/{resource}[/{id}]
description: 接口描述
auth: true|false
request:
  params:
    - name: param_name
      type: string|integer|boolean|array
      required: true|false
      description: 参数说明
  body: # 仅 POST/PUT/PATCH
    schema: { ... }
response:
  success:
    code: 200
    schema: { ... }
  errors:
    - code: 422
      description: 验证失败
    - code: 401
      description: 未授权
```
```

#### 4.2.3 状态机优化

建议增加状态机的结构化描述模板：

```markdown
### 状态机格式要求

```yaml
entity: Post
states:
  - name: draft
    description: 草稿
  - name: pending_review
    description: 待审核
  - name: published
    description: 已发布
  - name: archived
    description: 已归档

transitions:
  - from: draft
    to: pending_review
    event: submit_for_review
    conditions:
      - "title 不能为空"
      - "content 长度 ≥ 100"
    actions:
      - "设置 reviewed_at 为 null"
      - "发送通知给审核者"

  - from: pending_review
    to: published
    event: approve
    conditions:
      - "当前用户角色为 admin 或 editor"
    actions:
      - "设置 published_at 为当前时间"
      - "发送通知给作者"
```
```

### 4.3 引用机制优化建议

#### 4.3.1 定义引用语法规范

建议定义明确的引用语法：

```markdown
### 引用语法规范

| 语法 | 含义 | 解析规则 |
|------|------|----------|
| `@list_dir('path')` | 列出目录内容 | 解析为指定目录的文件列表 |
| `@doc/prompts/cards/xxx.md` | 引用卡片内容 | 解析为指定路径的卡片文件内容 |
| `@{role_name}` | 引用角色定义 | 解析为角色卡片的完整内容 |
| `@template-xxx.md` | 引用任务模板 | 解析为指定任务模板的完整内容 |

### 降级策略

若引用的文件不存在：
1. 记录警告日志
2. 使用默认模板生成
3. 在输出中标注"⚠️ 引用缺失: {reference}"
```

---

## 五、优化后的模板草案

### 5.1 优化版 L0: 项目元数据感知

```markdown
## 1. L0: 项目元数据感知 (Meta-Data Injection)

### 1.1 项目基础信息
- **项目名称**: {project_name}
- **技术栈**: Laravel 12 + Filament 3.x + MySQL 8.0
- **项目规模**: {小型|中型|大型}
- **现有领域**: @list_dir('app/Models')

### 1.2 规范引用
- **Filament 规范**: @doc/prompts/cards/02-context/filament-best-practices.md
- **领域约束**: @doc/prompts/cards/03-domains/*.md
- **任务模板**: @doc/prompts/cards/04-tasks/*.md

### 1.3 输出元数据头（自动生成）
```yaml
version: "1.0"
author: "{author_name}"
date: "{YYYY-MM-DD}"
project: "{project_name}"
status: "draft"
```
```

### 5.2 优化版 L4: 任务指令

```markdown
## 5. L4: 任务指令 (Task Instruction)

请为我生成一份 **"Laravel 博客系统"** 的详细需求文档。

### 文档结构要求

#### 5.1 领域模型定义 (Domain Models)

**实体列表**: Post, Category, Comment, Tag, User

**字段字典格式**（每个字段必须包含）:

| 字段名 | PHP类型 | DB类型 | 可空 | 唯一 | 索引 | 默认值 | 约束 | 外键 | 注释 |
|--------|---------|--------|------|------|------|--------|------|------|------|
| id | int | bigint | ❌ | ✅ | ✅ | - | - | - | 主键 |
| title | string | varchar(255) | ❌ | ❌ | ✅ | - | min:1, max:255 | - | 文章标题 |
| ... | ... | ... | ... | ... | ... | ... | ... | ... | ... |

**目的**: 方便调用 `@template-migration-generation.md` 生成迁移文件。

#### 5.2 业务流程与状态机 (State Machine)

**状态流转定义格式**:

```yaml
entity: Post
states: [draft, pending_review, published, archived]
transitions:
  - from: draft → pending_review
    event: submit_for_review
    conditions: ["title 非空", "content ≥ 100字"]
    actions: ["通知审核者"]
```

**目的**: 方便调用 `@template-service-layer.md` 生成服务层代码。

#### 5.3 API 接口契约 (API Contracts)

**接口定义格式**:

```yaml
method: POST
path: /api/posts
auth: true
request:
  - name: title
    type: string
    required: true
    rules: ["min:1", "max:255"]
response:
  success: { code: 200, schema: PostResource }
  errors: [{ code: 422, desc: "验证失败" }]
```

**目的**: 方便调用 `@template-dto-conversion.md` 生成 DTO。

#### 5.4 Filament 后台功能清单 (Admin UI)

**资源定义格式**:

```yaml
resource: PostResource
table:
  columns: [id, title, status, created_at]
  filters: [status, category_id]
  actions: [edit, delete, publish]
form:
  fields: [title, content, category_id, tags, status]
```

**目的**: 方便调用 `@filament-ui-designer.md` 生成 Filament Resource。

### 质量检查清单

- [ ] 所有实体字段都有完整的类型和约束定义
- [ ] 所有 API 接口都有请求/响应示例
- [ ] 所有状态流转都有触发条件和动作
- [ ] 所有 Filament 资源都有列、筛选器、操作定义
- [ ] 无占位符或伪代码
```

---

## 六、实施路线图

### Phase 1: 基础修复（1-2天）
- [ ] 定义引用语法规范文档
- [ ] 增加输出元数据头定义
- [ ] 增加负面约束说明

### Phase 2: 格式标准化（3-5天）
- [ ] 为领域模型定义 JSON Schema
- [ ] 为 API 契约定义 JSON Schema
- [ ] 为状态机定义结构化模板

### Phase 3: 质量增强（1周）
- [ ] 增加验收检查清单
- [ ] 增加非功能性需求章节
- [ ] 增加示例输出片段

### Phase 4: 集成测试（持续）
- [ ] 测试引用机制的解析正确性
- [ ] 验证输出格式的一致性
- [ ] 收集用户反馈并迭代

---

## 七、结论

### 7.1 总体评价

01-PM.md 是一个**结构良好但细节不足**的提示词模板。其分层架构（L0-L4）和管道化设计思路值得肯定，但在输出格式定义、验收标准、非功能性需求等方面存在明显缺失。

### 7.2 核心改进点

1. **定义引用语法规范**: 明确 `@list_dir()`、`@template-xxx.md` 等引用的解析规则和降级策略
2. **增加输出 Schema**: 为领域模型、API 套约、状态机等定义结构化格式
3. **增加验收标准**: 为每个模块定义可验证的质量检查清单
4. **补充非功能性需求**: 增加性能、安全、可观测性等章节

### 7.3 预期改进效果

| 指标 | 当前 | 优化后预期 |
|------|------|-----------|
| 输出一致性 | 中等 | 高 |
| 自动化验证可行性 | 低 | 高 |
| 下游模板衔接效率 | 中等 | 高 |
| 非功能性需求覆盖 | 无 | 有 |

---

*评估结束。如需进一步讨论或实施优化方案，请联系评估者。*
