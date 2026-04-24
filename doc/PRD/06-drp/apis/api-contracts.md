# 📡 DRP 进销存模块 - API 接口契约

> **L4: 需求碎片层级** | **RAG 友好格式** | **可直接组装到提示词**

---

## 📋 元数据

```yaml
module: "drp"
document_type: "api_contracts"
version: "1.0"
total_endpoints: 15
auth_required: true
```

---

## 📦 采购管理 API

### 创建采购单

```yaml
endpoint: POST /api/drp/purchase-orders
description: "创建采购单"
auth: "sanctum"
permission: "drp.purchase-orders.create"

request:
  body:
    supplier_id: "integer|required|exists:suppliers,id"
    warehouse_id: "integer|required|exists:warehouses,id"
    expected_delivery_date: "date|required|after:today"
    notes: "string|nullable|max:500"
    items:
      type: "array|required|min:1"
      items:
        product_id: "integer|required|exists:products,id"
        quantity: "integer|required|min:1"
        unit_price: "decimal|required|min:0"
        notes: "string|nullable"

response:
  success:
    code: 201
    data:
      id: "integer"
      order_no: "string"
      supplier: "object"
      warehouse: "object"
      status: "string"
      total_amount: "decimal"
      items: "array"
      created_at: "datetime"

errors:
  - code: 422
    message: "验证失败"
  - code: 403
    message: "无权限"
```

### 获取采购单列表

```yaml
endpoint: GET /api/drp/purchase-orders
description: "获取采购单列表"
auth: "sanctum"
permission: "drp.purchase-orders.view"

request:
  query:
    status: "string|nullable|in:draft,pending,received,cancelled"
    supplier_id: "integer|nullable|exists:suppliers,id"
    warehouse_id: "integer|nullable|exists:warehouses,id"
    start_date: "date|nullable"
    end_date: "date|nullable|after_or_equal:start_date"
    search: "string|nullable"
    per_page: "integer|nullable|min:1|max:100"
    page: "integer|nullable|min:1"

response:
  success:
    code: 200
    data:
      type: "paginator"
      items:
        - id: "integer"
          order_no: "string"
          supplier: "object"
          warehouse: "object"
          status: "string"
          total_amount: "decimal"
          created_at: "datetime"
      meta:
        current_page: "integer"
        per_page: "integer"
        total: "integer"
```

### 收货入库

```yaml
endpoint: POST /api/drp/purchase-orders/{id}/receive
description: "采购单收货入库"
auth: "sanctum"
permission: "drp.purchase-orders.receive"

request:
  params:
    id: "integer|required|exists:purchase_orders,id"
  body:
    items:
      type: "array|required"
      items:
        item_id: "integer|required|exists:purchase_order_items,id"
        received_quantity: "integer|required|min:1"
        notes: "string|nullable"

response:
  success:
    code: 200
    data:
      id: "integer"
      status: "string"
      received_at: "datetime"
      inventory_updated: "boolean"
      message: "string"

errors:
  - code: 422
    message: "收货数量超过采购数量"
  - code: 400
    message: "采购单状态不允许收货"
```

---

## 📤 销售出库 API

### 创建销售单

```yaml
endpoint: POST /api/drp/sales-orders
description: "创建销售单"
auth: "sanctum"
permission: "drp.sales-orders.create"

request:
  body:
    customer_name: "string|required|max:255"
    customer_phone: "string|required|max:20"
    customer_address: "string|required|max:500"
    warehouse_id: "integer|required|exists:warehouses,id"
    expected_delivery_date: "date|required|after:today"
    notes: "string|nullable|max:500"
    items:
      type: "array|required|min:1"
      items:
        product_id: "integer|required|exists:products,id"
        quantity: "integer|required|min:1"
        unit_price: "decimal|required|min:0"

response:
  success:
    code: 201
    data:
      id: "integer"
      order_no: "string"
      customer: "object"
      warehouse: "object"
      status: "string"
      total_amount: "decimal"
      items: "array"
      created_at: "datetime"
```

### 出库发货

```yaml
endpoint: POST /api/drp/sales-orders/{id}/ship
description: "销售单出库发货"
auth: "sanctum"
permission: "drp.sales-orders.ship"

request:
  params:
    id: "integer|required|exists:sales_orders,id"
  body:
    tracking_no: "string|nullable|max:100"
    shipping_company: "string|nullable|max:100"
    notes: "string|nullable|max:500"

response:
  success:
    code: 200
    data:
      id: "integer"
      status: "string"
      shipped_at: "datetime"
      tracking_no: "string"
      inventory_updated: "boolean"

errors:
  - code: 400
    message: "库存不足"
  - code: 400
    message: "销售单状态不允许出库"
```

---

