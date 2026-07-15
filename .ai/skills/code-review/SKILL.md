---
name: code-review
description: "Apply this skill for AI-assisted code review with static analysis in this Laravel + Filament project. Covers: automated checks (Pint, PHPStan, Pest), architectural review (DDD layer violations, dependency direction), security audit, performance analysis, and Filament convention compliance. Use whenever reviewing code changes, PRs, or doing a pre-commit quality gate."
license: MIT
metadata:
  author: laravel-filament
---

# Code Review — 代码审查 + 静态分析

> **技术栈**: Laravel 12 + Filament 3.x + PHP 8.5 + Pest
> **审查原则**: 自动化检查 → 架构审查 → 安全审计 → 性能分析

---

## 审查流程

```
┌─────────────────────────────────────────────────────┐
│  Phase 1: 自动化检查 (必须全部通过)                    │
│  Pint → PHPStan → Pest → Filament Convention        │
├─────────────────────────────────────────────────────┤
│  Phase 2: 架构审查 (人工/AI 判断)                     │
│  DDD 分层 → 依赖方向 → 职责边界                       │
├─────────────────────────────────────────────────────┤
│  Phase 3: 安全审计                                   │
│  输入验证 → 权限控制 → SQL 注入 → 敏感数据             │
├─────────────────────────────────────────────────────┤
│  Phase 4: 性能分析                                   │
│  N+1 查询 → 缓存策略 → 索引覆盖 → 内存使用            │
└─────────────────────────────────────────────────────┘
```

---

## Phase 1: 自动化检查

### 1.1 Pint 格式检查

```bash
# CI 模式：只检查不修改
./vendor/bin/pint --test

# 检查指定文件
./vendor/bin/pint --test app/Services/OrderService.php
```

**必须通过**:
- [ ] `declare(strict_types=1)` 存在
- [ ] import 已排序 (class → function → const)
- [ ] 无未使用的 import
- [ ] 短数组语法 `[]`
- [ ] 类成员顺序正确
- [ ] 多行末尾逗号

### 1.2 PHPStan 静态分析

```bash
./vendor/bin/phpstan analyse --no-progress
```

**Level 5 检查项**:
- [ ] 返回类型声明
- [ ] 参数类型声明
- [ ] 可能为 null 的值处理
- [ ] 未定义的属性/方法
- [ ] 死代码检测

### 1.3 Pest 测试

```bash
./vendor/bin/pest --compact
```

**必须通过**:
- [ ] 所有测试通过
- [ ] 新代码有对应测试
- [ ] 测试覆盖核心逻辑

### 1.4 Filament 资源规范

```bash
find app/Infrastructure/Filament/Resources -name '*Resource.php' -print0 | while IFS= read -r -d '' file; do
    grep -q "public static function form" "$file" || echo "MISSING form(): $file"
    grep -q "public static function table" "$file" || echo "MISSING table(): $file"
done
```

---

## Phase 2: 架构审查

### 2.1 DDD 分层违规检查

```bash
# Domain 层不应依赖 Filament
grep -rn "Filament\\" app/Domains/ 2>/dev/null && echo "❌ VIOLATION: Domain depends on Filament"

# Domain 层不应依赖 Http
grep -rn "Illuminate\\Http\\Request" app/Domains/ 2>/dev/null && echo "❌ VIOLATION: Domain depends on Http"

# Service 层不应直接使用 Request
grep -rn "Illuminate\\Http\\Request" app/Services/ 2>/dev/null && echo "❌ VIOLATION: Service uses Request"

# Filament Resource 不应包含业务逻辑
grep -rn "DB::transaction" app/Infrastructure/Filament/ 2>/dev/null && echo "⚠️  WARNING: Filament contains DB transaction"

# Controller 应该是瘦控制器
wc -l app/Http/Controllers/Api/*.php 2>/dev/null | awk '$1 > 100 {print "⚠️  WARNING: " $2 " has " $1 " lines (>100)"}'
```

### 2.2 依赖方向审查

```
Http → Infrastructure → Domain ← (不应被依赖)
         ↓
    Repositories (实现 Domain 接口)
```

**检查清单**:
- [ ] Domain 层只依赖 PHP 标准库
- [ ] Infrastructure 实现 Domain 定义的接口
- [ ] Http 只调用 Service，不直接操作 Eloquent
- [ ] 跨域调用通过事件/消息，不直接引用

### 2.3 职责边界审查

| 检查项 | ✅ 正确 | ❌ 错误 |
|--------|---------|---------|
| Controller | 调用 Service、返回响应 | 包含业务逻辑 |
| Service | 编排业务逻辑、调用 Repository | 直接使用 Request |
| Model | 定义关系、属性转换 | 包含业务逻辑 |
| Filament Resource | 定义 UI 展示 | 扣减库存、计算价格 |
| DTO | 纯数据对象 | 包含验证逻辑 |

---

## Phase 3: 安全审计

### 3.1 输入验证检查

```bash
# Controller 应使用 FormRequest
grep -rn "function store\|function update" app/Http/Controllers/Api/*.php | grep -v "Request" && echo "⚠️  Missing FormRequest"

# 禁止 $request->all()
grep -rn "\\$request->all()" app/ && echo "❌ FORBIDDEN: \\$request->all()"

# 禁止 $request->input() 不带默认值
grep -rn "\\$request->input(" app/ | grep -v ", null)" | grep -v ", \[" && echo "⚠️  Check input() without default"
```

