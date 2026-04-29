# 🔄 电商退货退款场景模板

**版本**: v2.0.0  
**适用场景**: 退货申请、退款处理、售后流程

---

## 1. 场景概述

退货退款系统需要支持：
- 退货申请（仅退款、退货退款）
- 退款审核
- 退货物流
- 退款处理
- 售后保障

---

## 2. 领域模型扩展

### 2.1 售后申请表 (after_sales)

```yaml
entity: AfterSale
table: after_sales
description: "售后申请"

fields:
  - name: id
    type: bigint
    primary: true

  - name: order_id
    type: bigint
    foreign: orders.id

  - name: user_id
    type: bigint
    foreign: users.id

  - name: type
    type: enum
    values: [refund_only, refund_with_return]
    comment: "售后类型"

  - name: reason
    type: varchar(500)
    comment: "售后原因"

  - name: amount
    type: decimal(10,2)
    comment: "退款金额"

  - name: status
    type: enum
    values: [pending, approved, rejected, returned, refunded, closed]
    default: pending
    comment: "售后状态"

  - name: images
    type: json
    nullable: true
    comment: "凭证图片"

  - name: admin_remark
    type: varchar(500)
    nullable: true
    comment: "管理员备注"

  - name: created_at
    type: timestamp

  - name: updated_at
    type: timestamp
```

### 2.2 退款记录表 (refunds)

```yaml
entity: Refund
table: refunds
description: "退款记录"

fields:
  - name: id
    type: bigint
    primary: true

  - name: after_sale_id
    type: bigint
    foreign: after_sales.id

  - name: order_id
    type: bigint
    foreign: orders.id

  - name: refund_no
    type: varchar(64)
    unique: true
    comment: "退款单号"

  - name: amount
    type: decimal(10,2)
    comment: "退款金额"

  - name: method
    type: enum
    values: [original, balance, bank_transfer]
    comment: "退款方式"

  - name: status
    type: enum
    values: [pending, processing, success, failed]
    default: pending
    comment: "退款状态"

  - name: refund_at
    type: timestamp
    nullable: true
    comment: "退款时间"

  - name: transaction_no
    type: varchar(100)
    nullable: true
    comment: "交易流水号"
```

---

## 3. 状态机定义

### 3.1 售后状态机

```yaml
entity: AfterSale
states:
  - name: pending
    label: "待审核"
    initial: true

  - name: approved
    label: "已通过"

  - name: rejected
    label: "已拒绝"

  - name: returned
    label: "已退货"

  - name: refunded
    label: "已退款"

  - name: closed
    label: "已关闭"

transitions:
  - from: pending
    to: approved
    event: AfterSaleApproved
    actions:
      - "记录审核时间"
      - "触发 AfterSaleApproved 事件"

  - from: pending
    to: rejected
    event: AfterSaleRejected
    actions:
      - "记录拒绝原因"

  - from: approved
    to: returned
    event: AfterSaleReturned
    trigger: "仅退款类型自动跳过"
    guard: "type == refund_with_return"

  - from: approved
    to: refunded
    event: AfterSaleRefunded
    actions:
      - "创建退款记录"
      - "触发 RefundCreated 事件"

  - from: returned
    to: refunded
    event: AfterSaleRefunded
    actions:
      - "创建退款记录"
      - "恢复库存"
      - "触发 RefundCreated 事件"
```

### 3.2 退款状态机

```yaml
entity: Refund
states:
  - name: pending
    label: "待退款"
    initial: true

  - name: processing
    label: "退款中"

  - name: success
    label: "退款成功"

  - name: failed
    label: "退款失败"

transitions:
  - from: pending
    to: processing
    event: RefundProcessing
    actions:
      - "调用支付网关退款接口"

  - from: processing
    to: success
    event: RefundSuccess
    actions:
      - "更新退款时间"
      - "更新订单状态为 refunded"
      - "触发 RefundSuccess 事件"

  - from: processing
    to: failed
    event: RefundFailed
    actions:
      - "记录失败原因"
      - "支持重试"
```

---

## 4. API 接口契约

### 4.1 申请售后
```yaml
endpoint: "POST /api/v1/after-sales"
auth: true

request:
  body:
    order_id: int
    type: string
    reason: string
    amount: decimal
    images: array

response:
  data:
    after_sale_id: int
    status: string
```

### 4.2 审核售后
```yaml
endpoint: "POST /api/v1/after-sales/{id}/approve"
auth: true
permission: "after-sale.approve"

request:
  body:
    action: string
    remark: string

response:
  data:
    status: string
```

### 4.3 处理退款
```yaml
endpoint: "POST /api/v1/refunds/{id}/process"
auth: true
permission: "refund.process"

response:
  data:
    refund_id: int
    status: string
```

---

## 5. 提示词模板

```yaml
prompt_fragments:
  roles:
    - "@TradeEngineer"
    - "@AssetManager"
  core:
    - "@type-safety-immutability"
    - "@event-driven"
    - "@error-handling"
  tasks:
    - "@template-service-layer"
    - "@template-dto-conversion"
    - "@template-event-listener"
  domains: []
  testing:
    - "@pest-feature-test"
```

---

**版本**: v2.0.0 | **更新日期**: 2026-04-27
