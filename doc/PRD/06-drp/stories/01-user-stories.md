# 📖 DRP 进销存模块 - 用户故事

> **L3: 用户故事层级** | **RAG 友好格式** | **可直接组装到提示词**

---

## 📋 元数据

```yaml
module: "drp"
document_type: "user_stories"
version: "1.0"
total_stories: 6
priority_distribution:
  P0: 4
  P1: 2
```

---

## 🎯 US-DRP-001: 采购入库管理

**作为** 采购人员  
**我希望** 能够创建采购单并管理入库  
**以便** 确保库存充足，满足销售需求

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "创建采购单"
    given: "采购人员进入采购管理页面"
    when: "选择供应商、仓库，添加采购商品"
    then: "采购单创建成功，状态为 draft"

  - scenario: "提交采购审批"
    given: "采购单状态为 draft"
    when: "提交审批"
    then: "采购单状态变为 pending"

  - scenario: "收货入库"
    given: "采购单状态为 pending/approved"
    when: "确认收货数量"
    then: "库存增加，采购单状态更新"

  - scenario: "部分收货"
    given: "采购单有多个商品"
    when: "部分商品收货"
    then: "已收货商品入库，采购单状态部分完成"

  - scenario: "采购单取消"
    given: "采购单状态为 draft/pending"
    when: "取消采购单"
    then: "采购单状态变为 cancelled"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - PurchaseOrder
    - PurchaseOrderItem
    - Supplier
  tables:
    - purchase_orders
    - purchase_order_items
  state_machine: "PurchaseOrder.status"
  states:
    - draft
    - pending
    - approved
    - received
    - partial_received
    - cancelled
  events:
    - PurchaseOrderSubmitted
    - PurchaseOrderReceived
```

---

## 🎯 US-DRP-002: 销售出库管理

**作为** 销售人员  
**我希望** 能够创建销售单并管理出库  
**以便** 及时发货，满足客户需求

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "创建销售单"
    given: "销售人员进入销售管理页面"
    when: "填写客户信息，添加销售商品"
    then: "销售单创建成功，状态为 draft"

  - scenario: "出库发货"
    given: "销售单状态为 draft/pending"
    when: "确认出库"
    then: "库存扣减，销售单状态更新为 shipped"

  - scenario: "库存不足检查"
    given: "商品库存不足"
    when: "尝试出库"
    then: "提示库存不足，阻止出库"

  - scenario: "销售单取消"
    given: "销售单状态为 draft"
    when: "取消销售单"
    then: "销售单状态变为 cancelled"

  - scenario: "销售单完成"
    given: "销售单已发货"
    when: "客户确认收货"
    then: "销售单状态变为 completed"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - SalesOrder
    - SalesOrderItem
  tables:
    - sales_orders
    - sales_order_items
  state_machine: "SalesOrder.status"
  states:
    - draft
    - pending
    - shipped
    - completed
    - cancelled
  inventory_check:
    - "出库前检查库存充足"
    - "使用 lockForUpdate 防止并发超卖"
```

---

## 🎯 US-DRP-003: 库存实时查询

**作为** 仓库管理员  
**我希望** 能够实时查看库存情况  
**以便** 及时了解库存状态，做出补货决策

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "查看库存列表"
    given: "仓库管理员进入库存管理页面"
    when: "查看库存列表"
    then: "显示所有商品库存信息"

  - scenario: "按仓库筛选"
    given: "有多个仓库"
    when: "选择特定仓库"
    then: "显示该仓库的库存信息"

  - scenario: "按商品筛选"
    given: "商品数量较多"
    when: "搜索商品名称/编码"
    then: "显示匹配商品的库存"

  - scenario: "库存预警"
    given: "商品库存低于最低库存"
    when: "查看库存列表"
    then: "库存预警商品高亮显示"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Inventory
    - Product
    - Warehouse
  tables:
    - inventory
  fields:
    - product_id
    - warehouse_id
    - quantity
    - reserved_quantity
    - min_stock
    - max_stock
  computed_fields:
    - available_quantity: "quantity - reserved_quantity"
    - is_low_stock: "quantity <= min_stock"
  caching:
    - "库存数据缓存 1 分钟"