### 3.2 权限控制检查

```bash
# Filament Resource 应有 Policy
grep -rn "class.*Resource extends Resource" app/Infrastructure/Filament/ | while read line; do
    model=$(echo "$line" | grep -oP "model = \\K[^;]+")
    if ! grep -q "Policy" app/Domains/*/Policies/*.php 2>/dev/null; then
        echo "⚠️  Missing Policy for $model"
    fi
done

# API 路由应有 auth 中间件
grep -rn "Route::" routes/api.php | grep -v "auth" | grep -v "public" && echo "⚠️  Check auth middleware"
```

### 3.3 SQL 注入防护

```bash
# 禁止原生 SQL 拼接
grep -rn "DB::select.*\\$" app/ && echo "❌ Potential SQL injection"
grep -rn "DB::statement.*\\$" app/ && echo "❌ Potential SQL injection"
grep -rn "whereRaw.*\\$" app/ && echo "⚠️  Check whereRaw for SQL injection"
```

### 3.4 敏感数据检查

```bash
# 禁止日志记录密码
grep -rn "Log::.*password" app/ && echo "❌ FORBIDDEN: Logging password"

# 禁止返回密码字段
grep -rn "'password'" app/Http/Resources/ && echo "⚠️  Check password in API Resource"

# .env 不应提交
git ls-files | grep "\.env$" && echo "❌ .env file committed"
```

---

## Phase 4: 性能分析

### 4.1 N+1 查询检查

```bash
# Model 缺少 $with 或 eager loading
grep -rn "->get()" app/ | grep -v "with(" | grep -v "->first()" && echo "⚠️  Potential N+1: missing eager loading"

# Blade 中查询
grep -rn "@foreach" resources/views | grep -v "@php" | head -5 && echo "⚠️  Check for queries inside foreach"
```

### 4.2 缓存策略检查

```bash
# 热点数据应使用缓存
grep -rn "::where(" app/Services/ | grep -v "Cache::" | head -10 && echo "⚠️  Check if caching needed"

# 缓存应有 TTL
grep -rn "Cache::put" app/ | grep -v ", " | grep -v "seconds" && echo "⚠️  Cache::put without TTL"
```

### 4.3 索引检查

```bash
# 检查 migration 中的索引
grep -rn "->index()" database/migrations/ | wc -l
grep -rn "->where(" app/ | grep -v "->whereNull" | wc -l
# 如果 where 查询远多于索引，可能缺索引
```

### 4.4 内存使用检查

```bash
# 大数据集应使用 chunk/cursor
grep -rn "->get()" app/ | grep -v "chunk" | grep -v "limit" && echo "⚠️  Check memory: use chunk/cursor for large datasets"

# 禁止 SELECT *
grep -rn "select \\*\\|->get()" app/ | grep -v "select(" && echo "⚠️  Avoid SELECT *"
```

---

## 审查报告模板

```markdown
# Code Review Report

## 自动化检查
- [ ] Pint: PASS/FAIL
- [ ] PHPStan: PASS/FAIL (X errors)
- [ ] Pest: PASS/FAIL (X tests)
- [ ] Filament Convention: PASS/FAIL

## 架构审查
- [ ] DDD 分层: PASS/FAIL
- [ ] 依赖方向: PASS/FAIL
- [ ] 职责边界: PASS/FAIL

## 安全审计
- [ ] 输入验证: PASS/FAIL
- [ ] 权限控制: PASS/FAIL
- [ ] SQL 注入: PASS/FAIL
- [ ] 敏感数据: PASS/FAIL

## 性能分析
- [ ] N+1 查询: PASS/WARN
- [ ] 缓存策略: PASS/WARN
- [ ] 索引覆盖: PASS/WARN
- [ ] 内存使用: PASS/WARN

## 问题清单
| # | 严重度 | 类型 | 文件 | 行号 | 描述 | 修复建议 |
|---|--------|------|------|------|------|---------|
| 1 | HIGH | security | AuthController.php:45 | SQL injection | Use parameter binding |
| 2 | MED | performance | OrderService.php:78 | N+1 query | Add with('items') |
| 3 | LOW | style | Product.php:12 | Missing return type | Add ?: string |

## 结论
- [ ] APPROVED — 可以合并
- [ ] APPROVED WITH COMMENTS — 可以合并，但建议修复
- [ ] REJECTED — 必须修复后重新审查
```

---

## 快速审查命令

```bash
# 一键自动化检查
./vendor/bin/pint --test && \
./vendor/bin/phpstan analyse --no-progress && \
./vendor/bin/pest --compact

# Docker 环境
docker compose exec app bash -c "\
    ./vendor/bin/pint --test && \
    ./vendor/bin/phpstan analyse --no-progress && \
    ./vendor/bin/pest --compact"

# 架构违规检查
grep -rn "Filament\\" app/Domains/ 2>/dev/null && echo "FAIL" || echo "PASS"
grep -rn "Illuminate\\Http\\Request" app/Services/ 2>/dev/null && echo "FAIL" || echo "PASS"
grep -rn "\\$request->all()" app/ 2>/dev/null && echo "FAIL" || echo "PASS"
```
