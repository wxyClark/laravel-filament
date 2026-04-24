# 📡 财务模块 - API 接口契约

> **L4: 需求碎片层级** | **RAG 友好格式** | **可直接组装到提示词**

---

## 📋 元数据

```yaml
module: "finance"
document_type: "api_contracts"
version: "1.0"
total_endpoints: 18
auth_required: true
```

---

## 💸 付款管理 API

### 创建付款单

```yaml
endpoint: POST /api/finance/payment-orders
description: "创建付款单"
auth: "sanctum"
permission: "finance.payment-orders.create"

request:
  body:
    payee_type: "string|required|in:supplier,employee,partner"
    payee_id: "integer|required"
    amount: "decimal|required|min:0.01"
    payment_method: "string|required|in:bank_transfer,alipay,wechat,cash,check"
    bank_account_id: "integer|required|exists:bank_accounts,id"
    purpose: "string|required|max:500"
    reference_no: "string|nullable|max:100"
    notes: "string|nullable|max:500"
    attachments: "array|nullable"
    attachments.*: "file|mimes:pdf,jpg,png|max:5120"

response:
  success:
    code: 201
    data:
      id: "integer"
      order_no: "string"
      payee: "object"
      amount: "decimal"
      payment_method: "string"
      status: "string"
      created_at: "datetime"

errors:
  - code: 422
    message: "验证失败"
  - code: 400
    message: "账户余额不足"
```

### 获取付款单列表

```yaml
endpoint: GET /api/finance/payment-orders
description: "获取付款单列表"
auth: "sanctum"
permission: "finance.payment-orders.view"

request:
  query:
    status: "string|nullable|in:draft,pending,approved,paid,rejected"
    payee_type: "string|nullable|in:supplier,employee,partner"
    payment_method: "string|nullable|in:bank_transfer,alipay,wechat,cash,check"
    start_date: "date|nullable"
    end_date: "date|nullable|after_or_equal:start_date"
    min_amount: "decimal|nullable|min:0"
    max_amount: "decimal|nullable|min:0"
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
          payee: "object"
          amount: "decimal"
          payment_method: "string"
          status: "string"
          created_at: "datetime"
      meta:
        current_page: "integer"
        per_page: "integer"
        total: "integer"
```

### 提交付款审批

```yaml
endpoint: POST /api/finance/payment-orders/{id}/submit
description: "提交付款审批"
auth: "sanctum"
permission: "finance.payment-orders.submit"

request:
  params:
    id: "integer|required|exists:payment_orders,id"

response:
  success:
    code: 200
    data:
      id: "integer"
      status: "string"
      submitted_at: "datetime"
      message: "string"

errors:
  - code: 400
    message: "付款单状态不允许提交"
```

### 审批付款单

```yaml
endpoint: POST /api/finance/payment-orders/{id}/approve
description: "审批付款单"
auth: "sanctum"
permission: "finance.payment-orders.approve"

request:
  params:
    id: "integer|required|exists:payment_orders,id"
  body:
    action: "string|required|in:approve,reject"
    remark: "string|required_if:action,reject|max:500"

response:
  success:
    code: 200
    data:
      id: "integer"
      status: "string"
      approved_by: "object"
      approved_at: "datetime"
      message: "string"

errors:
  - code: 400
    message: "付款单状态不允许审批"
  - code: 403
    message: "无审批权限"
```

### 执行付款

```yaml
endpoint: POST /api/finance/payment-orders/{id}/pay
description: "执行付款"
auth: "sanctum"
permission: "finance.payment-orders.pay"

request:
  params:
    id: "integer|required|exists:payment_orders,id"
  body:
    transaction_no: "string|required|max:100"
    paid_at: "datetime|required"
    notes: "string|nullable|max:500"

response:
  success:
    code: 200
    data:
      id: "integer"
      status: "string"
      paid_at: "datetime"
      transaction_no: "string"
      balance_after: "decimal"
      cashflow_created: "boolean"

errors:
  - code: 400
    message: "付款单状态不允许付款"
  - code: 400
    message: "账户余额不足"
```

---

## 🧾 发票管理 API

### 创建发票

