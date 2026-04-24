# 🔌 O2O 预约核销模块 - API 接口契约

> **L4: 需求碎片层级** | **RAG 友好格式** | **可直接组装到提示词**

---

## 📋 元数据

```yaml
module: "o2o"
document_type: "api_contracts"
version: "1.0"
api_count: 8
auth_required: 5
public_api: 3
```

---

## 🏪 服务接口

### GET /api/v1/services - 获取服务列表

```yaml
endpoint: "GET /api/v1/services"
description: "获取门店服务列表"
auth: false

request:
  query_params:
    - name: store_id
      type: integer
      required: true
      description: "门店ID"
    - name: status
      type: string
      required: false
      default: "active"
      description: "服务状态筛选"

response:
  success:
    code: 200
    schema:
      data:
        - id: integer
          name: string
          description: string
          duration: integer
          price: decimal
          max_capacity: integer
          images: array
          status: string

prompt_fragment: |
  # API: 获取服务列表
  @FilamentUIDesigner
  
  创建获取门店服务列表接口，支持按门店和状态筛选。
```

---

### GET /api/v1/services/{id}/timeslots - 获取可用时间片

```yaml
endpoint: "GET /api/v1/services/{id}/timeslots"
description: "获取服务在指定日期的可用时间片"
auth: false

request:
  path_params:
    - name: id
      type: integer
      required: true
      description: "服务ID"
  query_params:
    - name: date
      type: date
      required: true
      description: "预约日期 YYYY-MM-DD"

response:
  success:
    code: 200
    schema:
      data:
        - id: integer
          start_time: string
          end_time: string
          max_capacity: integer
          available_count: integer
          status: string
      service:
        id: integer
        name: string
        price: decimal
        duration: integer

prompt_fragment: |
  # API: 获取时间片
  @TradeEngineer
  
  创建获取可用时间片接口，返回剩余名额。
```

---

## 📅 预约接口

### POST /api/v1/appointments - 创建预约

```yaml
endpoint: "POST /api/v1/appointments"
description: "创建预约"
auth: true

request:
  body:
    - name: service_id
      type: integer
      required: true
      description: "服务ID"
    - name: timeslot_id
      type: integer
      required: true
      description: "时间片ID"
    - name: quantity
      type: integer
      required: false
      default: 1
      description: "预约数量"
    - name: remark
      type: string
      required: false
      description: "备注"

response:
  success:
    code: 201
    schema:
      data:
        appointment_no: string
        service_name: string
        date: date
        start_time: string
        end_time: string
        quantity: integer
        total_amount: decimal
        status: string
        qr_code: string
  errors:
    - code: 422
      description: "时间片已满或参数错误"

prompt_fragment: |
  # API: 创建预约
  @TradeEngineer
  
  创建预约接口，使用 lockForUpdate() 防止并发超卖。
```

---

### GET /api/v1/appointments - 获取预约列表

```yaml
endpoint: "GET /api/v1/appointments"
description: "获取当前用户的预约列表"
auth: true

request:
  query_params:
    - name: page
      type: integer
      required: false
      default: 1
    - name: per_page
      type: integer
      required: false
      default: 10
    - name: status
      type: string
      required: false
      description: "状态筛选"

response:
  success:
    code: 200
    schema:
      data:
        - appointment_no: string
          service_name: string
          store_name: string
          date: date
          start_time: string
          end_time: string
          status: string
          status_text: string
          total_amount: decimal
          qr_code: string
          created_at: datetime
      meta:
        current_page: integer
        total: integer

prompt_fragment: |
  # API: 预约列表
  @FilamentUIDesigner
  
  获取用户预约列表，支持状态筛选。
```

---

### GET /api/v1/appointments/{id} - 获取预约详情

