# 💰 分销场景模板

> **版本**: v3.0 | **层級**: L5+ | **最后更新**: 2026-06-07

## 场景概述
多级分销系统的核心业务场景，包括分销商管理、佣金计算、提现审核等。

## 领域模型扩展

### 表结构
| 表名 | 说明 |
|------|------|
| `distribution_users` | 分销商关系 |
| `commission_logs` | 佣金流水 |
| `withdrawals` | 提现记录 |

### 状态机
```
commission: frozen → available → withdrawn
withdrawal: pending → approved → paid
              ↓         ↓
            rejected  failed
```

### 关键约束
- 佣金基于利润计算而非总额
- 三级分销上限
- 佣金冻结期 7 天
- 提现需审核

## 提示词模板
```
## L3: 角色
### 资产管家 + 分销领域约束

## L4: 任务
实现分销服务：
1. 分销商关系绑定
2. 佣金计算（递归 CTE）
3. 佣金解冻（定时任务）
4. 提现申请与审核
5. 分销报表

## 领域约束
- constraint-distribution-commission
```

---

**版本**: v3.0 | **最后更新**: 2026-06-07