```yaml
endpoint: POST /api/finance/invoices
description: "创建发票"
auth: "sanctum"
permission: "finance.invoices.create"

request:
  body:
    type: "string|required|in:sales,purchase"
    customer_supplier_id: "integer|required"
    invoice_no: "string|nullable|max:50"
    invoice_date: "date|required"
    due_date: "date|required|after_or_equal:invoice_date"
    tax_rate: "decimal|required|min:0|max:100"
    items:
      type: "array|required|min:1"
      items:
        description: "string|required|max:500"
        quantity: "decimal|required|min:0.01"
        unit_price: "decimal|required|min:0"
        tax_rate: "decimal|nullable|min:0|max:100"
    notes: "string|nullable|max:500"

response:
  success:
    code: 201
    data:
      id: "integer"
      invoice_no: "string"
      type: "string"
      customer_supplier: "object"
      subtotal: "decimal"
      tax_amount: "decimal"
      total_amount: "decimal"
      status: "string"
      created_at: "datetime"
```

### 获取发票列表

```yaml
endpoint: GET /api/finance/invoices
description: "获取发票列表"
auth: "sanctum"
permission: "finance.invoices.view"

request:
  query:
    type: "string|nullable|in:sales,purchase"
    status: "string|nullable|in:draft,pending,issued,cancelled,void"
    start_date: "date|nullable"
    end_date: "date|nullable|after_or_equal:start_date"
    min_amount: "decimal|nullable|min:0"
    max_amount: "decimal|nullable|min:0"
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
          invoice_no: "string"
          type: "string"
          customer_supplier: "object"
          total_amount: "decimal"
          status: "string"
          invoice_date: "date"
          due_date: "date"
```

### 开具发票

```yaml
endpoint: POST /api/finance/invoices/{id}/issue
description: "开具发票"
auth: "sanctum"
permission: "finance.invoices.issue"

request:
  params:
    id: "integer|required|exists:invoices,id"
  body:
    invoice_no: "string|required|max:50"
    issued_at: "datetime|required"

response:
  success:
    code: 200
    data:
      id: "integer"
      invoice_no: "string"
      status: "string"
      issued_at: "datetime"

errors:
  - code: 400
    message: "发票状态不允许开具"
```

### 作废/冲红发票

```yaml
endpoint: POST /api/finance/invoices/{id}/void
description: "作废或冲红发票"
auth: "sanctum"
permission: "finance.invoices.void"

request:
  params:
    id: "integer|required|exists:invoices,id"
  body:
    void_type: "string|required|in:cancel,void"
    reason: "string|required|max:500"

response:
  success:
    code: 200
    data:
      id: "integer"
      status: "string"
      voided_at: "datetime"
      void_reason: "string"

errors:
  - code: 400
    message: "发票状态不允许作废"
  - code: 400
    message: "已超过作废期限"
```

---

## 💰 账户管理 API

### 获取账户列表

```yaml
endpoint: GET /api/finance/accounts
description: "获取资金账户列表"
auth: "sanctum"
permission: "finance.accounts.view"

request:
  query:
    type: "string|nullable|in:bank,alipay,wechat,cash"
    is_active: "boolean|nullable"

response:
  success:
    code: 200
    data:
      type: "array"
      items:
        - id: "integer"
          name: "string"
          type: "string"
          account_no: "string"
          balance: "decimal"
          currency: "string"
          is_active: "boolean"
```

### 账户资金流水

```yaml
endpoint: GET /api/finance/accounts/{id}/transactions
description: "获取账户资金流水"
auth: "sanctum"
permission: "finance.accounts.transactions"

request:
  params:
    id: "integer|required|exists:accounts,id"
  query:
    type: "string|nullable|in:income,expense,transfer"
    start_date: "date|nullable"
    end_date: "date|nullable|after_or_equal:start_date"
    min_amount: "decimal|nullable|min:0"
    max_amount: "decimal|nullable|min:0"
    per_page: "integer|nullable|min:1|max:100"
    page: "integer|nullable|min:1"

response:
  success:
    code: 200
    data:
      type: "paginator"
      items:
        - id: "integer"
          type: "string"
          amount: "decimal"
          balance_before: "decimal"
          balance_after: "decimal"
          description: "string"
          reference_type: "string"
          reference_id: "integer"
          created_at: "datetime"
```

