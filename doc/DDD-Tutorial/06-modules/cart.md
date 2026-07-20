# 06-modules/cart.md — 购物车模块（Cart）

> spec 子任务。聚合根：Cart。实体：CartItem。从简实现（登录态）。

## 需求（来自 03）
- US-CA1 加购/改量/选中/取消，超库存拦截；同 SKU 合并。
- US-CA2 结算时选中项转订单（在 Order 模块调用 `selectedList()` + `clearSelected()`）。

## 模型
- **Cart**：`id, user_id, timestamps`
- **CartItem**：`id, cart_id, sku_id, qty, selected(bool)`，唯一 `(cart_id,sku_id)`

## 领域服务 CartService
- `forUser(userId): Cart` firstOrCreate
- `addItem(cart, skuId, qty, selected)`：合并 + 库存校验
- `setSelected(item, bool)` / `updateQty(item, qty)`
- `selectedTotal(cart): float` / `selectedList(cart)` / `clearSelected(cart)`

## TDD
红：加购合并、超库存拦截、选中合计、清空选中。
绿：实现。
重构：无。

## 门禁
`pint --test && phpstan analyse && pest --compact`。
