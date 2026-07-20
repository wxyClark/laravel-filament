# 08 测试策略与结果（Testing）

> 角色：QA 智能体。遵循 AGENTS.md：每个 Service 方法 ≥1 单测，每个核心路径 ≥1 集成测试。

## 8.1 测试分层
- **Unit/Domains/{Module}**：纯领域逻辑（模型、Service、状态机、库存/余额计算）。
- **Feature/Trade**：跨模块端到端链路。

## 8.2 覆盖率与结果

| 模块 | 测试文件 | 用例数 | 结论 |
|------|----------|--------|------|
| Catalog | CatalogServiceTest | 6 | ✅ |
| Cart | CartServiceTest | 4 | ✅ |
| Order | OrderServiceTest | 6 | ✅ |
| Payment | PaymentServiceTest | 5 | ✅ |
| Fulfillment | FulfillmentServiceTest | 4 | ✅ |
| AfterSale | AfterSaleServiceTest | 4 | ✅ |
| Trade(联调) | TradeFlowTest | 2 | ✅ |
| **合计** | | **31** | **全绿** |

## 8.3 关键断言设计
- 金额精度：`decimal(10,2)`，断言用 `(float)` 比较（运费 10、纯虚拟 0）。
- 库存扣减：下单后 `Sku::find()->stock` 验证；退货回滚 `+qty`。
- 余额：充值/支付/退款后 `Wallet::where('user_id')->first()->balance` 断言。
- 虚拟码：唯一码前缀 `VC`、密码 `plainPassword()` 可逆解密且非空。
- 状态机：非法迁移 `transitionTo` 抛 `RuntimeException`。

## 8.4 发现并修复的问题
- `ProductBundle::totalSkuPrice` 懒加载 SKU → 在 `Model::shouldBeStrict` 下抛异常，改为 `->load('sku')`。
- Fulfillment/AfterSale 读取 `$item->sku->product` 懒加载 → 改为 `->load('items.sku.product')`。
- `Auth` 路由 `Request $request` 缺少 `use Illuminate\Http\Request` → PHPStan 报错已修。
- `AppServiceProvider` 被注释导致仓储绑定失效 → 已在 `bootstrap/providers.php` 注册（暴露了既有 auth 测试问题）。

## 8.5 既有失败（与本次无关，需另行修复）
- `Tests\Feature\Auth\*`、`Tests\Feature\Api\Auth\JwtAuthTest`、`Tests\Feature\Filament\Auth\SessionAuthTest`、
  `Tests\Unit\Domains\Auth\*`、`Tests\Unit\Domains\User\*`、`Tests\Unit\Architecture\AuthDDDTest/UserDDDTest`、
  `Tests\Feature\Filament\Address\*`：均来自**之前会话的 auth 重构未完成 + Filament 路由未注册**，非本次 DDD 交易域引入。
  注册 `AppServiceProvider` 后这些既有缺陷被严格模式/绑定显式暴露，建议单独排期修复。
