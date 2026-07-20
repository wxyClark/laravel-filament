# 06-modules/payment.md — 支付插件（Payment）

> spec 子任务。聚合根：Wallet、PaymentRecord。接口：PaymentGateway（策略）。仅实现余额支付 + 充值。

## 需求（来自 03）
- US-P1 余额支付订单，余额不足拦截，成功扣减并生成流水；支付成功订单转 PAID。
- US-P2 充值即增余额，生成充值流水。
- US-P3 支付方式可扩展：新增网关只需实现 `PaymentGateway` 并 `register()`。

## 模型
- **Wallet**：`user_id(唯一), balance(decimal)`
- **PaymentRecord**：`user_id, order_id(nullable), type(pay|recharge|refund), amount, gateway, status`

## 枚举
- PaymentType(pay|recharge|refund)、PaymentGatewayType(balance)

## 网关
- `PaymentGateway` 接口：`type()/supports()/pay(Order)`
- `BalanceGateway`：事务内 `lockForUpdate` 扣余额 → 写流水 → 订单转 PAID

## 领域服务 PaymentService
- `pay(order, gateway)` 按 gateway 分发
- `recharge(userId, amount)` / `refund(userId, amount, orderId)`

## TDD
红：支付成功扣款+订单PAID、余额不足抛错、充值增额、退款增额、网关不支持抛错。
绿：实现。

## 门禁
`pint --test && phpstan analyse && pest --compact`。