```yaml
endpoint: "GET /api/v1/appointments/{id}"
description: "获取预约详情"
auth: true

response:
  success:
    code: 200
    schema:
      data:
        appointment_no: string
        service:
          id: integer
          name: string
          duration: integer
        store:
          id: integer
          name: string
          address: string
        date: date
        start_time: string
        end_time: string
        quantity: integer
        total_amount: decimal
        status: string
        qr_code: string
        remark: string
        created_at: datetime

prompt_fragment: |
  # API: 预约详情
  @TradeEngineer
  
  获取预约详情，包含服务和门店信息。
```

---

### POST /api/v1/appointments/{id}/cancel - 取消预约

```yaml
endpoint: "POST /api/v1/appointments/{id}/cancel"
description: "取消预约"
auth: true

request:
  body:
    - name: reason
      type: string
      required: false
      description: "取消原因"

response:
  success:
    code: 200
    schema:
      message: "取消成功"
  errors:
    - code: 422
      description: "预约状态不允许取消或已过期"

prompt_fragment: |
  # API: 取消预约
  @TradeEngineer
  
  取消预约，减少时间片 booked_count。
```

---

## ✅ 核销接口

### POST /api/v1/appointments/{id}/verify - 核销预约

```yaml
endpoint: "POST /api/v1/appointments/{id}/verify"
description: "核销预约（门店员工）"
auth: true
permission: "o2o:appointment:verify"

request:
  body:
    - name: remark
      type: string
      required: false
      description: "核销备注"

response:
  success:
    code: 200
    schema:
      message: "核销成功"
      data:
        appointment_no: string
        service_name: string
        verified_at: datetime
        verified_by_name: string
  errors:
    - code: 422
      description: "预约状态不允许核销"
    - code: 403
      description: "无核销权限"

prompt_fragment: |
  # API: 核销预约
  @TradeEngineer @SecurityExpert
  
  核销预约接口，需要门店员工权限。
```

---

### GET /api/v1/appointments/qrcode/{appointment_no} - 获取核销二维码

```yaml
endpoint: "GET /api/v1/appointments/qrcode/{appointment_no}"
description: "获取核销二维码"
auth: true

response:
  success:
    code: 200
    schema:
      data:
        appointment_no: string
        qr_code_url: string
        qr_code_data: string
        expires_at: datetime

prompt_fragment: |
  # API: 核销二维码
  @TradeEngineer
  
  生成核销二维码，包含签名验证。
```

---

### GET /api/v1/appointments/{id}/verification - 获取核销记录

```yaml
endpoint: "GET /api/v1/appointments/{id}/verification"
description: "获取预约的核销记录"
auth: true

response:
  success:
    code: 200
    schema:
      data:
        appointment_no: string
        verified_at: datetime
        verified_by_name: string
        verification_method: string
        store_name: string

prompt_fragment: |
  # API: 核销记录
  @TradeEngineer
  
  获取预约的核销详情。
```

---

## 📊 接口汇总

| 分类 | 接口数 | 需认证 | 说明 |
|------|--------|--------|------|
| 服务 | 2 | 0 | 列表、时间片 |
| 预约 | 4 | 4 | 创建、列表、详情、取消 |
| 核销 | 3 | 3 | 核销、二维码、记录 |
| **合计** | **8** | **5** | - |

---

## 🔧 API 生成提示词模板

```markdown
# 任务：生成 O2O 预约模块 API 接口

## 角色
@TradeEngineer @FilamentUIDesigner

## 任务
请为以下接口创建 Controller 和 FormRequest：

1. 服务相关
   - GET /api/v1/services
   - GET /api/v1/services/{id}/timeslots

2. 预约相关
   - POST /api/v1/appointments
   - GET /api/v1/appointments
   - GET /api/v1/appointments/{id}
   - POST /api/v1/appointments/{id}/cancel

3. 核销相关
   - POST /api/v1/appointments/{id}/verify
   - GET /api/v1/appointments/qrcode/{appointment_no}
   - GET /api/v1/appointments/{id}/verification

## 特殊要求
- 创建预约接口必须使用 lockForUpdate() 防止并发超卖
- 核销接口需要权限检查
- 二维码接口需要签名验证
```

---

**版本**: v1.0 | **更新日期**: 2026-04-24
