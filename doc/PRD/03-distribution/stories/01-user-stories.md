# 📖 二级分销模块 - 用户故事

> **L3: 用户故事层级** | **RAG 友好格式** | **可直接组装到提示词**

---

## 📋 元数据

```yaml
module: "distribution"
document_type: "user_stories"
version: "1.0"
total_stories: 6
priority_distribution:
  P0: 4
  P1: 2
```

---

## 🎯 US-DIST-001: 分销员注册与审核

**作为** 平台管理员  
**我希望** 能够审核分销员注册申请  
**以便** 确保分销员质量，维护平台信誉

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "分销员提交注册申请"
    given: "用户已登录且满足基本条件"
    when: "提交分销员注册申请"
    then: "申请状态为 pending，等待管理员审核"

  - scenario: "管理员审核通过"
    given: "分销员申请状态为 pending"
    when: "管理员审核通过"
    then: "分销员状态变为 active，获得分销码"

  - scenario: "管理员审核拒绝"
    given: "分销员申请状态为 pending"
    when: "管理员审核拒绝并填写原因"
    then: "分销员状态变为 rejected，用户收到拒绝通知"

  - scenario: "分销员查看分销码"
    given: "分销员状态为 active"
    when: "查看个人分销中心"
    then: "显示专属分销码和分销链接"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Distributor
    - DistributorApplication
  state_machine: "Distributor.status"
  events:
    - DistributorApplicationSubmitted
    - DistributorApproved
    - DistributorRejected
  notifications:
    - DistributorApprovalNotification
    - DistributorRejectionNotification
  tables:
    - distributors
    - distributor_applications
```

---

## 🎯 US-DIST-002: 分销订单关联

**作为** 分销员  
**我希望** 客户通过我的分销链接下单能自动关联  
**以便** 确保我的分销业绩被正确记录

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "客户通过分销链接访问"
    given: "分销链接包含分销员ID参数"
    when: "客户访问商品页面"
    then: "客户浏览器记录分销员ID（cookie/localStorage）"

  - scenario: "客户下单关联分销员"
    given: "客户已通过分销链接访问"
    when: "客户完成下单"
    then: "订单关联分销员ID，记录分销层级"

  - scenario: "直接访问不关联分销员"
    given: "客户直接访问商品页面"
    when: "客户完成下单"
    then: "订单无分销员关联"

  - scenario: "分销链接有效期"
    given: "分销链接超过30天有效期"
    when: "客户通过过期链接下单"
    then: "不关联分销员"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Order
    - Distributor
    - DistributorLink
  implementation:
    - "Cookie/LocalStorage 存储 distributor_id"
    - "订单创建时检查并关联分销员"
    - "分销链接有效期：30天"
  tables:
    - orders (distributor_id, distributor_level)
    - distributor_links
```

---

## 🎯 US-DIST-003: 佣金自动计算

**作为** 系统  
**我希望** 订单完成后自动计算分销佣金  
**以便** 确保佣金计算准确及时

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "一级分销佣金计算"
    given: "订单关联一级分销员"
    when: "订单状态变为 completed"
    then: "计算一级佣金（订单金额 × 一级佣金比例）"

  - scenario: "二级分销佣金计算"
    given: "订单关联二级分销员（上级分销员的上级）"
    when: "订单状态变为 completed"
    then: "计算二级佣金（订单金额 × 二级佣金比例）"

  - scenario: "佣金计算精度"
    given: "订单金额为小数"
    when: "计算佣金"
    then: "佣金保留2位小数，四舍五入"

  - scenario: "佣金计算时机"
    given: "订单状态变为 completed"
    when: "触发 OrderCompleted 事件"
    then: "异步计算并记录佣金"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Commission
    - Order
    - Distributor
  events:
    - OrderCompleted
  listeners:
    - CalculateCommissionListener
  calculation:
    formula: "order_amount × commission_rate"
    primary_rate: "config('distribution.primary_rate')"
    secondary_rate: "config('distribution.secondary_rate')"
  tables:
    - commissions
