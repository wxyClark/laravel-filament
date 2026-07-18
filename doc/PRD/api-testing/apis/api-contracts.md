# API 契约

> **文档编号**: PRD-ATS-A001
> **版本**: v1.0
> **创建日期**: 2026-07-18

---

## 1. API 列表

### 1.1 环境管理

| 方法 | 路径 | 描述 | 认证 |
|------|------|------|------|
| GET | /api/api-testing/environments | 获取环境列表 | 是 |
| POST | /api/api-testing/environments | 创建环境 | 是 |
| GET | /api/api-testing/environments/{id} | 获取环境详情 | 是 |
| PUT | /api/api-testing/environments/{id} | 更新环境 | 是 |
| DELETE | /api/api-testing/environments/{id} | 删除环境 | 是 |
| POST | /api/api-testing/environments/{id}/copy | 复制环境 | 是 |
| PUT | /api/api-testing/environments/{id}/default | 设为默认 | 是 |

### 1.2 模块管理

| 方法 | 路径 | 描述 | 认证 |
|------|------|------|------|
| GET | /api/api-testing/modules | 获取模块列表 | 是 |
| POST | /api/api-testing/modules | 创建模块 | 是 |
| GET | /api/api-testing/modules/{id} | 获取模块详情 | 是 |
| PUT | /api/api-testing/modules/{id} | 更新模块 | 是 |
| DELETE | /api/api-testing/modules/{id} | 删除模块 | 是 |
| PUT | /api/api-testing/modules/sort | 批量排序 | 是 |

### 1.3 功能管理

| 方法 | 路径 | 描述 | 认证 |
|------|------|------|------|
| GET | /api/api-testing/modules/{moduleId}/functions | 获取功能列表 | 是 |
| POST | /api/api-testing/modules/{moduleId}/functions | 创建功能 | 是 |
| PUT | /api/api-testing/functions/{id} | 更新功能 | 是 |
| DELETE | /api/api-testing/functions/{id} | 删除功能 | 是 |

### 1.4 接口管理

| 方法 | 路径 | 描述 | 认证 |
|------|------|------|------|
| GET | /api/api-testing/interfaces | 获取接口列表 (支持搜索) | 是 |
| POST | /api/api-testing/interfaces | 创建接口 | 是 |
| GET | /api/api-testing/interfaces/{id} | 获取接口详情 | 是 |
| PUT | /api/api-testing/interfaces/{id} | 更新接口 | 是 |
| DELETE | /api/api-testing/interfaces/{id} | 删除接口 | 是 |
| GET | /api/api-testing/interfaces/tree | 获取接口树 | 是 |

### 1.5 测试用例管理

| 方法 | 路径 | 描述 | 认证 |
|------|------|------|------|
| GET | /api/api-testing/interfaces/{interfaceId}/test-cases | 获取用例列表 | 是 |
| POST | /api/api-testing/interfaces/{interfaceId}/test-cases | 创建用例 | 是 |
| GET | /api/api-testing/test-cases/{id} | 获取用例详情 | 是 |
| PUT | /api/api-testing/test-cases/{id} | 更新用例 | 是 |
| DELETE | /api/api-testing/test-cases/{id} | 删除用例 | 是 |
| POST | /api/api-testing/test-cases/{id}/copy | 复制用例 | 是 |
| PUT | /api/api-testing/interfaces/{interfaceId}/test-cases/sort | 批量排序 | 是 |

### 1.6 测试执行

| 方法 | 路径 | 描述 | 认证 |
|------|------|------|------|
| POST | /api/api-testing/test-cases/{id}/execute | 执行单个用例 | 是 |
| POST | /api/api-testing/interfaces/{id}/execute-all | 执行接口所有用例 | 是 |
| POST | /api/api-testing/batch-execute | 批量执行 | 是 |
| GET | /api/api-testing/test-results | 获取结果列表 | 是 |
| GET | /api/api-testing/test-results/{id} | 获取结果详情 | 是 |
| GET | /api/api-testing/interfaces/{id}/results | 获取接口测试历史 | 是 |

### 1.7 场景测试 (Phase 2)

| 方法 | 路径 | 描述 | 认证 |
|------|------|------|------|
| GET | /api/api-testing/scenarios | 获取场景列表 | 是 |
| POST | /api/api-testing/scenarios | 创建场景 | 是 |
| GET | /api/api-testing/scenarios/{id} | 获取场景详情 | 是 |
| PUT | /api/api-testing/scenarios/{id} | 更新场景 | 是 |
| DELETE | /api/api-testing/scenarios/{id} | 删除场景 | 是 |
| POST | /api/api-testing/scenarios/{id}/execute | 执行场景 | 是 |

### 1.8 测试计划 (Phase 2)

