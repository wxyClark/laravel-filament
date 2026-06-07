# 领域约束：电商库存并发扣减 (E-commerce Inventory Concurrency)

> **版本**: v3.0 | **层級**: L2+ | **最后更新**: 2026-06-07

## 用途说明
解决高并发下单场景下的库存超卖问题。

## 适用场景
- 秒杀、限时抢购等高并发场景
- 常规下单库存扣减

## 标准内容块
```markdown
## 库存扣减约束

### 悲观锁方案（常规下单）
```php
DB::transaction(function () use ($productId, $quantity) {
    $product = Product::lockForUpdate()->findOrFail($productId);

    if ($product->stock < $quantity) {
        throw new InsufficientStockException($productId, $quantity, $product->stock);
    }

    $product->decrement('stock', $quantity);
});
```

### 乐观锁方案（高并发秒杀）
```php
// 使用版本号字段
$update = DB::table('products')
    ->where('id', $productId)
    ->where('version', $currentVersion)
    ->where('stock', '>=', $quantity)
    ->update([
        'stock' => DB::raw("stock - {$quantity}"),
        'version' => DB::raw('version + 1'),
    ]);

if ($update === 0) {
    throw new ConcurrencyConflictException('库存扣减失败，请重试');
}
```

### 约束
- 库存扣减和订单创建必须在同一事务中
- 库存字段类型为 `int UNSIGNED`，默认 0
- 退款时恢复库存也需要加锁
```
```
