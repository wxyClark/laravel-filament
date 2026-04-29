# 📦 电商促销场景模板

**版本**: v2.0.0  
**适用场景**: 优惠券、满减、折扣、限时抢购等促销活动

---

## 1. 场景概述

促销系统是电商的核心营销能力，需要支持：
- 优惠券（满减券、折扣券、免运费券）
- 满减活动（满额减、满件减）
- 限时折扣
- 组合促销

---

## 2. 领域模型扩展

### 2.1 促销活动表 (promotions)

```yaml
entity: Promotion
table: promotions
description: "促销活动主表"

fields:
  - name: id
    type: bigint
    primary: true

  - name: name
    type: varchar(255)
    comment: "活动名称"

  - name: type
    type: enum
    values: [coupon, flash_sale, full_reduction, combo]
    comment: "活动类型"

  - name: status
    type: enum
    values: [draft, active, paused, ended]
    default: draft
    comment: "活动状态"

  - name: start_time
    type: timestamp
    comment: "开始时间"

  - name: end_time
    type: timestamp
    comment: "结束时间"

  - name: rules
    type: json
    comment: "活动规则（JSON）"

  - name: budget
    type: decimal(10,2)
    comment: "预算上限"

  - name: used_amount
    type: decimal(10,2)
    default: 0
    comment: "已使用金额"
```

### 2.2 优惠券表 (coupons)

```yaml
entity: Coupon
table: coupons
description: "优惠券实例"

fields:
  - name: id
    type: bigint
    primary: true

  - name: promotion_id
    type: bigint
    foreign: promotions.id
    comment: "关联活动"

  - name: user_id
    type: bigint
    foreign: users.id
    comment: "所属用户"

  - name: code
    type: varchar(64)
    unique: true
    comment: "优惠券码"

  - name: status
    type: enum
    values: [unused, used, expired]
    default: unused
    comment: "使用状态"

  - name: used_at
    type: timestamp
    nullable: true
    comment: "使用时间"

  - name: order_id
    type: bigint
    nullable: true
    comment: "使用的订单ID"
```

### 2.3 订单优惠明细表 (order_discounts)

```yaml
entity: OrderDiscount
table: order_discounts
description: "订单优惠明细"

fields:
  - name: id
    type: bigint
    primary: true

  - name: order_id
    type: bigint
    foreign: orders.id

  - name: promotion_id
    type: bigint
    nullable: true
    comment: "关联活动"

  - name: coupon_id
    type: bigint
    nullable: true
    comment: "关联优惠券"

  - name: discount_type
    type: enum
    values: [promotion, coupon, manual]
    comment: "优惠类型"

  - name: discount_amount
    type: decimal(10,2)
    comment: "优惠金额"

  - name: description
    type: varchar(255)
    comment: "优惠描述"
```

---

## 3. 状态机定义

### 3.1 促销活动状态机

```yaml
entity: Promotion
states:
  - name: draft
    label: "草稿"
    initial: true

  - name: active
    label: "进行中"

  - name: paused
    label: "已暂停"

  - name: ended
    label: "已结束"

transitions:
  - from: draft
    to: active
    event: PromotionActivated
    guard: "活动时间未过期"

  - from: active
    to: paused
    event: PromotionPaused

  - from: paused
    to: active
    event: PromotionResumed

  - from: active
    to: ended
    event: PromotionEnded
    trigger: "定时任务到达结束时间"
```

---

## 4. API 接口契约

### 4.1 获取可用优惠券列表
```yaml
endpoint: "GET /api/v1/coupons/available"
auth: true

request:
  query_params:
    - name: order_amount
      type: decimal
      description: "订单金额（用于筛选可用券）"

response:
  data:
    - id: int
      code: string
      discount_type: string
      discount_value: decimal
      min_amount: decimal
      valid_until: datetime
```

### 4.2 应用优惠券
```yaml
endpoint: "POST /api/v1/orders/{id}/apply-coupon"
auth: true

request:
  body:
    coupon_code: string

response:
  data:
    discount_amount: decimal
    pay_amount: decimal
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
  tasks:
    - "@template-service-layer"
    - "@template-dto-conversion"
  domains:
    - "@constraint-inventory-concurrency"
  testing:
    - "@pest-feature-test"
```

---

**版本**: v2.0.0 | **更新日期**: 2026-04-27
