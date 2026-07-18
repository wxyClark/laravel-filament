# 领域模型设计

> **文档编号**: PRD-ATS-M001
> **版本**: v1.0
> **创建日期**: 2026-07-18

---

## 1. 实体关系图

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              API 测试系统 ER 图                              │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌──────────────┐       ┌──────────────┐       ┌──────────────┐            │
│  │   Environment │       │    Module    │       │   Function   │            │
│  │   (环境)      │       │   (模块)     │       │   (功能)     │            │
│  └──────┬───────┘       └──────┬───────┘       └──────┬───────┘            │
│         │                      │                      │                     │
│         │                      │                      │                     │
│         │                      └──────────────────────┼─────────────┐      │
│         │                                             │             │      │
│         │                                             ▼             │      │
│         │                                    ┌──────────────┐       │      │
│         │                                    │  Interface   │       │      │
│         │                                    │   (接口)     │       │      │
│         │                                    └──────┬───────┘       │      │
│         │                                           │               │      │
│         │         ┌─────────────────────────────────┼───────────┐   │      │
│         │         │                                 │           │   │      │
│         │         │                                 ▼           │   │      │
│         │         │                        ┌──────────────┐     │   │      │
│         │         │                        │  TestCase    │     │   │      │
│         │         │                        │  (测试用例)   │     │   │      │
│         │         │                        └──────┬───────┘     │   │      │
│         │         │                               │             │   │      │
│         │         │                               ▼             │   │      │
│         │         │                      ┌──────────────┐       │   │      │
│         │         │                      │  TestResult  │       │   │      │
│         │         │                      │  (测试结果)   │       │   │      │
│         │         │                      └──────────────┘       │   │      │
│         │         │                                             │   │      │
│         │         │  ┌──────────────┐      ┌──────────────┐    │   │      │
│         │         │  │ TestScenario │─────▶│ScenarioStep  │    │   │      │
│         │         │  │  (测试场景)  │      │  (场景步骤)  │    │   │      │
│         │         │  └──────┬───────┘      └──────────────┘    │   │      │
│         │         │         │                                   │   │      │
│         │         │         ▼                                   │   │      │
│         │         │  ┌──────────────┐      ┌──────────────┐    │   │      │
│         │         │  │  TestPlan    │─────▶│ TestExecution│    │   │      │
│         │         │  │  (测试计划)  │      │  (执行记录)  │    │   │      │
│         │         │  └──────────────┘      └──────┬───────┘    │   │      │
│         │         │                               │             │   │      │
│         │         │                               ▼             │   │      │
│         │         │                      ┌──────────────┐       │   │      │
│         │         │                      │ExecStep(步骤)│       │   │      │
│         │         │                      └──────────────┘       │   │      │
│         │         │                                             │   │      │
└─────────┼─────────┼─────────────────────────────────────────────┼───┼──────┘
          │         │                                             │   │
          │         └─────────────────────────────────────────────┘   │
          │                                                           │
          └───────────────────────────────────────────────────────────┘
