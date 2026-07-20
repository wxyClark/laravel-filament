# 06-modules/fulfillment.md — 履约发货（Fulfillment）

> spec 子任务。聚合根：Fulfillment。实体：Shipment（实体）、VirtualCode（虚拟）。
> 虚拟交付 = 唯一码 + 密码（加密存储，可重发）。

## 需求（来自 03）
- US-F1 实体订单发货：录入物流单号 + 承运商，状态→delivered。
- US-F2 虚拟订单发货：生成唯一码 + 密码并交付。

## 模型
- **Fulfillment**：`order_id, type(entity|virtual), status(pending|delivered), delivered_at`
- **Shipment**：`fulfillment_id, carrier, tracking_no`
- **VirtualCode**：`fulfillment_id, code(唯一), password(加密), delivered_at`

## 枚举
- FulfillmentType(entity|virtual)、FulfillmentStatus(pending|delivered)

## 领域服务 FulfillmentService
- `createFromOrder(order)`：按订单是否含虚拟商品决定履约类型；虚拟直接生成码交付。
- `ship(fulfillment, carrier, trackingNo)`：实体发货。
- `resendVirtualCodes(fulfillment)`：重发虚拟码（明文密码，可逆加密）。

## 安全约定
- 虚拟码密码用 `Crypt::encryptString` 加密存储（可逆，支持重发），`plainPassword()` 解密展示。

## TDD
红：虚拟订单自动生成唯一码+密码、实体订单需物流单、重发返回明文、非已支付订单不可履约。
绿：实现。

## 门禁
`pint --test && phpstan analyse && pest --compact`。
