---
name: git-workflow
description: "Use this skill for Git workflow and commit conventions in this Laravel + Filament project. Trigger when: 'commit code', 'push changes', 'create branch', 'merge PR', 'resolve conflict', 'rebase', 'cherry-pick', or any git operation. Covers: Conventional Commits, branch naming, PR workflow, merge strategies, and Git hooks. Ensures consistent Git history and collaboration."
license: MIT
metadata:
  author: laravel-filament
  version: 1.0.0
---

# Git Workflow — Git 工作流与提交规范

> **技术栈**: Laravel 12 + Filament 3.x + PHP 8.5
> **提交规范**: Conventional Commits
> **分支策略**: Git Flow (简化版)

---

## 核心原则

```
📝 规范提交 → 清晰的变更历史
🌿 分支管理 → 并行开发不冲突
🔍 代码审查 → 质量保证
🔄 持续集成 → 自动化验证
```

---

## Conventional Commits

### 提交格式

```
<type>(<scope>): <description>

[optional body]

[optional footer(s)]
```

### Type 类型

| Type | 说明 | 示例 |
|------|------|------|
| `feat` | 新功能 | `feat(order): add order creation API` |
| `fix` | 修复 Bug | `fix(auth): resolve login timeout issue` |
| `docs` | 文档更新 | `docs(readme): update installation guide` |
| `style` | 代码格式（不影响功能） | `style(code): apply pint formatting` |
| `refactor` | 重构（不新增功能/修复 Bug） | `refactor(service): extract validation logic` |
| `perf` | 性能优化 | `perf(query): add index for order search` |
| `test` | 测试相关 | `test(order): add unit tests for OrderService` |
| `chore` | 构建/工具/依赖更新 | `chore(deps): update laravel/framework to 12.0` |
| `ci` | CI 配置 | `ci(actions): add phpstan check` |
| `revert` | 回滚 | `revert: revert "feat(order): add bulk delete"` |

### Scope 范围

| Scope | 说明 |
|-------|------|
| `auth` | 认证相关 |
| `order` | 订单相关 |
| `product` | 商品相关 |
| `user` | 用户相关 |
| `api` | API 接口 |
| `filament` | Filament 后台 |
| `database` | 数据库相关 |
| `config` | 配置相关 |

### 示例

```bash
# 新功能
git commit -m "feat(order): add order creation API"

# 修复 Bug
git commit -m "fix(auth): resolve login timeout issue"

# 多行提交
git commit -m "feat(order): add bulk order export

- Add export endpoint for order list
- Support CSV and Excel formats
- Add date range filter

Closes #123"

# 破坏性变更
git commit -m "feat(api)!: change user response format

BREAKING CHANGE: User resource now returns nested profile object"
```

---

## 分支策略

### 分支类型

| 分支 | 用途 | 命名规则 | 示例 |
|------|------|----------|------|
| `main` | 生产分支 | - | `main` |
| `develop` | 开发分支 | - | `develop` |
| `feature/*` | 功能开发 | `feature/{ticket}-{description}` | `feature/123-add-order-api` |
| `bugfix/*` | Bug 修复 | `bugfix/{ticket}-{description}` | `bugfix/456-fix-login-timeout` |
| `hotfix/*` | 紧急修复 | `hotfix/{ticket}-{description}` | `hotfix/789-fix-security-vulnerability` |
| `release/*` | 发布准备 | `release/{version}` | `release/1.2.0` |
| `chore/*` | 工具/依赖更新 | `chore/{description}` | `chore/update-deps` |

### 分支操作

```bash
# 创建功能分支
git checkout develop
git pull origin develop
git checkout -b feature/123-add-order-api

# 开发完成后合并
git checkout develop
git merge --no-ff feature/123-add-order-api
git push origin develop

# 删除已合并分支
git branch -d feature/123-add-order-api
git push origin --delete feature/123-add-order-api
```

---

## PR 工作流

### PR 标题规范

```
<type>(<scope>): <description>
```

### PR 模板

```markdown
## 变更说明
{描述本次变更的内容和原因}

## 变更类型
- [ ] 新功能 (feat)
- [ ] Bug 修复 (fix)
- [ ] 重构 (refactor)
- [ ] 文档更新 (docs)
- [ ] 测试 (test)
- [ ] 其他 (chore)

## 影响范围
- [ ] API 接口
- [ ] 数据库
- [ ] 后台管理
- [ ] 前端页面

## 测试情况
- [ ] 单元测试通过
- [ ] 集成测试通过
- [ ] 手动测试完成

## 相关文档
- 需求文档: REQ-{模块}-{序号}
- 设计文档: DES-{模块}-{序号}

## 截图/录屏
{如有 UI 变更，提供截图或录屏}

## Checklist
- [ ] 代码符合 Pint 规范
- [ ] PHPStan 分析通过
- [ ] 所有测试通过
- [ ] 文档已更新
- [ ] 无安全漏洞
```

---

## 提交检查清单

### 提交前检查