```

---

## 🎯 US-DIST-004: 佣金提现管理

**作为** 分销员  
**我希望** 能够申请提现我的佣金  
**以便** 将佣金收入转为实际收益

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "分销员申请提现"
    given: "分销员可用佣金 > 0"
    when: "提交提现申请"
    then: "创建提现申请，状态为 pending"

  - scenario: "提现金额限制"
    given: "分销员可用佣金为 100 元"
    when: "申请提现 150 元"
    then: "提示可用佣金不足，拒绝申请"

  - scenario: "最低提现金额"
    given: "系统设置最低提现金额为 100 元"
    when: "申请提现 50 元"
    then: "提示未达到最低提现金额"

  - scenario: "管理员审核提现"
    given: "提现申请状态为 pending"
    when: "管理员审核通过"
    then: "更新佣金状态为 paid，记录提现时间"

  - scenario: "提现拒绝"
    given: "提现申请状态为 pending"
    when: "管理员审核拒绝"
    then: "更新佣金状态为 rejected，退回可用佣金"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Commission
    - WithdrawalRequest
  state_machine: "Commission.status"
  states:
    - pending
    - approved
    - paid
    - rejected
  validation:
    min_amount: "config('distribution.min_withdrawal')"
    max_amount: "available_commission"
  tables:
    - commissions
    - withdrawal_requests
```

---

## 🎯 US-DIST-005: 分销员业绩统计

**作为** 分销员  
**我希望** 能够查看我的分销业绩统计  
**以便** 了解我的分销表现和收益情况

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "查看业绩概览"
    given: "分销员登录分销中心"
    when: "查看业绩概览"
    then: "显示总佣金、可用佣金、已提现佣金、下级分销员数量"

  - scenario: "查看佣金明细"
    given: "分销员查看佣金列表"
    when: "筛选佣金记录"
    then: "显示佣金来源订单、金额、状态、时间"

  - scenario: "查看下级分销员"
    given: "分销员有下级分销员"
    when: "查看下级分销员列表"
    then: "显示下级分销员信息和业绩"

  - scenario: "查看分销图表"
    given: "分销员查看业绩图表"
    when: "选择时间范围"
    then: "显示佣金趋势图、订单数量图"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Distributor
    - Commission
    - DistributorStatistics
  api_endpoints:
    - GET /api/distributor/dashboard
    - GET /api/distributor/commissions
    - GET /api/distributor/subordinates
  statistics:
    - total_commission
    - available_commission
    - withdrawn_commission
    - subordinate_count
    - monthly_trend
  caching:
    - "统计数据缓存 5 分钟"
```

---

## 🎯 US-DIST-006: 分销员等级管理

**作为** 平台管理员  
**我希望** 能够管理分销员等级和佣金比例  
**以便** 激励分销员提升业绩

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "查看分销员等级列表"
    given: "管理员进入分销员等级管理"
    when: "查看等级列表"
    then: "显示所有等级及其佣金比例、门槛"

  - scenario: "创建分销员等级"
    given: "管理员创建新等级"
    when: "填写等级名称、佣金比例、升级门槛"
    then: "创建成功，等级列表更新"

  - scenario: "编辑分销员等级"
    given: "管理员编辑等级"
    when: "修改佣金比例或门槛"
    then: "更新成功，不影响已有关联"

  - scenario: "自动升级分销员"
    given: "分销员累计佣金达到升级门槛"
    when: "系统检测到满足升级条件"
    then: "自动升级分销员等级，更新佣金比例"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - DistributorLevel
    - Distributor
  tables:
    - distributor_levels
    - distributors (level_id)
  auto_upgrade:
    trigger: "CommissionCreated event"
    check: "累计佣金 >= level.threshold"
  api_endpoints:
    - GET /api/admin/distributor-levels
    - POST /api/admin/distributor-levels
    - PUT /api/admin/distributor-levels/{id}
```

---

## 📊 用户故事汇总

| 故事ID | 优先级 | 复杂度 | 关联实体 |
|--------|--------|--------|---------|
| US-DIST-001 | P0 | 中 | Distributor, DistributorApplication |
| US-DIST-002 | P0 | 高 | Order, Distributor |
| US-DIST-003 | P0 | 高 | Commission, Order |
| US-DIST-004 | P0 | 中 | Commission, WithdrawalRequest |
| US-DIST-005 | P1 | 中 | Distributor, Commission |
| US-DIST-006 | P1 | 中 | DistributorLevel, Distributor |

---

**版本**: v1.0 | **更新日期**: 2026-04-24
