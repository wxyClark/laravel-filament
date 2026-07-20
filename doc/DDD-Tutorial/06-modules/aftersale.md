# 06-modules/aftersale.md — 售后模块（AfterSale，从简）

> spec 子任务。聚合根：AfterSale。从简实现：仅退款 / 退货退款。

## 需求（来自 03）
- US-A1 已支付订单发起退款，退款回写余额（关联原订单）。
- US-A2 退货退款：实体商品退回，库存回滚；虚拟商品不支持退货。

## 模型
- **AfterSale**：`order_id, user_id, type(refund|return), status(pending|completed|rejected), refund_amount`

## 枚举
- AfterSaleType(refund|return)、AfterSaleStatus(pending|completed|rejected)

## 领域服务 AfterSaleService（依赖 PaymentService）
- `request(order, type, amount)`：校验订单状态；虚拟商品禁止退货。
- `approve(afterSale)`：事务内退款（PaymentService.refund）+ 退货回滚库存 + 完成。

## TDD
红：退款回写余额、退货回滚库存、虚拟商品不可退货、非已支付订单不可申请。
绿：实现。

## 门禁
`pint --test && phpstan analyse && pest --compact`。
