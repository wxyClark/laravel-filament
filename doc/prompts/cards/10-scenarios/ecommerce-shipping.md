# 🚚 电商物流场景模板

**版本**: v2.0.0  
**适用场景**: 运费计算、物流跟踪、发货管理

---

## 1. 场景概述

物流系统需要支持：
- 运费模板（按重量、按件、按区域）
- 物流公司对接
- 运单管理
- 物流轨迹查询

---

## 2. 领域模型扩展

### 2.1 运费模板表 (shipping_templates)

```yaml
entity: ShippingTemplate
table: shipping_templates
description: "运费模板"

fields:
  - name: id
    type: bigint
    primary: true

  - name: name
    type: varchar(255)
    comment: "模板名称"

  - name: calculation_type
    type: enum
    values: [by_weight, by_piece, by_amount]
    comment: "计算方式"

  - name: free_shipping_threshold
    type: decimal(10,2)
    nullable: true
    comment: "包邮门槛"

  - name: rules
    type: json
    comment: "运费规则（JSON）"

  - name: status
    type: enum
    values: [active, inactive]
    default: active
```

### 2.2 物流公司表 (shipping_companies)

```yaml
entity: ShippingCompany
table: shipping_companies
description: "物流公司"

fields:
  - name: id
    type: bigint
    primary: true

  - name: code
    type: varchar(50)
    unique: true
    comment: "物流公司编码"

  - name: name
    type: varchar(255)
    comment: "物流公司名称"

  - name: api_config
    type: json
    nullable: true
    comment: "API配置"

  - name: status
    type: enum
    values: [active, inactive]
    default: active
```

### 2.3 运单表 (shipments)

```yaml
entity: Shipment
table: shipments
description: "运单"

fields:
  - name: id
    type: bigint
    primary: true

  - name: order_id
    type: bigint
    foreign: orders.id

  - name: shipping_company_id
    type: bigint
    foreign: shipping_companies.id

  - name: tracking_no
    type: varchar(100)
    comment: "物流单号"

  - name: status
    type: enum
    values: [pending, shipped, in_transit, delivered, returned]
    default: pending
    comment: "物流状态"

  - name: shipped_at
    type: timestamp
    nullable: true
    comment: "发货时间"

  - name: delivered_at
    type: timestamp
    nullable: true
    comment: "签收时间"

  - name: receiver_name
    type: varchar(100)
    comment: "收件人姓名"

  - name: receiver_phone
    type: varchar(20)
    comment: "收件人电话"

  - name: receiver_address
    type: varchar(500)
    comment: "收件地址"

  - name: weight
    type: decimal(8,2)
    nullable: true
    comment: "重量(kg)"

  - name: shipping_fee
    type: decimal(10,2)
    default: 0
    comment: "运费"
```

### 2.4 物流轨迹表 (shipment_tracks)

```yaml
entity: ShipmentTrack
table: shipment_tracks
description: "物流轨迹"

fields:
  - name: id
    type: bigint
    primary: true

  - name: shipment_id
    type: bigint
    foreign: shipments.id

  - name: status
    type: varchar(50)
    comment: "轨迹状态"

  - name: description
    type: varchar(500)
    comment: "轨迹描述"

  - name: location
    type: varchar(255)
    nullable: true
    comment: "所在位置"

  - name: track_time
    type: timestamp
    comment: "轨迹时间"

  - name: raw_data
    type: json
    nullable: true
    comment: "原始数据"
```

---

## 3. 状态机定义

### 3.1 运单状态机

```yaml
entity: Shipment
states:
  - name: pending
    label: "待发货"
    initial: true

  - name: shipped
    label: "已发货"

  - name: in_transit
    label: "运输中"

  - name: delivered
    label: "已签收"

  - name: returned
    label: "已退回"

transitions:
  - from: pending
    to: shipped
    event: ShipmentShipped
    actions:
      - "保存物流单号"
      - "更新订单状态为 shipped"
      - "触发 OrderShipped 事件"

  - from: shipped
    to: in_transit
    event: ShipmentInTransit
    trigger: "物流轨迹更新"

  - from: in_transit
    to: delivered
    event: ShipmentDelivered
    actions:
      - "更新签收时间"
      - "触发 ShipmentDelivered 事件"
```

---

## 4. API 接口契约

### 4.1 发货
```yaml
endpoint: "POST /api/v1/orders/{id}/ship"
auth: true
permission: "order.ship"

request:
  body:
    shipping_company_id: int
    tracking_no: string
    receiver_name: string
    receiver_phone: string
    receiver_address: string
    weight: decimal

response:
  data:
    shipment_id: int
    tracking_no: string
    shipping_fee: decimal
```

### 4.2 查询物流轨迹
```yaml
endpoint: "GET /api/v1/shipments/{id}/tracks"
auth: true

response:
  data:
    - status: string
      description: string
      location: string
      track_time: datetime
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
    - "@template-event-listener"
  domains: []
  testing:
    - "@pest-feature-test"
```

---

**版本**: v2.0.0 | **更新日期**: 2026-04-27
