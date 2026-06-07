# 领域约束：分销佣金递归计算 (Distribution Commission)

> **版本**: v3.0 | **层級**: L2+ | **最后更新**: 2026-06-07

## 用途说明
高效处理多级分销关系链的佣金分配。

## 适用场景
- 三级分销、团队计酬、推广奖励系统
- 佣金冻结/解冻、提现审核流程

## 标准内容块
```markdown
## 算法约束：MySQL 递归 CTE

### 1. 关系链获取
使用 MySQL 8.0+ 的 `WITH RECURSIVE` 语法获取指定层级（如 3 级）的上级用户。

```php
$uplines = DistributionRelationship::withUplines($userId, depth: 3)->get();
```

### 2. 利润基数
佣金必须基于订单的 `profit_margin`（利润）而非 `total_amount`（总额）计算。

### 3. 状态流转
初始佣金状态为 `frozen`（冻结），待过了售后期后自动转为 `available`（可提现）。
- 冻结期：订单完成后 7 天
- 解冻触发：定时任务扫描到期记录

### 4. 并发安全
佣金计算必须在事务中执行，使用 `lockForUpdate()` 锁定关系链。
```
```
