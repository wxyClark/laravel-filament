# 领域约束：分销佣金递归计算

## 用途说明
高效处理多级分销关系链的佣金分配。

## 适用场景
- 三级分销、团队计酬、推广奖励系统。

## 标准内容块
```markdown
## 算法约束：MySQL 递归 CTE
1. **关系链获取**：使用 MySQL 8.0+ 的 `WITH RECURSIVE` 语法或 Laravel Eloquent CTE 插件获取指定层级（如 3 级）的上级用户。
2. **利润基数**：佣金必须基于订单的 `profit_margin`（利润）而非 `total_amount`（总额）计算。
3. **状态流转**：初始佣金状态为 `frozen`（冻结），待过了售后期后自动转为 `available`（可提现）。

## 代码模式参考
```php
// 伪代码：获取 3 级上级
$uplines = DistributionRelationship::withUplines($userId, depth: 3)->get();
```
```
