# 07 联调（Integration）

> 阶段目标：跨模块端到端验证「商品 → 购物车 → 订单 → 支付 → 履约 → 售后」全链路。
> 角色：集成测试智能体。产出：`tests/Feature/Trade/TradeFlowTest.php`。

## 7.1 联调场景

### 场景 A：实体商品完整链路（含退货退款）
1. 建 SPU(实体) + SKU(库存 5)
2. 加购 1 件 → 结算生成订单（总额 = 商品 + 运费 10）
3. 充值 500 → 余额支付（订单 → paid，余额扣减）
4. 履约：实体发货录入物流单（fulfillment → delivered）
5. 售后：仅退款 210 → 审批 → 余额回写，售后 completed

### 场景 B：虚拟商品自动交付
1. 建 SPU(虚拟) + SKU
2. 加购 2 件 → 结算（纯虚拟无运费）
3. 充值 → 余额支付
4. 履约：系统自动生成「唯一码 + 密码」并标记 delivered
5. 重发虚拟码可还原明文密码

## 7.2 跨聚合协作方式
- 订单支付成功由 `BalanceGateway` 内 `Order::transitionTo(PAID)` 完成（领域状态内聚）。
- 履约由 `FulfillmentService::createFromOrder` 读取订单商品类型决定实体/虚拟策略。
- 退款由 `AfterSaleService::approve` 调用 `PaymentService::refund` + 实体退货回滚库存。

## 7.3 并发与一致性
- 下单、支付、退款均使用 `DB::transaction` + `lockForUpdate`，避免超卖/超扣。
- 金额统一 `decimal(10,2)`，展示层格式化。

## 7.4 联调结论
两条主链路在 SQLite(:memory) 下全部通过（`tests/Feature/Trade` 2 个用例，覆盖实体+虚拟）。