| 方法 | 路径 | 描述 | 认证 |
|------|------|------|------|
| GET | /api/api-testing/plans | 获取计划列表 | 是 |
| POST | /api/api-testing/plans | 创建计划 | 是 |
| GET | /api/api-testing/plans/{id} | 获取计划详情 | 是 |
| PUT | /api/api-testing/plans/{id} | 更新计划 | 是 |
| DELETE | /api/api-testing/plans/{id} | 删除计划 | 是 |
| POST | /api/api-testing/plans/{id}/execute | 执行计划 | 是 |

### 1.9 仪表盘

| 方法 | 路径 | 描述 | 认证 |
|------|------|------|------|
| GET | /api/api-testing/dashboard/overview | 获取概览数据 | 是 |
| GET | /api/api-testing/dashboard/trends | 获取趋势数据 | 是 |
| GET | /api/api-testing/dashboard/failures | 获取失败接口列表 | 是 |

---

## 2. 通用响应格式

### 成功响应

```json
{
  "code": 200,
  "message": "success",
  "data": { ... }
}
```

### 列表响应

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "items": [ ... ],
    "total": 100,
    "per_page": 20,
    "current_page": 1,
    "last_page": 5
  }
}
```

### 错误响应

```json
{
  "code": 422,
  "message": "Validation failed",
  "errors": {
    "field": ["The field is required."]
  }
}
```

---

## 3. 详细接口定义

### 3.1 创建环境

**POST** `/api/api-testing/environments`

**请求体**:
```json
{
  "name": "开发环境",
  "base_url": "http://localhost:8082",
  "auth_type": "jwt",
  "auth_config": {
    "token_url": "/admin/api/login",
    "username_field": "email",
    "password_field": "password",
    "username": "admin@example.com",
    "password": "password",
    "token_path": "token",
    "header_name": "Authorization",
    "header_prefix": "Bearer"
  },
  "headers": {
    "Accept": "application/json",
    "Content-Type": "application/json"
  },
  "is_default": true
}
```

**成功响应 (201)**:
```json
{
  "code": 201,
  "message": "Environment created",
  "data": {
    "id": 1,
    "name": "开发环境",
    "base_url": "http://localhost:8082",
    "auth_type": "jwt",
    "is_default": true,
    "created_at": "2026-07-18T10:00:00Z"
  }
}
```

---

### 3.2 获取接口树

**GET** `/api/api-testing/interfaces/tree`

**响应 (200)**:
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "type": "module",
      "name": "用户管理",
      "children": [
        {
          "id": 1,
          "type": "function",
          "name": "用户认证",
          "children": [
            {
              "id": 1,
              "type": "interface",
              "name": "管理员登录",
              "method": "POST",
              "path": "/admin/api/login",
              "test_cases_count": 3,
              "last_result": "pass"
            },
            {
              "id": 2,
              "type": "interface",
              "name": "管理员登出",
              "method": "POST",
              "path": "/admin/api/logout",
              "test_cases_count": 2,
              "last_result": "fail"
            }
          ]
        }
      ]
    }
  ]
}
```

---

### 3.3 执行测试用例

**POST** `/api/api-testing/test-cases/{id}/execute`

**请求体**:
```json
{
  "environment_id": 1,
  "overrides": {
    "body": {
      "email": "admin@example.com",
      "password": "wrong-password"
    }
  }
}
```

**响应 (200)**:
```json
{
  "code": 200,
  "message": "Test executed",
  "data": {
    "id": 1,
    "status": "pass",
    "request": {
      "url": "http://localhost:8082/admin/api/login",
      "method": "POST",
      "headers": {
        "Accept": "application/json",
        "Content-Type": "application/json"
      },
      "body": {
        "email": "admin@example.com",
        "password": "wrong-password"
      }
    },
    "response": {
      "status": 401,
      "headers": {
        "Content-Type": "application/json"
      },
      "body": {
        "message": "Invalid credentials"
      }
    },
    "assertions": [
      {
        "type": "status",
        "expected": 401,
        "actual": 401,
        "passed": true
      },
      {
        "type": "body",
        "path": "message",
        "expected": "Invalid credentials",
        "actual": "Invalid credentials",
        "passed": true
      }
    ],
    "response_time": 45,
    "executed_at": "2026-07-18T10:00:00Z"
  }
}
```

---

### 3.4 执行场景测试 (Phase 2)

**POST** `/api/api-testing/scenarios/{id}/execute`

