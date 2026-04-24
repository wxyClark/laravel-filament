# 🔌 二级分销模块 - API 接口契约

> **L4: 需求碎片层级** | **RAG 友好格式** | **可直接组装到提示词**

---

## 📋 元数据

```yaml
module: "distribution"
document_type: "api_contracts"
version: "1.0"
api_count: 7
auth_required: 6
public_api: 1
```

---

## 📊 分销中心接口

### GET /api/v1/distribution/profile - 获取分销中心信息

```yaml
endpoint: "GET /api/v1/distribution/profile"
description: "获取当前用户的分销中心信息"
auth: true

response:
  success:
    code: 200
    schema:
      data:
        total_commission: decimal
        available_commission: decimal
        frozen_commission: decimal
        paid_commission: decimal
        level1_count: integer
        level2_count: integer
        total_children: integer
        invitation_code: string
        invitation_url: string

prompt_fragment: |
  # API: 分销中心
  @AssetManager
  
  获取用户分销中心汇总信息。
```

---

### GET /api/v1/distribution/children - 获取下级列表

```yaml
endpoint: "GET /api/v1/distribution/children"
description: "获取我的下级用户列表"
auth: true

request:
  query_params:
    - name: page
      type: integer
      required: false
      default: 1
    - name: level
      type: integer
      required: false
      description: "层级筛选（1或2）"

response:
  success:
    code: 200
    schema:
      data:
        - user_id: integer
          nickname: string
          avatar: string
          level: integer
          order_count: integer
          total_amount: decimal
          created_at: datetime
      meta:
        current_page: integer
        total: integer

prompt_fragment: |
  # API: 下级列表
  @AssetManager
  
  获取用户的下级分销关系列表。
```

---

## 💰 佣金接口

### GET /api/v1/distribution/commissions - 获取佣金记录

```yaml
endpoint: "GET /api/v1/distribution/commissions"
description: "获取佣金记录列表"
auth: true

request:
  query_params:
    - name: page
      type: integer
      required: false
      default: 1
    - name: status
      type: string
      required: false
      description: "状态筛选（frozen/available/paid/cancelled）"

response:
  success:
    code: 200
    schema:
      data:
        - id: integer
          order_sn: string
          level: integer
          order_amount: decimal
          rate: decimal
          amount: decimal
          status: string
          status_text: string
          frozen_until: date
          created_at: datetime
      summary:
        total_frozen: decimal
        total_available: decimal
        total_paid: decimal
        total_cancelled: decimal

prompt_fragment: |
  # API: 佣金记录
  @AssetManager
  
  获取佣金记录列表，支持状态筛选。
```

---

### GET /api/v1/distribution/commissions/summary - 佣金汇总

```yaml
endpoint: "GET /api/v1/distribution/commissions/summary"
description: "获取佣金汇总统计"
auth: true

response:
  success:
    code: 200
    schema:
      data:
        total_commission: decimal
        frozen: decimal
        available: decimal
        paid: decimal
        cancelled: decimal
        level1_total: decimal
        level2_total: decimal
        pending_withdrawal: decimal

prompt_fragment: |
  # API: 佣金汇总
  @AssetManager
  
  获取佣金汇总统计。
```

---

## 💸 提现接口

### POST /api/v1/distribution/withdrawals - 申请提现

```yaml
endpoint: "POST /api/v1/distribution/withdrawals"
description: "申请佣金提现"
auth: true

request:
  body:
    - name: amount
      type: decimal
      required: true
      description: "提现金额"
    - name: commission_ids
      type: array
      required: true
      items: integer
      description: "关联佣金ID列表"
    - name: bank_info
      type: object
      required: true
      description: "收款信息"
      properties:
        bank_name: { type: string, required: true }
        account_no: { type: string, required: true }
        account_name: { type: string, required: true }

response:
  success:
    code: 201
    schema:
      data:
        withdrawal_no: string
        amount: decimal
        status: string
  errors:
    - code: 422
      description: "金额不足或参数错误"

prompt_fragment: |
  # API: 申请提现
  @AssetManager
  
  创建提现申请，校验可用佣金余额。
```

---

### GET /api/v1/distribution/withdrawals - 获取提现记录

```yaml
endpoint: "GET /api/v1/distribution/withdrawals"
description: "获取提现记录列表"
auth: true

request:
  query_params:
    - name: page
      type: integer
      required: false
      default: 1
    - name: status
      type: string
      required: false
      description: "状态筛选"

response:
  success:
    code: 200
    schema:
      data:
        - withdrawal_no: string
          amount: decimal
          status: string
          status_text: string
          bank_name: string
          audit_at: datetime
          paid_at: datetime
          created_at: datetime

prompt_fragment: |
  # API: 提现记录
  @AssetManager
  
  获取提现记录列表。
```

---

### GET /api/v1/distribution/qrcode - 获取推广二维码

```yaml
endpoint: "GET /api/v1/distribution/qrcode"
description: "获取推广二维码"
auth: true

response:
  success:
    code: 200
    schema:
      data:
        invitation_code: string
        invitation_url: string
        qrcode_image_url: string

prompt_fragment: |
  # API: 推广二维码
  @AssetManager
  
  生成用户专属推广二维码。
```

---

### GET /api/v1/distribution/config - 获取分销配置（公开）

```yaml
endpoint: "GET /api/v1/distribution/config"
description: "获取分销配置（公开接口）"
auth: false

response:
  success:
    code: 200
    schema:
      data:
        level1_rate: decimal
        level2_rate: decimal
        frozen_days: integer
        min_withdrawal: decimal

prompt_fragment: |
  # API: 分销配置
  @AssetManager
  
  获取公开的分销配置信息。
```

---

## 📊 接口汇总

| 分类 | 接口数 | 需认证 | 说明 |
|------|--------|--------|------|
| 分销中心 | 2 | 2 | 信息、下级列表 |
| 佣金 | 2 | 2 | 记录、汇总 |
| 提现 | 2 | 2 | 申请、记录 |
| 推广 | 1 | 1 | 二维码 |
| 配置 | 1 | 0 | 公开配置 |
| **合计** | **7** | **6** | - |

---

**版本**: v1.0 | **更新日期**: 2026-04-24