## 📊 库存管理 API

### 库存查询

```yaml
endpoint: GET /api/drp/inventory
description: "查询库存列表"
auth: "sanctum"
permission: "drp.inventory.view"

request:
  query:
    warehouse_id: "integer|nullable|exists:warehouses,id"
    product_id: "integer|nullable|exists:products,id"
    category_id: "integer|nullable|exists:categories,id"
    low_stock: "boolean|nullable"
    search: "string|nullable"
    per_page: "integer|nullable|min:1|max:100"
    page: "integer|nullable|min:1"

response:
  success:
    code: 200
    data:
      type: "paginator"
      items:
        - id: "integer"
          product: "object"
          warehouse: "object"
          quantity: "integer"
          reserved_quantity: "integer"
          available_quantity: "integer"
          min_stock: "integer"
          max_stock: "integer"
          is_low_stock: "boolean"
          last_in_at: "datetime"
          last_out_at: "datetime"
```

### 库存调整

```yaml
endpoint: POST /api/drp/inventory/{id}/adjust
description: "库存调整"
auth: "sanctum"
permission: "drp.inventory.adjust"

request:
  params:
    id: "integer|required|exists:inventory,id"
  body:
    adjustment_type: "string|required|in:in,out,correction"
    quantity: "integer|required"
    reason: "string|required|max:500"
    reference_no: "string|nullable|max:100"

response:
  success:
    code: 200
    data:
      id: "integer"
      previous_quantity: "integer"
      new_quantity: "integer"
      adjustment: "integer"
      adjusted_at: "datetime"
      adjusted_by: "object"

errors:
  - code: 422
    message: "调整后库存不能为负数"
  - code: 403
    message: "无权限调整库存"
```

### 库存盘点

```yaml
endpoint: POST /api/drp/inventory/stocktaking
description: "发起库存盘点"
auth: "sanctum"
permission: "drp.inventory.stocktaking"

request:
  body:
    warehouse_id: "integer|required|exists:warehouses,id"
    product_ids: "array|nullable"
    notes: "string|nullable|max:500"

response:
  success:
    code: 201
    data:
      id: "integer"
      stocktaking_no: "string"
      warehouse: "object"
      status: "string"
      items_count: "integer"
      created_at: "datetime"
```

### 提交盘点结果

```yaml
endpoint: POST /api/drp/inventory/stocktaking/{id}/submit
description: "提交盘点结果"
auth: "sanctum"
permission: "drp.inventory.stocktaking.submit"

request:
  params:
    id: "integer|required|exists:stocktakings,id"
  body:
    items:
      type: "array|required"
      items:
        inventory_id: "integer|required"
        actual_quantity: "integer|required|min:0"
        notes: "string|nullable"

response:
  success:
    code: 200
    data:
      id: "integer"
      status: "string"
      submitted_at: "datetime"
      variance_summary: "object"
```

---

## 📈 报表 API

### 进销存汇总报表

```yaml
endpoint: GET /api/drp/reports/summary
description: "获取进销存汇总报表"
auth: "sanctum"
permission: "drp.reports.view"

request:
  query:
    warehouse_id: "integer|nullable|exists:warehouses,id"
    start_date: "date|required"
    end_date: "date|required|after_or_equal:start_date"
    group_by: "string|nullable|in:day,week,month"

response:
  success:
    code: 200
    data:
      period: "string"
      opening_stock: "integer"
      total_purchased: "integer"
      total_sold: "integer"
      total_adjusted: "integer"
      closing_stock: "integer"
      purchase_amount: "decimal"
      sales_amount: "decimal"
      profit: "decimal"
```

### 库存预警报表

```yaml
endpoint: GET /api/drp/reports/low-stock
description: "获取库存预警报表"
auth: "sanctum"
permission: "drp.reports.view"

request:
  query:
    warehouse_id: "integer|nullable|exists:warehouses,id"
    category_id: "integer|nullable|exists:categories,id"

response:
  success:
    code: 200
    data:
      type: "array"
      items:
        - product_id: "integer"
          product_name: "string"
          warehouse: "object"
          current_stock: "integer"
          min_stock: "integer"
          deficit: "integer"
          last_purchase_date: "datetime"
          avg_daily_sales: "decimal"
          suggested_reorder_qty: "integer"
```

---

## 📊 API 汇总

| 模块 | 端点数 | 认证 | 权限前缀 |
|------|--------|------|---------|
| 采购管理 | 3 | required | drp.purchase-orders.* |
| 销售出库 | 2 | required | drp.sales-orders.* |
| 库存管理 | 4 | required | drp.inventory.* |
| 报表 | 2 | required | drp.reports.* |

---

**版本**: v1.0 | **更新日期**: 2026-04-24
