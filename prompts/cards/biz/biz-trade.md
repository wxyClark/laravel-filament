# 交易域业务卡片

> **卡片 ID**: `biz-trade`
> **优先级**: L2
> **依赖**: `base-domain`

---

## 交易域核心实体

### Order (订单)
- 表名: `orders`
- 主字段: `order_number`, `customer_id`, `total_amount`, `status`
- 枚举: `OrderStatus` (pending/paid/processing/shipped/delivered/completed/cancelled/refunded)

### OrderItem (订单项)
- 表名: `order_items`
- 主字段: `order_id`, `product_id`, `quantity`, `unit_price`, `total_price`

### Payment (支付记录)
- 表名: `payments`
- 主字段: `order_id`, `amount`, `method`, `status`, `transaction_id`

## 核心 Service

| Service | 职责 |
|---------|------|
| `OrderService` | 订单创建、取消、状态流转 |
| `PaymentService` | 支付处理 |
| `CartService` | 购物车管理 |

## 关键字段

```yaml
fields:
  - name: order_number
    type: text
    required: true
    unique: true
    label: 订单号
  - name: customer_id
    type: relationship
    relationship: customer
    label: 客户
    required: true
  - name: total_amount
    type: money
    currency: CNY
    required: true
  - name: status
    type: select
    enum: OrderStatus
    required: true
  - name: paid_at
    type: datetime
    label: 支付时间
  - name: notes
    type: textarea
    maxLength: 2000
    columnSpan: full
```

## 订单状态流转

```
pending → paid → processing → shipped → delivered → completed
         ↓                           ↓
      cancelled                  cancelled/refunded
```

## Filament 资源

| Resource | 特殊配置 |
|----------|---------|
| `OrderResource` | 带 OrderItemsRelationManager, PaymentRelationManager |
| `PaymentResource` | 关联 Order |