```

---

## 2. 实体定义

### 2.1 Environment (环境)

**表名**: `api_environments`

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| id | bigint | PK, AUTO_INCREMENT | 主键 |
| name | varchar(100) | NOT NULL, UNIQUE | 环境名称 |
| base_url | varchar(500) | NOT NULL | Base URL |
| auth_type | enum | NOT NULL | 认证类型 (none/jwt/session/apikey) |
| auth_config | json | NULLABLE | 认证配置 (加密存储) |
| headers | json | NULLABLE | 默认 Headers |
| is_default | boolean | NOT NULL, DEFAULT false | 是否默认环境 |
| sort_order | integer | NOT NULL, DEFAULT 0 | 排序 |
| created_at | timestamp | NOT NULL | 创建时间 |
| updated_at | timestamp | NOT NULL | 更新时间 |

**索引**:
- `uk_api_environments_name` (name)
- `idx_api_environments_is_default` (is_default)

---

### 2.2 Module (模块)

**表名**: `api_modules`

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| id | bigint | PK, AUTO_INCREMENT | 主键 |
| name | varchar(100) | NOT NULL | 模块名称 |
| description | text | NULLABLE | 模块描述 |
| icon | varchar(50) | NULLABLE | 图标 |
| sort_order | integer | NOT NULL, DEFAULT 0 | 排序 |
| created_at | timestamp | NOT NULL | 创建时间 |
| updated_at | timestamp | NOT NULL | 更新时间 |

---

### 2.3 Function (功能)

**表名**: `api_functions`

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| id | bigint | PK, AUTO_INCREMENT | 主键 |
| module_id | bigint | NOT NULL, FK → api_modules.id | 所属模块 |
| name | varchar(100) | NOT NULL | 功能名称 |
| description | text | NULLABLE | 功能描述 |
| sort_order | integer | NOT NULL, DEFAULT 0 | 排序 |
| created_at | timestamp | NOT NULL | 创建时间 |
| updated_at | timestamp | NOT NULL | 更新时间 |

**索引**:
- `idx_api_functions_module_id` (module_id)

---

### 2.4 Interface (接口)

**表名**: `api_interfaces`

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| id | bigint | PK, AUTO_INCREMENT | 主键 |
| function_id | bigint | NOT NULL, FK → api_functions.id | 所属功能 |
| name | varchar(200) | NOT NULL | 接口名称 |
| description | text | NULLABLE | 接口描述 |
| method | enum | NOT NULL | HTTP 方法 (GET/POST/PUT/PATCH/DELETE) |
| path | varchar(500) | NOT NULL | 接口路径 |
| headers | json | NULLABLE | 默认 Headers |
| body_type | enum | NOT NULL, DEFAULT 'json' | Body 类型 (json/form/raw/none) |
| body_schema | json | NULLABLE | Body 结构定义 |
| auth_required | boolean | NOT NULL, DEFAULT true | 是否需要认证 |
| tags | json | NULLABLE | 标签 |
| sort_order | integer | NOT NULL, DEFAULT 0 | 排序 |
| created_at | timestamp | NOT NULL | 创建时间 |
| updated_at | timestamp | NOT NULL | 更新时间 |

**索引**:
- `idx_api_interfaces_function_id` (function_id)
- `idx_api_interfaces_method` (method)

---

### 2.5 TestCase (测试用例)

**表名**: `api_test_cases`

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| id | bigint | PK, AUTO_INCREMENT | 主键 |
| interface_id | bigint | NOT NULL, FK → api_interfaces.id | 所属接口 |
| environment_id | bigint | NOT NULL, FK → api_environments.id | 测试环境 |
| name | varchar(200) | NOT NULL | 用例名称 |
| description | text | NULLABLE | 用例描述 |
| headers | json | NULLABLE | 请求 Headers (覆盖默认) |
| query_params | json | NULLABLE | Query 参数 |
| body | json | NULLABLE | 请求 Body |
| expected_status | integer | NOT NULL, DEFAULT 200 | 预期状态码 |
| expected_structure | json | NULLABLE | 预期响应结构 |
| expected_data | json | NULLABLE | 预期数据值 |
| expected_response_time | integer | NULLABLE | 预期最大响应时间 (ms) |
| sort_order | integer | NOT NULL, DEFAULT 0 | 排序 |
| created_at | timestamp | NOT NULL | 创建时间 |
| updated_at | timestamp | NOT NULL | 更新时间 |

**索引**:
- `idx_api_test_cases_interface_id` (interface_id)
- `idx_api_test_cases_environment_id` (environment_id)

---

### 2.6 TestResult (测试结果)

**表名**: `api_test_results`

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| id | bigint | PK, AUTO_INCREMENT | 主键 |
| test_case_id | bigint | NOT NULL, FK → api_test_cases.id | 测试用例 |
| environment_id | bigint | NOT NULL, FK → api_environments.id | 测试环境 |
| status | enum | NOT NULL | 状态 (pass/fail/error/skip) |
| request_url | varchar(1000) | NOT NULL | 请求 URL |
| request_method | varchar(10) | NOT NULL | 请求方法 |
| request_headers | json | NULLABLE | 请求 Headers |
| request_body | json | NULLABLE | 请求 Body |
| response_status | integer | NULLABLE | 响应状态码 |
| response_headers | json | NULLABLE | 响应 Headers |
| response_body | json | NULLABLE | 响应 Body |
| response_time | integer | NULLABLE | 响应时间 (ms) |
| assertion_results | json | NULLABLE | 断言结果详情 |
| error_message | text | NULLABLE | 错误信息 |
| executed_at | timestamp | NOT NULL | 执行时间 |
| created_at | timestamp | NOT NULL | 创建时间 |

**索引**:
- `idx_api_test_results_test_case_id` (test_case_id)
- `idx_api_test_results_status` (status)
- `idx_api_test_results_executed_at` (executed_at)

---

### 2.7 TestScenario (测试场景)

**表名**: `api_test_scenarios`

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| id | bigint | PK, AUTO_INCREMENT | 主键 |
| name | varchar(200) | NOT NULL | 场景名称 |
| description | text | NULLABLE | 场景描述 |
| type | enum | NOT NULL | 类型 (smoke/regression/custom) |
| environment_id | bigint | NOT NULL, FK → api_environments.id | 测试环境 |
| abort_on_failure | boolean | NOT NULL, DEFAULT true | 失败时停止 |
| created_at | timestamp | NOT NULL | 创建时间 |
| updated_at | timestamp | NOT NULL | 更新时间 |

---

### 2.8 TestScenarioStep (场景步骤)

**表名**: `api_test_scenario_steps`

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| id | bigint | PK, AUTO_INCREMENT | 主键 |
| scenario_id | bigint | NOT NULL, FK → api_test_scenarios.id | 所属场景 |
| interface_id | bigint | NOT NULL, FK → api_interfaces.id | 关联接口 |
| name | varchar(200) | NOT NULL | 步骤名称 |
| sort_order | integer | NOT NULL | 执行顺序 |
| headers | json | NULLABLE | 请求 Headers |
| query_params | json | NULLABLE | Query 参数 |
| body | json | NULLABLE | 请求 Body |
| body_template | text | NULLABLE | Body 模板 (支持变量替换) |
| extractors | json | NULLABLE | 提取器配置 |
| assertions | json | NULLABLE | 断言配置 |
| continue_on_failure | boolean | NOT NULL, DEFAULT false | 失败时继续 |
| created_at | timestamp | NOT NULL | 创建时间 |
| updated_at | timestamp | NOT NULL | 更新时间 |

**extractors 结构**:
```json
[
  {
    "name": "token",
    "source": "body",
    "path": "data.token",
    "description": "提取登录 Token"
  }
]
```

**assertions 结构**:
```json
[
  {
    "type": "status",
    "operator": "equals",
    "expected": 200
  },
  {
    "type": "body",
    "path": "data.id",
    "operator": "exists"
  },
  {
    "type": "body",
    "path": "data.name",
    "operator": "equals",
    "expected": "张三"
  }
]
```

---

### 2.9 TestPlan (测试计划)

**表名**: `api_test_plans`

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| id | bigint | PK, AUTO_INCREMENT | 主键 |
| name | varchar(200) | NOT NULL | 计划名称 |
| description | text | NULLABLE | 计划描述 |
| type | enum | NOT NULL | 类型 (smoke/regression/custom/ci) |
| schedule | varchar(100) | NULLABLE | Cron 表达式 |
| notify_email | varchar(200) | NULLABLE | 通知邮箱 |
| notify_webhook | varchar(500) | NULLABLE | Webhook URL |
| is_active | boolean | NOT NULL, DEFAULT true | 是否启用 |
| created_at | timestamp | NOT NULL | 创建时间 |
| updated_at | timestamp | NOT NULL | 更新时间 |

---

### 2.10 TestPlanScenario (计划-场景关联)

**表名**: `api_test_plan_scenarios`

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| id | bigint | PK, AUTO_INCREMENT | 主键 |
| plan_id | bigint | NOT NULL, FK → api_test_plans.id | 测试计划 |
| scenario_id | bigint | NOT NULL, FK → api_test_scenarios.id | 测试场景 |
| sort_order | integer | NOT NULL | 执行顺序 |
| created_at | timestamp | NOT NULL | 创建时间 |

**索引**:
- `uk_api_test_plan_scenarios` (plan_id, scenario_id) UNIQUE

---

### 2.11 TestExecution (执行记录)

**表名**: `api_test_executions`

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| id | bigint | PK, AUTO_INCREMENT | 主键 |
| plan_id | bigint | NULLABLE, FK → api_test_plans.id | 关联计划 |
| scenario_id | bigint | NULLABLE, FK → api_test_scenarios.id | 关联场景 |
| environment_id | bigint | NOT NULL, FK → api_environments.id | 测试环境 |
| status | enum | NOT NULL | 状态 (running/passed/failed/error) |
| total_scenarios | integer | NOT NULL, DEFAULT 0 | 总场景数 |
| passed_scenarios | integer | NOT NULL, DEFAULT 0 | 通过场景数 |
| failed_scenarios | integer | NOT NULL, DEFAULT 0 | 失败场景数 |
| total_steps | integer | NOT NULL, DEFAULT 0 | 总步骤数 |
| passed_steps | integer | NOT NULL, DEFAULT 0 | 通过步骤数 |
| failed_steps | integer | NOT NULL, DEFAULT 0 | 失败步骤数 |
| started_at | timestamp | NOT NULL | 开始时间 |
| completed_at | timestamp | NULLABLE | 完成时间 |
| duration | integer | NULLABLE | 总耗时 (ms) |
| trigger_type | enum | NOT NULL | 触发方式 (manual/scheduled/ci) |
| created_at | timestamp | NOT NULL | 创建时间 |

**索引**:
- `idx_api_test_executions_plan_id` (plan_id)
- `idx_api_test_executions_status` (status)
- `idx_api_test_executions_started_at` (started_at)

---

### 2.12 TestExecutionStep (执行步骤)

**表名**: `api_test_execution_steps`

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| id | bigint | PK, AUTO_INCREMENT | 主键 |
| execution_id | bigint | NOT NULL, FK → api_test_executions.id | 所属执行 |
| scenario_step_id | bigint | NULLABLE, FK → api_test_scenario_steps.id | 关联场景步骤 |
| interface_id | bigint | NOT NULL, FK → api_interfaces.id | 关联接口 |
| step_order | integer | NOT NULL | 步骤顺序 |
| status | enum | NOT NULL | 状态 (pending/running/pass/fail/error/skip) |
| request_url | varchar(1000) | NOT NULL | 请求 URL |
| request_method | varchar(10) | NOT NULL | 请求方法 |
| request_headers | json | NULLABLE | 请求 Headers |
| request_body | json | NULLABLE | 请求 Body |
| response_status | integer | NULLABLE | 响应状态码 |
| response_headers | json | NULLABLE | 响应 Headers |
| response_body | json | NULLABLE | 响应 Body |
| response_time | integer | NULLABLE | 响应时间 (ms) |
| extracted_params | json | NULLABLE | 提取的参数 |
| assertion_results | json | NULLABLE | 断言结果 |
| error_message | text | NULLABLE | 错误信息 |
| started_at | timestamp | NOT NULL | 开始时间 |
| completed_at | timestamp | NULLABLE | 完成时间 |
| created_at | timestamp | NOT NULL | 创建时间 |

**索引**:
- `idx_api_test_execution_steps_execution_id` (execution_id)
- `idx_api_test_execution_steps_status` (status)

---

## 3. DDD 分层设计

### Domain 层

```
app/Domains/ApiTesting/
├── Models/
│   ├── Environment.php
│   ├── Module.php
│   ├── Function.php
│   ├── Interface.php
│   ├── TestCase.php
│   ├── TestResult.php
│   ├── TestScenario.php
│   ├── TestScenarioStep.php
│   ├── TestPlan.php
│   ├── TestPlanScenario.php
│   ├── TestExecution.php
│   └── TestExecutionStep.php
├── Enums/
│   ├── AuthType.php
│   ├── HttpMethod.php
│   ├── BodyType.php
│   ├── TestStatus.php
│   ├── ScenarioType.php
│   ├── TriggerType.php
│   └── AssertionOperator.php
├── Services/
│   ├── EnvironmentService.php
│   ├── InterfaceService.php
│   ├── TestCaseService.php
│   ├── TestExecutorService.php
│   ├── ScenarioExecutorService.php
│   ├── PlanExecutorService.php
│   ├── AssertionService.php
│   └── ExtractorService.php
├── Data/
│   ├── EnvironmentData.php
│   ├── InterfaceData.php
│   ├── TestCaseData.php
│   ├── ScenarioData.php
│   └── ExecutionResultData.php
├── Events/
│   ├── TestExecuted.php
│   ├── ScenarioCompleted.php
│   └── PlanCompleted.php
└── Repositories/
    ├── EnvironmentRepositoryInterface.php
    ├── InterfaceRepositoryInterface.php
    ├── TestCaseRepositoryInterface.php
    ├── TestResultRepositoryInterface.php
    ├── ScenarioRepositoryInterface.php
    ├── ExecutionRepositoryInterface.php
    └── PlanRepositoryInterface.php
```

### Infrastructure 层

```
app/Infrastructure/
├── Filament/Resources/
│   ├── ApiTesting/
│   │   ├── EnvironmentResource.php
│   │   ├── ModuleResource.php
│   │   ├── InterfaceResource.php
│   │   ├── TestCaseResource.php
│   │   ├── TestScenarioResource.php
│   │   ├── TestPlanResource.php
│   │   └── TestExecutionResource.php
│   └── Widgets/
│       ├── TestDashboardWidget.php
│       └── RecentExecutionsWidget.php
├── Repositories/Eloquent/
│   ├── EnvironmentRepository.php
│   ├── InterfaceRepository.php
│   ├── TestCaseRepository.php
│   ├── TestResultRepository.php
│   ├── ScenarioRepository.php
│   ├── ExecutionRepository.php
│   └── PlanRepository.php
├── Http/
│   ├── Clients/
│   │   └── ApiTestClient.php
│   └── Extractors/
│       └── JsonPathExtractor.php
└── Queue/
    ├── Jobs/
    │   ├── ExecuteTestJob.php
    │   ├── ExecuteScenarioJob.php
    │   └── ExecutePlanJob.php
    └── Listener/
        └── SendNotificationListener.php
```