```

---

## 🎯 US-DRP-004: 库存盘点

**作为** 仓库管理员  
**我希望** 能够定期进行库存盘点  
**以便** 确保账实相符，发现库存差异

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "发起盘点"
    given: "仓库管理员进入盘点管理"
    when: "选择仓库，发起盘点"
    then: "创建盘点单，冻结盘点商品库存"

  - scenario: "录入盘点数量"
    given: "盘点单已创建"
    when: "录入实际盘点数量"
    then: "保存盘点数据"

  - scenario: "提交盘点结果"
    given: "盘点数量已录入"
    when: "提交盘点结果"
    then: "生成差异报告，等待审批"

  - scenario: "盘点审批调整"
    given: "盘点结果已提交"
    when: "管理员审批通过"
    then: "根据盘点结果调整库存"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Stocktaking
    - StocktakingItem
  tables:
    - stocktakings
    - stocktaking_items
  state_machine: "Stocktaking.status"
  states:
    - draft
    - in_progress
    - submitted
    - approved
    - rejected
  implementation:
    - "盘点期间冻结库存变动"
    - "自动计算差异"
    - "审批后自动调整库存"
```

---

## 🎯 US-DRP-005: 库存预警通知

**作为** 采购人员  
**我希望** 能够收到库存预警通知  
**以便** 及时补货，避免缺货

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "低库存预警"
    given: "商品库存低于最低库存"
    when: "系统检测到低库存"
    then: "发送低库存预警通知"

  - scenario: "库存预警设置"
    given: "管理员配置预警规则"
    when: "设置预警阈值和通知人"
    then: "预警规则生效"

  - scenario: "预警通知方式"
    given: "库存触发预警"
    when: "发送通知"
    then: "支持站内消息、邮件、钉钉等通知方式"

  - scenario: "预警处理记录"
    given: "收到预警通知"
    when: "处理预警（创建采购单）"
    then: "记录预警处理状态"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - InventoryAlert
    - AlertRule
  tables:
    - inventory_alerts
    - alert_rules
  alert_types:
    - low_stock
    - out_of_stock
    - overstock
  notifications:
    - LowStockNotification
    - OutOfStockNotification
  scheduling:
    - "定时检查库存状态（每小时）"
```

---

## 🎯 US-DRP-006: 进销存报表

**作为** 管理人员  
**我希望** 能够查看进销存报表  
**以便** 了解库存周转情况，优化库存管理

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "查看进销存汇总"
    given: "管理人员进入报表页面"
    when: "选择时间范围"
    then: "显示期间的采购、销售、库存汇总"

  - scenario: "库存周转率"
    given: "查看库存报表"
    when: "查看周转率指标"
    then: "显示库存周转率和周转天数"

  - scenario: "商品销售排行"
    given: "查看销售报表"
    when: "按销量/销售额排序"
    then: "显示商品销售排行榜"

  - scenario: "报表导出"
    given: "查看报表"
    when: "导出报表"
    then: "导出 Excel/PDF 格式报表"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - InventoryReport
  api_endpoints:
    - GET /api/drp/reports/summary
    - GET /api/drp/reports/turnover
    - GET /api/drp/reports/top-products
  metrics:
    - opening_stock
    - closing_stock
    - total_purchased
    - total_sold
    - turnover_rate
    - turnover_days
  export:
    - Excel (.xlsx)
    - PDF
```

---

## 📊 用户故事汇总

| 故事ID | 优先级 | 复杂度 | 关联实体 |
|--------|--------|--------|---------|
| US-DRP-001 | P0 | 高 | PurchaseOrder, Supplier |
| US-DRP-002 | P0 | 高 | SalesOrder, Inventory |
| US-DRP-003 | P0 | 中 | Inventory, Product, Warehouse |
| US-DRP-004 | P0 | 中 | Stocktaking, StocktakingItem |
| US-DRP-005 | P1 | 中 | InventoryAlert, AlertRule |
| US-DRP-006 | P1 | 中 | InventoryReport |

---

**版本**: v1.0 | **更新日期**: 2026-04-24
