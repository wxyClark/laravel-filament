# DDD 教程模块：通用商品交易域从 0 到 1

> 本教程以「当前大模型 + 提示词系统 + rules + agent + skills」协作开发一套通用商品交易域为线索，
> 完整记录从**产品诉求 → 头脑风暴 → 需求梳理 → 评审 → 架构设计 → 开发 → 联调 → 测试 → CI 校验**
> 的端到端过程。每一个阶段都有 AI 参与，并以多智能体（主会话 + Task 子代理）配合推进。

技术底座：**Laravel 12 + Filament 3 + PHP 8.5 + Pest**，项目已具备 DDD 分层（`app/Domains` / `app/Infrastructure` / `app/Http`）、
架构测试（`.ai/skills/architecture-testing`）、Pint / PHPStan level 5 / Pest 门禁。

---

## 目录

| 文件 | 阶段 | AI 角色 |
|------|------|---------|
| [01-product-request.md](01-product-request.md) | 产品原始诉求 | 用户 → 存档 |
| [02-brainstorm.md](02-brainstorm.md) | 头脑风暴 | 多智能体发散 |
| [03-requirements.md](03-requirements.md) | 需求梳理 | 需求分析师 |
| [04-review.md](04-review.md) | 需求评审 | 评审官 + 架构师 |
| [05-architecture.md](05-architecture.md) | 架构设计 | 领域架构师 |
| [06-modules/product.md](06-modules/product.md) | 商品模块开发 | 开发智能体 + 测试智能体 |
| [06-modules/cart.md](06-modules/cart.md) | 购物车模块开发 | 开发智能体 |
| [06-modules/order.md](06-modules/order.md) | 订单模块开发 | 开发智能体 |
| [06-modules/payment.md](06-modules/payment.md) | 支付插件开发 | 开发智能体 |
| [06-modules/aftersale.md](06-modules/aftersale.md) | 售后模块开发 | 开发智能体 |
| [06-modules/fulfillment.md](06-modules/fulfillment.md) | 履约发货开发 | 开发智能体 |
| [07-integration.md](07-integration.md) | 联调 | 集成测试智能体 |
| [08-testing.md](08-testing.md) | 测试策略与结果 | QA 智能体 |
| [09-ci.md](09-ci.md) | CI 校验门禁 | CI 智能体 |
| [SPEC-MASTER.md](SPEC-MASTER.md) | 结构化 spec 主提示词 | 元指令 |

---

## 核心约束（来自 AGENTS.md）

- 每个域落在 `app/Domains/{Domain}/`；基础设施/框架适配在 `app/Infrastructure/`；HTTP 在 `app/Http/`、`app/Filament/`。
- 金额字段必须用 `decimal(10,2)`，禁止 FLOAT/DOUBLE。
- 核心业务表开启软删除。
- 类文件含 `declare(strict_types=1)`；import 字母序；类成员顺序 trait→const→property→constructor→method。
- 提交前必过：`pint --test` → `phpstan analyse` → `pest --compact`。
- DDD 边界：Domain 不依赖框架；Infrastructure 实现 Domain 接口；Http 仅调用 Service。

## 模块清单

1. 商品（SPU+SKU、套餐、实体/虚拟）
2. 购物车
3. 订单
4. 支付（余额 + 充值，策略扩展点）
5. 售后（退款/退货，从简）
6. 履约发货（实体物流 / 虚拟唯一码+密码）

## 如何复现本教程

按文件顺序阅读即可。每个 `06-modules/*.md` 对应一次「spec 子任务 → TDD 红绿 → 联调 → 测试」闭环。
代码最终落入 `app/Domains/**`、`app/Infrastructure/**`、`app/Http/**`、`database/migrations/**`、`tests/**`。
