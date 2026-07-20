# 06-modules/order.md — 订单模块（Order）

> spec 子任务。聚合根：Order。实体：OrderItem。状态机：pending→paid→completed / canceled。

## 需求（来自 03）
- US-O1 从购物车选中项生成订单，计算总额（商品金额 + 运费）。
- US-O2 状态机受约束，非法迁移抛异常。

## 模型
- **Order**：`order_no(唯一), user_id, status, total_amount, freight, paid_at, softDeletes`
- **OrderItem**：`order_id, sku_id, product_id, qty, unit_price, subtotal`

## 枚举 OrderStatus
- PENDING/PAID/COMPLETED/CANCELED；`canTransitionTo()` 约束迁移。

## 领域服务 OrderService
- `checkout(cart): Order`
  - 取选中项；实体商品订单加运费 10，纯虚拟 0；
  - 事务内 `lockForUpdate` 校验并扣 SKU 库存；生成 order + items；清空购物车选中项。
- 状态迁移：`transitionTo()` 在 Order 模型内。

## TDD
红：结算生成订单与 items、金额含运费、库存扣减、状态机非法迁移抛异常。
绿：实现。

## 门禁
`pint --test && phpstan analyse && pest --compact`。
