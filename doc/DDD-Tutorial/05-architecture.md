# 05 架构设计（领域架构师）

> 阶段目标：给出限界上下文地图、聚合根、实体/值对象、领域事件与 ER。
> 遵循 `.ai/skills/architecture-testing` 的 DDD 分层与依赖方向：Domain ← Infrastructure ← Http。

## 5.1 限界上下文地图

```
┌──────────────────────────────────────────────────────────────┐
│  Catalog 上下文          Cart 上下文        Order 上下文       │
│  Product(SPU)            Cart              Order              │
│   ├ Sku (实体)            ├ CartItem        ├ OrderItem(实体)  │
│   └ ProductBundle(套餐)   └ 选中逻辑         └ 状态机           │
├──────────────────────────────────────────────────────────────┤
│  Payment 上下文        Fulfillment 上下文      AfterSale 上下文 │
│  Wallet(余额)          Fulfillment            AfterSale        │
│  PaymentRecord         ├ Shipment(实体)       └ 退款/退货       │
│  PaymentGateway(接口)  └ VirtualCode(码+密)                      │
└──────────────────────────────────────────────────────────────┘
         │                │                  │
         └── 领域事件串联 ──┘                  │
        OrderPaid → 触发 Fulfillment 创建；PaymentRefunded → 售后完结
```

## 5.2 聚合根与核心模型

### Catalog（商品）
- **Product（聚合根）**：`id, name, category_id, type(enum Entity|Virtual), status, created_at, deleted_at`
- **Sku（实体，属 Product 聚合）**：`id, product_id, specs(JSON), price(decimal), stock(int), status`
- **ProductBundle（聚合根，套餐）**：`id, name, price(decimal), status`
  - `bundle_items`：bundle_id, sku_id, qty（引用 SKU，不自持库存）

### Cart（购物车）
- **Cart（聚合根）**：`id, user_id, created_at`
- **CartItem（实体）**：`id, cart_id, sku_id, qty, selected(bool)`

### Order（订单）
- **Order（聚合根）**：`id, user_id, status(enum), total_amount, freight, paid_at, created_at, deleted_at`
- **OrderItem（实体）**：`id, order_id, sku_id, product_id, qty, unit_price, subtotal`

### Payment（支付）
- **Wallet（聚合根，按 user 维度）**：`id, user_id, balance(decimal)`
- **PaymentRecord（聚合根）**：`id, user_id, order_id(nullable), type(enum Pay|Recharge|Refund), amount, gateway, status, created_at`
- **PaymentGateway（接口）**：`pay(Order, amount): PaymentRecord` / `supports(type)`

### Fulfillment（履约）
- **Fulfillment（聚合根）**：`id, order_id, type(enum Entity|Virtual), status`
- **Shipment（实体，实体履约）**：`id, fulfillment_id, tracking_no, carrier`
- **VirtualCode（实体，虚拟履约）**：`id, fulfillment_id, code(唯一), password_hash, delivered_at`

### AfterSale（售后）
- **AfterSale（聚合根）**：`id, order_id, type(enum Refund|Return), status, refund_amount, created_at`
  - 关联原 PaymentRecord 做退款。

## 5.3 领域事件

- `OrderCreated`：待支付
- `OrderPaid`：触发 Payment 扣款成功 → 触发 Fulfillment 建单
- `FulfillmentDelivered`：发货完成
- `PaymentRefunded`：售后退款完成 → AfterSale 完结

## 5.4 依赖方向（架构测试约束）

```
Domain/*   → 仅依赖自身 + 标准库（不 use Illuminate 具体实现）
Infrastructure/* → implements Domain 接口，使用 Eloquent
Http/Filament/*  → 调用 Domain Service，不直连 Eloquent
```

## 5.5 数据库要点

- 所有金额：`decimal(10,2)`。
- 核心表软删除：`products, orders, carts, after_sales`。
- 索引：`skus(product_id)`、`order_items(order_id)`、`payment_records(user_id,order_id)`、`virtual_codes(code)` 唯一。
- 并发：Wallet 更新使用 `lockForUpdate`。

## 5.6 模块开发顺序

商品 → 购物车 → 订单 → 支付 → 履约 → 售后（详见 06-modules/*）。
