# 🔌 RBAC 权限管理模块 - API 接口契约

> **L4: 需求碎片层级** | **RAG 友好格式** | **可直接组装到提示词**

---

## 📋 元数据

```yaml
module: "rbac"
document_type: "api_contracts"
version: "1.0"
api_count: 12
auth_required: 12
```

---

## 🏢 部门管理接口

### GET /api/v1/departments - 获取部门树

```yaml
endpoint: "GET /api/v1/departments"
description: "获取部门树形结构"
auth: true
permission: "system:department"

response:
  success:
    code: 200
    schema:
      data:
        - id: integer
          name: string
          code: string
          parent_id: integer|null
          sort: integer
          status: string
          children: array
          user_count: integer

prompt_fragment: |
  # API: 部门树
  @SecurityExpert
  
  获取部门树形结构，支持无限层级。
```

---

### POST /api/v1/departments - 创建部门

```yaml
endpoint: "POST /api/v1/departments"
description: "创建部门"
auth: true
permission: "system:department:create"

request:
  body:
    - name: parent_id
      type: integer
      required: false
      description: "上级部门ID"
    - name: name
      type: string
      required: true
      description: "部门名称"
    - name: code
      type: string
      required: true
      description: "部门编码"
    - name: sort
      type: integer
      required: false
      default: 0
      description: "排序"

response:
  success:
    code: 201
    schema:
      data:
        id: integer
        name: string
        code: string

prompt_fragment: |
  # API: 创建部门
  @SecurityExpert
  
  创建部门，支持树形结构。
```

---

### PUT /api/v1/departments/{id} - 更新部门

```yaml
endpoint: "PUT /api/v1/departments/{id}"
description: "更新部门"
auth: true
permission: "system:department:edit"

request:
  body:
    - name: name
      type: string
      required: false
    - name: sort
      type: integer
      required: false
    - name: status
      type: string
      required: false

response:
  success:
    code: 200
    schema:
      message: "更新成功"

prompt_fragment: |
  # API: 更新部门
  @SecurityExpert
```

---

### DELETE /api/v1/departments/{id} - 删除部门

```yaml
endpoint: "DELETE /api/v1/departments/{id}"
description: "删除部门"
auth: true
permission: "system:department:delete"

response:
  success:
    code: 200
    schema:
      message: "删除成功"
  errors:
    - code: 422
      description: "部门下有用户，无法删除"

prompt_fragment: |
  # API: 删除部门
  @SecurityExpert
```

---

## 👥 角色管理接口

### GET /api/v1/roles - 获取角色列表

```yaml
endpoint: "GET /api/v1/roles"
description: "获取角色列表"
auth: true
permission: "system:role:view"

response:
  success:
    code: 200
    schema:
      data:
        - id: integer
          name: string
          code: string
          description: string
          is_system: boolean
          permission_count: integer
          user_count: integer
          status: string

prompt_fragment: |
  # API: 角色列表
  @SecurityExpert
```

---

### POST /api/v1/roles - 创建角色

```yaml
endpoint: "POST /api/v1/roles"
description: "创建角色"
auth: true
permission: "system:role:create"

request:
  body:
    - name: name
      type: string
      required: true
    - name: code
      type: string
      required: true
    - name: description
      type: string
      required: false
    - name: permission_ids
      type: array
      required: false
      items: integer
      description: "权限ID列表"

response:
  success:
    code: 201
    schema:
      data:
        id: integer
        name: string
        code: string

prompt_fragment: |
  # API: 创建角色
  @SecurityExpert
```

---

### PUT /api/v1/roles/{id}/permissions - 分配权限

```yaml
endpoint: "PUT /api/v1/roles/{id}/permissions"
description: "为角色分配权限"
auth: true
permission: "system:role:edit"

request:
  body:
    - name: permission_ids
      type: array
      required: true
      items: integer
      description: "权限ID列表"

response:
  success:
    code: 200
    schema:
      message: "权限分配成功"

prompt_fragment: |
  # API: 分配权限
  @SecurityExpert
```

---

## 👤 用户管理接口

### GET /api/v1/users - 获取用户列表

```yaml
endpoint: "GET /api/v1/users"
description: "获取用户列表"
auth: true
permission: "system:user:view"

request:
  query_params:
    - name: page
      type: integer
      required: false
      default: 1
    - name: keyword
      type: string
      required: false
      description: "搜索关键词"
    - name: department_id
      type: integer
      required: false
      description: "部门筛选"
    - name: role_code
      type: string
      required: false
      description: "角色筛选"

response:
  success:
    code: 200
    schema:
      data:
        - id: integer
          name: string
          email: string
          phone: string
          department_name: string
          roles: array
          status: string
          last_login_at: datetime

prompt_fragment: |
  # API: 用户列表
  @SecurityExpert
```

---

### POST /api/v1/users - 创建用户

```yaml
endpoint: "POST /api/v1/users"
description: "创建用户"
auth: true
permission: "system:user:create"

request:
  body:
    - name: name
      type: string
      required: true
    - name: email
      type: string
      required: true
    - name: password
      type: string
      required: true
    - name: department_id
      type: integer
      required: false
    - name: phone
      type: string
      required: false
    - name: role_ids
      type: array
      required: false
      items: integer

response:
  success:
    code: 201
    schema:
      data:
        id: integer
        name: string
        email: string

prompt_fragment: |
  # API: 创建用户
  @SecurityExpert
```

---

### PUT /api/v1/users/{id}/roles - 分配角色

```yaml
endpoint: "PUT /api/v1/users/{id}/roles"
description: "为用户分配角色"
auth: true
permission: "system:user:edit"

request:
  body:
    - name: role_ids
      type: array
      required: true
      items: integer

response:
  success:
    code: 200
    schema:
      message: "角色分配成功"

prompt_fragment: |
  # API: 分配角色
  @SecurityExpert
```

---

## 📊 接口汇总

| 分类 | 接口数 | 权限前缀 |
|------|--------|---------|
| 部门管理 | 4 | system:department |
| 角色管理 | 4 | system:role |
| 用户管理 | 4 | system:user |
| **合计** | **12** | - |

---

**版本**: v1.0 | **更新日期**: 2026-04-24