---

## 📊 费用管理 API

### 创建费用单

```yaml
endpoint: POST /api/finance/expenses
description: "创建费用单"
auth: "sanctum"
permission: "finance.expenses.create"

request:
  body:
    category_id: "integer|required|exists:expense_categories,id"
    amount: "decimal|required|min:0.01"
    expense_date: "date|required"
    description: "string|required|max:500"
    account_id: "integer|required|exists:accounts,id"
    attachments: "array|nullable"
    attachments.*: "file|mimes:pdf,jpg,png|max:5120"

response:
  success:
    code: 201
    data:
      id: "integer"
      expense_no: "string"
      category: "object"
      amount: "decimal"
      expense_date: "date"
      status: "string"
      created_at: "datetime"
```

### 获取费用列表

```yaml
endpoint: GET /api/finance/expenses
description: "获取费用列表"
auth: "sanctum"
permission: "finance.expenses.view"

request:
  query:
    category_id: "integer|nullable|exists:expense_categories,id"
    status: "string|nullable|in:draft,pending,approved,rejected"
    start_date: "date|nullable"
    end_date: "date|nullable|after_or_equal:start_date"
    min_amount: "decimal|nullable|min:0"
    max_amount: "decimal|nullable|min:0"
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
          expense_no: "string"
          category: "object"
          amount: "decimal"
          expense_date: "date"
          status: "string"
          created_by: "object"
```

---

## 📈 财务报表 API

### 利润表

```yaml
endpoint: GET /api/finance/reports/profit-loss
description: "获取利润表"
auth: "sanctum"
permission: "finance.reports.view"

request:
  query:
    start_date: "date|required"
    end_date: "date|required|after_or_equal:start_date"
    group_by: "string|nullable|in:day,week,month,quarter,year"

response:
  success:
    code: 200
    data:
      period: "string"
      revenue: "object"
        sales: "decimal"
        service: "decimal"
        other: "decimal"
        total: "decimal"
      cost_of_goods: "decimal"
      gross_profit: "decimal"
      expenses: "object"
        salary: "decimal"
        rent: "decimal"
        marketing: "decimal"
        other: "decimal"
        total: "decimal"
      net_profit: "decimal"
      profit_margin: "decimal"
```

### 现金流量表

```yaml
endpoint: GET /api/finance/reports/cash-flow
description: "获取现金流量表"
auth: "sanctum"
permission: "finance.reports.view"

request:
  query:
    account_id: "integer|nullable|exists:accounts,id"
    start_date: "date|required"
    end_date: "date|required|after_or_equal:start_date"

response:
  success:
    code: 200
    data:
      opening_balance: "decimal"
      inflows:
        type: "array"
        items:
          - category: "string"
            amount: "decimal"
        total: "decimal"
      outflows:
        type: "array"
        items:
          - category: "string"
            amount: "decimal"
        total: "decimal"
      net_cash_flow: "decimal"
      closing_balance: "decimal"
```

### 应收应付账龄分析

```yaml
endpoint: GET /api/finance/reports/aging
description: "应收应付账龄分析"
auth: "sanctum"
permission: "finance.reports.view"

request:
  query:
    type: "string|required|in:receivable,payable"
    as_of_date: "date|required"

response:
  success:
    code: 200
    data:
      type: "array"
      items:
        - party: "object"
          current: "decimal"
          days_30: "decimal"
          days_60: "decimal"
          days_90: "decimal"
          over_90: "decimal"
          total: "decimal"
```

---

## 📊 API 汇总

| 模块 | 端点数 | 认证 | 权限前缀 |
|------|--------|------|---------|
| 付款管理 | 5 | required | finance.payment-orders.* |
| 发票管理 | 4 | required | finance.invoices.* |
| 账户管理 | 2 | required | finance.accounts.* |
| 费用管理 | 2 | required | finance.expenses.* |
| 财务报表 | 3 | required | finance.reports.* |

---

**版本**: v1.0 | **更新日期**: 2026-04-24
