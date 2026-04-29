# 📋 电商核心模块 - 领域事件

> **L4: 需求碎片层级** | **RAG 友好格式** | **可直接组装到提示词**

---

## 📋 元数据

```yaml
module: "ecommerce"
document_type: "domain_events"
version: "2.0"
events_count: 8
consumers_count: 15
```

---

## 📦 事件清单

### OrderCreated - 订单创建

```yaml
event_name: "OrderCreated"
module: "ecommerce"
entity: "Order"
trigger: "用户提交订单"
producer: "OrderService"

payload:
  order_id: int
  order_sn: string
  user_id: int
  total_amount: decimal
  pay_amount: decimal
  items: array
    - sku_id: int
      quantity: int
      price: decimal
  created_at: datetime

consumers:
  - module: "drp"
    service: "InventoryService"
    method: "lockStock"
    description: "锁定库存"
  - module: "crm"
    service: "CustomerService"
    method: "updateOrderStats"
    description: "更新客户订单统计"
```

### OrderPaid - 订单支付

```yaml
event_name: "OrderPaid"
module: "ecommerce"
entity: "Order"
trigger: "支付回调成功"
producer: "PaymentService"

payload:
  order_id: int
  order_sn: string
  user_id: int
  payment_id: int
  pay_amount: decimal
  payment_method: string
  paid_at: datetime

consumers:
  - module: "drp"
    service: "InventoryService"
    method: "deductStock"
    description: "扣减库存"
  - module: "finance"
    service: "AccountService"
    method: "createIncomeRecord"
    description: "生成收款记录"
  - module: "distribution"
    service: "CommissionService"
    method: "createPendingCommission"
    description: "创建待结算佣金"
  - module: "crm"
    service: "CustomerService"
    method: "updateConsumptionStats"
    description: "更新客户消费统计"
```

### OrderShipped - 订单发货

```yaml
event_name: "OrderShipped"
module: "ecommerce"
entity: "Order"
trigger: "管理员发货"
producer: "OrderService"

payload:
  order_id: int
  order_sn: string
  shipping_company: string
  tracking_number: string
  shipped_by: int
  shipped_at: datetime

consumers:
  - module: "crm"
    service: "CustomerService"
    method: "updateShippingStatus"
    description: "更新客户物流状态"
```

### OrderCompleted - 订单完成

```yaml
event_name: "OrderCompleted"
module: "ecommerce"
entity: "Order"
trigger: "用户确认收货或自动确认"
producer: "OrderService"

payload:
  order_id: int
  order_sn: string
  user_id: int
  total_amount: decimal
  completed_at: datetime
  auto_completed: boolean

consumers:
  - module: "distribution"
    service: "CommissionService"
    method: "calculateCommission"
    description: "计算分销佣金"
  - module: "crm"
    service: "CustomerService"
    method: "updateOrderStats"
    description: "更新客户订单统计"
  - module: "finance"
    service: "AccountService"
    method: "createReceivable"
    description: "生成应收账款"
```

### OrderCancelled - 订单取消

```yaml
event_name: "OrderCancelled"
module: "ecommerce"
entity: "Order"
trigger: "用户取消或超时取消"
producer: "OrderService"

payload:
  order_id: int
  order_sn: string
  user_id: int
  reason: string
  cancelled_by: int
  cancelled_at: datetime

consumers:
  - module: "drp"
    service: "InventoryService"
    method: "releaseStock"
    description: "释放锁定库存"
```

### OrderRefunded - 订单退款

```yaml
event_name: "OrderRefunded"
module: "ecommerce"
entity: "Order"
trigger: "退款完成"
producer: "RefundService"

payload:
  order_id: int
  order_sn: string
  user_id: int
  refund_amount: decimal
  refund_reason: string
  refunded_by: int
  refunded_at: datetime

consumers:
  - module: "drp"
    service: "InventoryService"
    method: "restoreStock"
    description: "恢复库存"
  - module: "distribution"
    service: "CommissionService"
    method: "cancelCommission"
    description: "取消/扣减佣金"
  - module: "finance"
    service: "AccountService"
    method: "createRefundRecord"
    description: "生成退款记录"
  - module: "crm"
    service: "CustomerService"
    method: "updateRefundStats"
    description: "更新客户退款统计"
```

### PaymentSuccess - 支付成功

```yaml
event_name: "PaymentSuccess"
module: "ecommerce"
entity: "Payment"
trigger: "支付网关回调"
producer: "PaymentGateway"

payload:
  payment_id: int
  order_id: int
  amount: decimal
  payment_method: string
  transaction_no: string
  paid_at: datetime

consumers:
  - module: "ecommerce"
    service: "OrderService"
    method: "handlePaymentSuccess"
    description: "更新订单状态并触发 OrderPaid"
```

### PaymentFailed - 支付失败

```yaml
event_name: "PaymentFailed"
module: "ecommerce"
entity: "Payment"
trigger: "支付网关回调"
producer: "PaymentGateway"

payload:
  payment_id: int
  order_id: int
  amount: decimal
  payment_method: string
  failure_reason: string
  failed_at: datetime

consumers:
  - module: "ecommerce"
    service: "OrderService"
    method: "handlePaymentFailed"
    description: "记录支付失败原因"
```

---

## 📊 事件汇总

| 事件名称 | 触发时机 | 消费模块数 | 关键消费者 |
|---------|---------|-----------|-----------|
| OrderCreated | 用户提交订单 | 2 | DRP, CRM |
| OrderPaid | 支付成功 | 4 | DRP, Finance, Distribution, CRM |
| OrderShipped | 管理员发货 | 1 | CRM |
| OrderCompleted | 确认收货 | 3 | Distribution, CRM, Finance |
| OrderCancelled | 取消订单 | 1 | DRP |
| OrderRefunded | 退款完成 | 4 | DRP, Distribution, Finance, CRM |
| PaymentSuccess | 支付回调 | 1 | Ecommerce |
| PaymentFailed | 支付失败 | 1 | Ecommerce |

---

## 🔧 事件监听器生成提示词

```markdown
# 任务：生成电商领域事件监听器

## 角色
@TradeEngineer

## 依赖
- 包: spatie/laravel-event-sourcing (可选)
- 队列: RabbitMQ

## 任务
请为以下事件创建监听器：

### 1. OrderPaid 监听器
- HandleOrderPaidDrp (DRP库存扣减)
- HandleOrderPaidFinance (财务收款)
- HandleOrderPaidDistribution (分销佣金)
- HandleOrderPaidCrm (客户统计)

### 2. OrderCompleted 监听器
- HandleOrderCompletedDistribution (佣金计算)
- HandleOrderCompletedFinance (应收账款)

### 3. OrderRefunded 监听器
- HandleOrderRefundedDrp (库存恢复)
- HandleOrderRefundedDistribution (佣金取消)

## 输出要求
- 所有监听器实现 ShouldQueue 接口
- 包含 try/catch 异常处理
- 记录处理日志
- 失败重试机制（3次，指数退避）
```

---

**版本**: v2.0.0 | **更新日期**: 2026-04-27
