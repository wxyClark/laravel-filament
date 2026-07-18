# 状态机

> **文档编号**: PRD-ATS-S001
> **版本**: v1.0
> **创建日期**: 2026-07-18

---

## 1. 测试结果状态

**实体**: TestResult

### 状态定义

| 状态 | 值 | 描述 | 颜色 |
|------|-----|------|------|
| 通过 | pass | 所有断言通过 | success |
| 失败 | fail | 断言失败 | danger |
| 错误 | error | 请求异常（网络错误、超时等） | warning |
| 跳过 | skip | 未执行 | gray |

### 状态流转

```
[初始] --执行完成--> [pass] (所有断言通过)
[初始] --执行完成--> [fail] (断言失败)
[初始] --执行异常--> [error] (网络错误、超时等)
[初始] --手动跳过--> [skip]
```

### 状态约束

- 状态只能从初始状态流转到终态
- 终态不可变更
- 每次执行生成新的 TestResult 记录

---

## 2. 场景执行状态

**实体**: TestScenario

### 状态定义

| 状态 | 值 | 描述 | 颜色 |
|------|-----|------|------|
| 待执行 | pending | 未执行 | gray |
| 执行中 | running | 正在执行 | info |
| 通过 | passed | 所有步骤通过 | success |
| 失败 | failed | 有步骤失败 | danger |
| 错误 | error | 执行异常 | warning |

### 状态流转

```
[pending] --开始执行--> [running]
[running] --所有步骤通过--> [passed]
[running] --有步骤失败--> [failed]
[running] --执行异常--> [error]
```

---

## 3. 执行记录状态

**实体**: TestExecution

### 状态定义

| 状态 | 值 | 描述 | 颜色 |
|------|-----|------|------|
| 执行中 | running | 正在执行 | info |
| 通过 | passed | 所有场景通过 | success |
| 失败 | failed | 有场景失败 | danger |
| 错误 | error | 执行异常 | warning |

### 状态流转

```
[初始] --开始执行--> [running]
[running] --所有场景通过--> [passed]
[running] --有场景失败--> [failed]
[running] --执行异常--> [error]
```

---

## 4. 执行步骤状态

**实体**: TestExecutionStep

### 状态定义

| 状态 | 值 | 描述 | 颜色 |
|------|-----|------|------|
| 待执行 | pending | 等待执行 | gray |
| 执行中 | running | 正在执行 | info |
| 通过 | pass | 断言通过 | success |
| 失败 | fail | 断言失败 | danger |
| 错误 | error | 执行异常 | warning |
| 跳过 | skip | 因前置步骤失败而跳过 | gray |

### 状态流转

```
[pending] --开始执行--> [running]
[running] --断言通过--> [pass]
[running] --断言失败--> [fail]
[running] --执行异常--> [error]
[pending] --前置失败--> [skip]
```

---

## 5. 测试计划状态

**实体**: TestPlan

### 状态定义

| 状态 | 值 | 描述 | 颜色 |
|------|-----|------|------|
| 启用 | active | 已启用，可执行 | success |
| 禁用 | inactive | 已禁用，不可执行 | gray |

### 状态流转

```
[active] --禁用--> [inactive]
[inactive] --启用--> [active]
```

---

## 6. 触发方式

**实体**: TestExecution

### 类型定义

| 类型 | 值 | 描述 |
|------|-----|------|
| 手动 | manual | 用户手动触发 |
| 定时 | scheduled | 定时任务触发 |
| CI/CD | ci | CI/CD 流水线触发 |

### 使用场景

- **manual**: 用户在界面点击"执行"按钮
- **scheduled**: Cron 定时任务触发
- **ci**: GitHub Actions 等 CI/CD 工具触发

---

## 7. 场景类型

**实体**: TestScenario

### 类型定义

| 类型 | 值 | 描述 |
|------|-----|------|
| 冒烟 | smoke | 核心接口快速验证 |
| 回归 | regression | 全量回归测试 |
| 自定义 | custom | 自定义场景 |

### 使用场景

- **smoke**: 每次部署后快速验证核心功能
- **regression**: 定期执行全量回归
- **custom**: 特定业务流程测试

---

## 8. 认证类型

**实体**: Environment

### 类型定义

| 类型 | 值 | 描述 |
|------|-----|------|
| 无认证 | none | 不需要认证 |
| JWT | jwt | JWT Token 认证 |
| Session | session | Session Cookie 认证 |
| API Key | apikey | API Key 认证 |

### 认证配置结构

#### JWT

```json
{
  "token_url": "/admin/api/login",
  "username_field": "email",
  "password_field": "password",
  "username": "admin@example.com",
  "password": "password",
  "token_path": "token",
  "header_name": "Authorization",
  "header_prefix": "Bearer",
  "expires_in": 3600
}
```

#### Session

```json
{
  "login_url": "/login",
  "username_field": "email",
  "password_field": "password",
  "username": "user@example.com",
  "password": "password",
  "csrf": true
}
```

#### API Key

```json
{
  "key_name": "X-API-Key",
  "key_value": "your-api-key-here",
  "key_location": "header"
}
```

---

## 9. 状态验证规则

### 9.1 测试结果验证

```php
// 状态只能是预定义值
enum TestStatus: string
{
    case PASS = 'pass';
    case FAIL = 'fail';
    case ERROR = 'error';
    case SKIP = 'skip';
}

// 必须有请求和响应信息
if ($status === TestStatus::PASS || $status === TestStatus::FAIL) {
    assert($requestUrl !== null);
    assert($responseStatus !== null);
}
```

### 9.2 执行记录验证

```php
// 统计数据必须一致
assert($totalSteps === $passedSteps + $failedSteps + $skippedSteps);

// 状态与统计数据一致
if ($status === TestExecutionStatus::PASSED) {
    assert($failedSteps === 0);
    assert($failedScenarios === 0);
}

// 完成时间必须晚于开始时间
if ($completedAt !== null) {
    assert($completedAt >= $startedAt);
}
```

### 9.3 场景步骤验证

```php
// 步骤顺序必须连续
$steps->sortBy('sort_order');
for ($i = 0; $i < $steps->count(); $i++) {
    assert($steps[$i]->sort_order === $i + 1);
}

// 失败停止时，后续步骤必须是 pending 或 skip
if ($scenario->abort_on_failure && $failedStep !== null) {
    $subsequentSteps = $steps->filter(fn ($s) => $s->sort_order > $failedStep->sort_order);
    foreach ($subsequentSteps as $step) {
        assert(in_array($step->status, ['pending', 'skip']));
    }
}
```
