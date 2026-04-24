# 🔌 CRM 客户关系管理模块 - API 接口契约

> **L4: 需求碎片层级** | **RAG 友好格式** | **可直接组装到提示词**

---

## 📋 元数据

```yaml
module: "crm"
document_type: "api_contracts"
version: "1.0"
api_count: 11
auth_required: 11
```

---

## 👥 客户管理接口

### GET /api/v1/customers - 获取客户列表

```yaml
endpoint: "GET /api/v1/customers"
description: "获取客户列表"
auth: true

request:
  query_params:
    - name: page
      type: integer
      required: false
      default: 1
    - name: keyword
      type: string
      required: false
      description: "搜索关键词（名称/电话/公司）"
    - name: level
      type: string
      required: false
      description: "等级筛选"
    - name: status
      type: string
      required: false
      description: "状态筛选"
    - name: owner_id
      type: integer
      required: false
      description: "负责人筛选"

response:
  success:
    code: 200
    schema:
      data:
        - id: integer
          name: string
          type: string
          phone: string
          company: string
          level: string
          owner_name: string
          total_orders: integer
          total_amount: decimal
          last_contact_at: datetime
          status: string

prompt_fragment: |
  # API: 客户列表
  @FilamentUIDesigner
  
  获取客户列表，支持多维度筛选。
```

---

### POST /api/v1/customers - 创建客户

```yaml
endpoint: "POST /api/v1/customers"
description: "创建客户"
auth: true

request:
  body:
    - name: name
      type: string
      required: true
    - name: type
      type: string
      required: false
      default: "individual"
    - name: phone
      type: string
      required: false
    - name: email
      type: string
      required: false
    - name: company
      type: string
      required: false
    - name: level
      type: string
      required: false
      default: "C"
    - name: source
      type: string
      required: false
    - name: address
      type: object
      required: false

response:
  success:
    code: 201
    schema:
      data:
        id: integer
        name: string

prompt_fragment: |
  # API: 创建客户
  @SystemArchitect
```

---

### GET /api/v1/customers/{id} - 获取客户详情

```yaml
endpoint: "GET /api/v1/customers/{id}"
description: "获取客户详情"
auth: true

response:
  success:
    code: 200
    schema:
      data:
        id: integer
        name: string
        type: string
        phone: string
        email: string
        company: string
        address: object
        source: string
        level: string
        owner: object
        tags: array
        status: string
        total_orders: integer
        total_amount: decimal
        last_contact_at: datetime
        contacts: array
        created_at: datetime

prompt_fragment: |
  # API: 客户详情
  @SystemArchitect
```

---

### PUT /api/v1/customers/{id} - 更新客户

```yaml
endpoint: "PUT /api/v1/customers/{id}"
description: "更新客户"
auth: true

request:
  body:
    - name: name
      type: string
      required: false
    - name: phone
      type: string
      required: false
    - name: level
      type: string
      required: false
    - name: tags
      type: array
      required: false

response:
  success:
    code: 200
    schema:
      message: "更新成功"

prompt_fragment: |
  # API: 更新客户
  @SystemArchitect
```

---

### DELETE /api/v1/customers/{id} - 删除客户

```yaml
endpoint: "DELETE /api/v1/customers/{id}"
description: "删除客户（软删除）"
auth: true

response:
  success:
    code: 200
    schema:
      message: "删除成功"

prompt_fragment: |
  # API: 删除客户
  @SystemArchitect
```

---

## 📞 联系人接口

### GET /api/v1/customers/{id}/contacts - 获取联系人列表

```yaml
endpoint: "GET /api/v1/customers/{id}/contacts"
description: "获取客户联系人列表"
auth: true

response:
  success:
    code: 200
    schema:
      data:
        - id: integer
          name: string
          position: string
          phone: string
          email: string
          is_primary: boolean

prompt_fragment: |
  # API: 联系人列表
  @SystemArchitect
```

---

### POST /api/v1/customers/{id}/contacts - 添加联系人

```yaml
endpoint: "POST /api/v1/customers/{id}/contacts"
description: "添加客户联系人"
auth: true

request:
  body:
    - name: name
      type: string
      required: true
    - name: position
      type: string
      required: false
    - name: phone
      type: string
      required: false
    - name: email
      type: string
      required: false
    - name: is_primary
      type: boolean
      required: false

response:
  success:
    code: 201
    schema:
      data:
        id: integer
        name: string

prompt_fragment: |
  # API: 添加联系人
  @SystemArchitect
```

---

## 📝 跟进记录接口

### GET /api/v1/customers/{id}/follow-ups - 获取跟进记录

```yaml
endpoint: "GET /api/v1/customers/{id}/follow-ups"
description: "获取客户跟进记录"
auth: true

response:
  success:
    code: 200
    schema:
      data:
        - id: integer
          type: string
          content: string
          user_name: string
          next_time: datetime
          attachments: array
          created_at: datetime

prompt_fragment: |
  # API: 跟进记录
  @SystemArchitect
```

---

### POST /api/v1/customers/{id}/follow-ups - 添加跟进记录

```yaml
endpoint: "POST /api/v1/customers/{id}/follow-ups"
description: "添加客户跟进记录"
auth: true

request:
  body:
    - name: type
      type: string
      required: true
      description: "跟进方式：phone/email/visit/wechat/other"
    - name: content
      type: string
      required: true
    - name: next_time
      type: datetime
      required: false
      description: "下次跟进时间"
    - name: attachments
      type: array
      required: false
      items: string

response:
  success:
    code: 201
    schema:
      data:
        id: integer
        content: string

prompt_fragment: |
  # API: 添加跟进记录
  @SystemArchitect
```

---

## 📊 客户统计接口

### GET /api/v1/customers/statistics - 获取客户统计

```yaml
endpoint: "GET /api/v1/customers/statistics"
description: "获取客户统计信息"
auth: true

response:
  success:
    code: 200
    schema:
      data:
        total_customers: integer
        active_customers: integer
        new_this_month: integer
        by_level:
          A: integer
          B: integer
          C: integer
          D: integer
        by_source:
          register: integer
          recommend: integer
          activity: integer
          other: integer

prompt_fragment: |
  # API: 客户统计
  @SystemArchitect
```

---

## 📊 接口汇总

| 分类 | 接口数 | 说明 |
|------|--------|------|
| 客户管理 | 5 | CRUD + 列表 |
| 联系人 | 2 | 列表、添加 |
| 跟进记录 | 2 | 列表、添加 |
| 统计 | 1 | 客户统计 |
| **合计** | **11** | - |

---

**版本**: v1.0 | **更新日期**: 2026-04-24
