# 06-modules/product.md — 商品模块（Catalog）

> spec 子任务（依据 SPEC-MASTER 模板）。聚合根：Product(SPU)、ProductBundle(套餐)。
> 实体：Sku（属 Product 聚合）。枚举：ProductType(实体/虚拟)、ProductStatus。

## 需求（来自 03）
- US-C1 SPU + 多 SKU；US-C2 套餐引用 SKU；US-C3 实体/虚拟区分。

## 模型设计

### Product（聚合根）
- `id, name, category_id, type(ProductType), status(ProductStatus), description, created_at, updated_at, deleted_at`
- 关系：`skus()` HasMany Sku；`bundles()` 通过 bundle_items 反查

### Sku（实体，聚合内）
- `id, product_id, specs(JSON 规格键值), price(decimal 10,2), stock(int), status, created_at, updated_at`

### ProductBundle（聚合根，套餐）
- `id, name, price(decimal 10,2), status, created_at, updated_at, deleted_at`
- 关系：`items()` HasMany BundleItem(sku_id, qty)

## 领域服务：CatalogService
- `createProduct(data): Product`
- `addSku(product, data): Sku`
- `createBundle(name, price, items[]): ProductBundle`
- `decrementStock(sku, qty)`：扣库存（用于下单，事务内在 Order 侧调用）
- `typeOf(product): ProductType`

## 仓库接口（Domain） + Eloquent 实现（Infrastructure）
- `ProductRepositoryInterface`：`findOrFail(id)`, `allActive()`

## TDD
- 红：ProductTest 创建 + 软删；SkuTest 价格/库存；BundleTest 套餐价与组成。
- 绿：实现上述。
- 重构：Cache::remember 列表缓存（公开查询）。

## 门禁
`pint --test && phpstan analyse && pest --compact` 全绿。