**响应 (200)**:
```json
{
  "code": 200,
  "message": "Scenario executed",
  "data": {
    "id": 1,
    "status": "passed",
    "steps": [
      {
        "step_order": 1,
        "interface_name": "管理员登录",
        "status": "pass",
        "request": {
          "url": "http://localhost:8082/admin/api/login",
          "method": "POST",
          "body": {
            "email": "admin@example.com",
            "password": "password"
          }
        },
        "response": {
          "status": 200,
          "body": {
            "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
          }
        },
        "extracted_params": {
          "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
        },
        "assertions": [
          {
            "type": "status",
            "expected": 200,
            "actual": 200,
            "passed": true
          }
        ],
        "response_time": 120
      },
      {
        "step_order": 2,
        "interface_name": "获取管理员信息",
        "status": "pass",
        "request": {
          "url": "http://localhost:8082/admin/api/me",
          "method": "GET",
          "headers": {
            "Authorization": "Bearer {{token}}"
          }
        },
        "response": {
          "status": 200,
          "body": {
            "id": 1,
            "name": "Admin",
            "email": "admin@example.com"
          }
        },
        "extracted_params": {},
        "assertions": [
          {
            "type": "status",
            "expected": 200,
            "actual": 200,
            "passed": true
          },
          {
            "type": "body",
            "path": "data.email",
            "expected": "admin@example.com",
            "actual": "admin@example.com",
            "passed": true
          }
        ],
        "response_time": 35
      }
    ],
    "total_steps": 2,
    "passed_steps": 2,
    "failed_steps": 0,
    "duration": 155,
    "executed_at": "2026-07-18T10:00:00Z"
  }
}
```

---

### 3.5 获取仪表盘概览

**GET** `/api/api-testing/dashboard/overview`

**响应 (200)**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total_modules": 5,
    "total_interfaces": 25,
    "total_test_cases": 80,
    "total_executions": 150,
    "today_executions": 12,
    "today_pass_rate": 95.5,
    "recent_executions": [
      {
        "id": 150,
        "status": "passed",
        "total_steps": 25,
        "passed_steps": 24,
        "failed_steps": 1,
        "duration": 1250,
        "executed_at": "2026-07-18T09:30:00Z"
      }
    ],
    "top_failures": [
      {
        "interface_id": 5,
        "interface_name": "管理员登出",
        "failure_count": 3,
        "last_failure": "2026-07-18T09:30:00Z"
      }
    ]
  }
}
```

---

### 3.6 获取趋势数据

**GET** `/api/api-testing/dashboard/trends`

**查询参数**:
- `days`: 天数 (默认 7)

**响应 (200)**:
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "date": "2026-07-12",
      "total": 20,
      "passed": 18,
      "failed": 2,
      "pass_rate": 90.0
    },
    {
      "date": "2026-07-13",
      "total": 25,
      "passed": 24,
      "failed": 1,
      "pass_rate": 96.0
    }
  ]
}
```

---

## 4. 断言操作符

| 操作符 | 说明 | 示例 |
|--------|------|------|
| `equals` | 等于 | `{"path": "status", "operator": "equals", "expected": "active"}` |
| `not_equals` | 不等于 | `{"path": "status", "operator": "not_equals", "expected": "deleted"}` |
| `exists` | 字段存在 | `{"path": "data.id", "operator": "exists"}` |
| `not_exists` | 字段不存在 | `{"path": "data.deleted_at", "operator": "not_exists"}` |
| `contains` | 包含 | `{"path": "message", "operator": "contains", "expected": "success"}` |
| `gt` | 大于 | `{"path": "data.count", "operator": "gt", "expected": 0}` |
| `gte` | 大于等于 | `{"path": "data.count", "operator": "gte", "expected": 1}` |
| `lt` | 小于 | `{"path": "data.count", "operator": "lt", "expected": 100}` |
| `lte` | 小于等于 | `{"path": "data.count", "operator": "lte", "expected": 100}` |
| `in` | 在列表中 | `{"path": "data.status", "operator": "in", "expected": ["active", "pending"]}` |
| `not_in` | 不在列表中 | `{"path": "data.status", "operator": "not_in", "expected": ["deleted"]}` |
| `regex` | 正则匹配 | `{"path": "data.email", "operator": "regex", "expected": "^.+@.+$"}` |
| `type` | 类型检查 | `{"path": "data.id", "operator": "type", "expected": "integer"}` |
| `length` | 长度等于 | `{"path": "data.items", "operator": "length", "expected": 3}` |
| `min_length` | 最小长度 | `{"path": "data.name", "operator": "min_length", "expected": 1}` |
| `max_length` | 最大长度 | `{"path": "data.name", "operator": "max_length", "expected": 255}` |

---

## 5. JSON Path 提取器

### 5.1 提取器配置

```json
{
  "name": "token",
  "source": "body",
  "path": "data.token",
  "description": "提取登录 Token"
}
```

### 5.2 支持的提取来源

| 来源 | 说明 |
|------|------|
| `body` | 响应 Body |
| `header` | 响应 Header |
| `status` | 响应状态码 |

### 5.3 JSON Path 示例

| Path | 说明 |
|------|------|
| `data` | 根节点下的 data 字段 |
| `data.token` | data 对象下的 token 字段 |
| `data.user.id` | 嵌套对象 |
| `data.items[0].id` | 数组第一个元素 |
| `data.items[-1].name` | 数组最后一个元素 |
| `data.items[*].id` | 数组所有元素的 id |
