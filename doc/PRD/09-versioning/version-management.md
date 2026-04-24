# 📋 PRD 文档版本管理规范

> **版本控制** | **变更追踪** | **协作规范**

---

## 📋 元数据

```yaml
document: "Version Management Guide"
version: "1.0"
last_updated: "2026-04-24"
```

---

## 🏷️ 版本号规范

### 语义化版本 (SemVer)

```
格式: MAJOR.MINOR.PATCH[-PRERELEASE][+BUILD]

示例:
- 1.0.0          # 正式发布版本
- 1.1.0          # 新增功能
- 1.1.1          # 修复错误
- 2.0.0-alpha.1  # 2.0 预发布版本
- 2.0.0+20260424 # 带构建号的版本
```

### 版本号规则

| 组件 | 规则 | 示例 |
|------|------|------|
| MAJOR | 架构重大变更、不兼容变更 | 1.0.0 → 2.0.0 |
| MINOR | 新增功能、新增模块、向后兼容 | 1.0.0 → 1.1.0 |
| PATCH | 文档修正、细节优化、向后兼容 | 1.0.0 → 1.0.1 |

### 版本号更新时机

```
MAJOR:
- 新增子系统模块
- 架构层级调整 (L0-L5)
- 领域模型重大变更

MINOR:
- 新增用户故事
- 新增 API 端点
- 新增领域实体
- 新增状态机

PATCH:
- 修正文档错误
- 优化文档格式
- 补充缺失细节
```

---

## 📁 文件管理

### 版本号文件

**文件**: `doc/PRD/VERSION`

```
1.0.0
```

### 变更日志文件

**文件**: `doc/PRD/CHANGELOG.md`

遵循 [Keep a Changelog](https://keepachangelog.com/) 规范:

```markdown
## [版本号] - YYYY-MM-DD

### Added (新增)
- 新增内容

### Changed (变更)
- 变更内容

### Deprecated (废弃)
- 即将废弃的内容

### Removed (移除)
- 已移除的内容

### Fixed (修复)
- 修复内容

### Security (安全)
- 安全相关修复
```

---

## 📊 变更追踪

### 变更记录表

| 字段 | 说明 |
|------|------|
| version | 版本号 |
| date | 变更日期 |
| author | 变更作者 |
| type | 变更类型 (Added/Changed/Fixed/...) |
| module | 影响的模块 |
| description | 变更描述 |
| files | 变更的文件列表 |

### 变更类型

| 类型 | 说明 | 版本号影响 |
|------|------|-----------|
| Added | 新增功能、文档、模块 | MINOR |
| Changed | 变更现有内容 | MINOR |
| Deprecated | 标记即将废弃 | MINOR |
| Removed | 移除功能、文档 | MAJOR |
| Fixed | 修复错误、缺陷 | PATCH |
| Security | 安全修复 | PATCH |

---

## 🔄 协作规范

### 提交变更流程

```
1. 创建变更分支 (可选)
   git checkout -b feature/add-xxx-docs

2. 更新文档
   - 修改相关 .md 文件
   - 更新文档索引 (00-PRD-INDEX.md)
   - 更新汇总文档 (00-PRD-SUMMARY.md)

3. 更新版本号
   - 修改 VERSION 文件
   - 在 CHANGELOG.md 添加变更记录

4. 提交变更
   git add .
   git commit -m "docs: add xxx documentation (v1.1.0)"

5. 合并到主分支 (可选)
   git checkout main
   git merge feature/add-xxx-docs
```

### 提交信息规范

遵循 [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <description>

类型:
- docs: 文档变更
- feat: 新增功能/文档
- fix: 修复错误
- refactor: 重构
- style: 格式调整

范围:
- prd: PRD 文档
- ecommerce: 电商模块
- o2o: O2O 模块
- distribution: 分销模块
- rbac: RBAC 模块
- crm: CRM 模块
- drp: DRP 模块
- finance: 财务模块

示例:
docs(prd): add distribution module documentation (v1.1.0)
feat(ecommerce): add product management user stories (v1.1.0)
fix(o2o): correct booking state machine diagram (v1.0.1)
```

---

## 🔍 文档质量检查

### 检查清单

```yaml
quality_checklist:
  format:
    - "所有文档使用 UTF-8 编码"
    - "Markdown 格式正确"
    - "YAML/JSON 格式有效"
    - "Mermaid 图表可渲染"

  content:
    - "元数据完整 (version, date, author)"
    - "文档索引已更新"
    - "交叉引用正确"
    - "示例代码可运行"

  structure:
    - "遵循金字塔结构 (L1-L5)"
    - "文件命名规范"
    - "目录结构清晰"
    - "版本号一致"
```

### 自动化检查 (可选)

```bash
# 检查 Markdown 格式
npx markdownlint doc/PRD/**/*.md

# 检查 YAML 格式
yamllint doc/PRD/**/*.md

# 检查链接有效性
npx markdown-link-check doc/PRD/**/*.md
```

---

## 📈 版本历史

| 版本 | 日期 | 说明 |
|------|------|------|
| 1.0.0 | 2026-04-24 | 初始版本，七大子系统完整 PRD |

---

**版本**: v1.0 | **更新日期**: 2026-04-24