```bash
# 1. 代码格式检查
./vendor/bin/pint --test

# 2. 静态分析
./vendor/bin/phpstan analyse --no-progress

# 3. 运行测试
./vendor/bin/pest --compact

# 4. 检查未跟踪文件
git status

# 5. 检查变更内容
git diff
git diff --cached
```

### 自动化检查 (Git Hooks)

```bash
# 安装 Git Hooks
composer install

# pre-commit hook (自动运行 Pint)
#!/bin/bash
./vendor/bin/pint

# commit-msg hook (验证提交信息)
#!/bin/bash
commit_msg=$(cat "$1")
pattern="^(feat|fix|docs|style|refactor|perf|test|chore|ci|revert)(\([a-z-]+\))?: .{1,72}"

if ! echo "$commit_msg" | grep -qE "$pattern"; then
    echo "❌ 提交信息不符合 Conventional Commits 规范"
    echo "格式: <type>(<scope>): <description>"
    echo "示例: feat(order): add order creation API"
    exit 1
fi
```

---

## 合并策略

### 合并方式

| 方式 | 适用场景 | 命令 |
|------|----------|------|
| `--no-ff` | 功能分支合并 | `git merge --no-ff feature/xxx` |
| `--squash` | 压缩多个提交 | `git merge --squash feature/xxx` |
| `rebase` | 保持线性历史 | `git rebase develop` |

### 合并冲突处理

```bash
# 1. 拉取最新代码
git fetch origin

# 2. 尝试合并
git merge origin/develop

# 3. 解决冲突
# 编辑冲突文件，选择保留的代码

# 4. 标记冲突已解决
git add .

# 5. 完成合并
git commit -m "merge: resolve conflicts with develop"
```

---

## 发布流程

### 版本号规范 (SemVer)

```
MAJOR.MINOR.PATCH

MAJOR: 破坏性变更
MINOR: 新功能（向后兼容）
PATCH: Bug 修复（向后兼容）
```

### 发布步骤

```bash
# 1. 创建发布分支
git checkout develop
git checkout -b release/1.2.0

# 2. 更新版本号
# 修改 config/app.php 中的 version

# 3. 更新 CHANGELOG
# 更新 CHANGELOG.md

# 4. 提交
git commit -m "chore(release): prepare for v1.2.0"

# 5. 合并到 main
git checkout main
git merge --no-ff release/1.2.0
git tag -a v1.2.0 -m "Release v1.2.0"

# 6. 合并回 develop
git checkout develop
git merge --no-ff release/1.2.0

# 7. 推送
git push origin main --tags
git push origin develop

# 8. 删除发布分支
git branch -d release/1.2.0
```

---

## 常见场景

### 场景 1: 开发新功能

```bash
# 1. 创建分支
git checkout develop
git pull origin develop
git checkout -b feature/123-add-order-api

# 2. 开发
# 编写代码...

# 3. 提交
git add .
git commit -m "feat(order): add order creation API"

# 4. 推送
git push origin feature/123-add-order-api

# 5. 创建 PR
# 在 GitHub/GitLab 上创建 PR
```

### 场景 2: 修复紧急 Bug

```bash
# 1. 从 main 创建 hotfix
git checkout main
git pull origin main
git checkout -b hotfix/456-fix-security-vulnerability

# 2. 修复
# 编写修复代码...

# 3. 提交
git add .
git commit -m "fix(security): resolve SQL injection vulnerability"

# 4. 合并到 main 和 develop
git checkout main
git merge --no-ff hotfix/456-fix-security-vulnerability
git tag -a v1.2.1 -m "Hotfix v1.2.1"

git checkout develop
git merge --no-ff hotfix/456-fix-security-vulnerability

# 5. 推送
git push origin main --tags
git push origin develop
```

### 场景 3: 代码审查

```bash
# 1. 拉取 PR
git fetch origin
git checkout feature/123-add-order-api
git merge origin/develop

# 2. 处理审查反馈
# 修改代码...

# 3. 提交修改
git add .
git commit -m "fix(order): address code review feedback"

# 4. 推送
git push origin feature/123-add-order-api
```

---

## 禁止操作

| 操作 | 原因 | 替代方案 |
|------|------|----------|
| `git push --force` | 会覆盖远程历史 | `git push --force-with-lease` |
| `git commit --amend` (已推送) | 会改变提交哈希 | 创建新提交 |
| `git rebase` (已推送分支) | 会改变提交历史 | `git merge` |
| 直接提交到 main | 跳过代码审查 | 通过 PR 合并 |

---

## 快速命令

```bash
# 查看状态
git status
git log --oneline -10
git diff --stat

# 撤销操作
git reset HEAD .                    # 取消暂存
git checkout -- .                   # 丢弃工作区变更
git reset --soft HEAD~1             # 撤销最后一次提交（保留变更）
git reset --hard HEAD~1             # 撤销最后一次提交（丢弃变更）

# 暂存工作
git stash
git stash pop
git stash list

# 查看历史
git log --graph --oneline --all
git log --author="username"
git log --since="2026-01-01"
```
