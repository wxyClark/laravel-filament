# 📖 财务模块 - 用户故事

> **L3: 用户故事层级** | **RAG 友好格式** | **可直接组装到提示词**

---

## 📋 元数据

```yaml
module: "finance"
document_type: "user_stories"
version: "1.0"
total_stories: 6
priority_distribution:
  P0: 4
  P1: 2
```

---

## 🎯 US-FIN-001: 付款审批流程

**作为** 财务人员  
**我希望** 能够管理付款审批流程  
**以便** 确保付款合规，资金安全

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "创建付款单"
    given: "财务人员进入付款管理页面"
    when: "填写收款人、金额、付款方式等信息"
    then: "付款单创建成功，状态为 draft"

  - scenario: "提交付款审批"
    given: "付款单状态为 draft"
    when: "提交审批"
    then: "付款单状态变为 pending，通知审批人"

  - scenario: "审批通过"
    given: "付款单状态为 pending"
    when: "审批人审批通过"
    then: "付款单状态变为 approved"

  - scenario: "审批拒绝"
    given: "付款单状态为 pending"
    when: "审批人审批拒绝并填写原因"
    then: "付款单状态变为 rejected"

  - scenario: "执行付款"
    given: "付款单状态为 approved"
    when: "财务人员确认付款"
    then: "付款单状态变为 paid，扣减账户余额，创建资金流水"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - PaymentOrder
    - BankAccount
  tables:
    - payment_orders
  state_machine: "PaymentOrder.status"
  states:
    - draft
    - pending
    - approved
    - paid
    - rejected
  events:
    - PaymentOrderSubmitted
    - PaymentOrderApproved
    - PaymentOrderRejected
    - PaymentOrderPaid
  notifications:
    - PaymentOrderApprovalNotification
    - PaymentOrderPaidNotification
```

---

## 🎯 US-FIN-002: 发票开具管理

**作为** 财务人员  
**我希望** 能够管理发票开具流程  
**以便** 规范发票管理，满足税务要求

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "创建发票"
    given: "财务人员进入发票管理页面"
    when: "填写发票信息（客户、金额、明细）"
    then: "发票创建成功，状态为 draft"

  - scenario: "提交开票申请"
    given: "发票状态为 draft"
    when: "提交开票"
    then: "发票状态变为 pending"

  - scenario: "开具发票"
    given: "发票状态为 pending"
    when: "财务人员确认开具"
    then: "发票状态变为 issued，记录开票时间"

  - scenario: "作废发票"
    given: "发票状态为 issued"
    when: "在当月作废"
    then: "发票状态变为 cancelled"

  - scenario: "冲红发票"
    given: "发票状态为 issued"
    when: "跨月冲红"
    then: "发票状态变为 void，生成红字发票信息"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Invoice
    - InvoiceItem
  tables:
    - invoices
    - invoice_items
  state_machine: "Invoice.status"
  states:
    - draft
    - pending
    - issued
    - cancelled
    - void
  validation:
    - "当月发票可作废"
    - "跨月发票需冲红"
  events:
    - InvoiceIssued
    - InvoiceCancelled
    - InvoiceVoided
```

---

## 🎯 US-FIN-003: 资金账户管理

**作为** 财务人员  
**我希望** 能够管理多个资金账户  
**以便** 统一管理公司资金

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "创建资金账户"
    given: "财务人员进入账户管理页面"
    when: "填写账户信息（名称、类型、账号、初始余额）"
    then: "账户创建成功"

  - scenario: "查看账户余额"
    given: "资金账户已存在"
    when: "查看账户列表"
    then: "显示各账户当前余额"

  - scenario: "查看资金流水"
    given: "资金账户有交易记录"
    when: "查看账户流水"
    then: "显示所有收支明细"

  - scenario: "账户间转账"
    given: "有两个资金账户"
    when: "执行转账"
    then: "转出账户余额减少，转入账户余额增加"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Account
    - Transaction
  tables:
    - accounts
    - transactions
  account_types:
    - bank
    - alipay
    - wechat
    - cash
  transaction_types:
    - income
    - expense
    - transfer
  implementation:
    - "余额计算使用数据库事务"
    - "流水记录包含余额快照"
```

---

## 🎯 US-FIN-004: 费用报销管理

**作为** 员工  
**我希望** 能够提交费用报销申请  
**以便** 及时获得费用报销

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "提交费用报销"
    given: "员工进入费用报销页面"
    when: "填写费用信息并上传凭证"
    then: "报销单创建成功，状态为 pending"

  - scenario: "审批费用报销"
    given: "报销单状态为 pending"
    when: "审批人审批"
    then: "报销单状态更新为 approved/rejected"

  - scenario: "费用入账"
    given: "报销单状态为 approved"
    when: "财务确认入账"
    then: "生成费用记录，扣减账户余额"

  - scenario: "费用分类统计"
    given: "有多个费用记录"
    when: "查看费用报表"
    then: "按分类、部门、时间统计费用"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Expense
    - ExpenseCategory
  tables:
    - expenses
    - expense_categories
  state_machine: "Expense.status"
  states:
    - draft
    - pending
    - approved
    - rejected
    - paid
  features:
    - "附件上传（发票照片）"
    - "费用分类管理"
    - "审批流程配置"
```

---

## 🎯 US-FIN-005: 财务报表生成

**作为** 管理人员  
**我希望** 能够查看财务报表  
**以便** 了解公司财务状况，做出决策

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "查看利润表"
    given: "管理人员进入报表页面"
    when: "选择利润表"
    then: "显示收入、成本、费用、利润数据"

  - scenario: "查看现金流量表"
    given: "管理人员查看现金流量"
    when: "选择时间范围"
    then: "显示现金流入流出情况"

  - scenario: "应收应付账龄"
    given: "查看账龄分析"
    when: "选择截止日期"
    then: "显示应收应付账龄分布"

  - scenario: "报表导出"
    given: "查看报表"
    when: "导出报表"
    then: "导出 Excel/PDF 格式报表"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - FinancialReport
  api_endpoints:
    - GET /api/finance/reports/profit-loss
    - GET /api/finance/reports/cash-flow
    - GET /api/finance/reports/aging
  metrics:
    - revenue
    - cost_of_goods
    - gross_profit
    - expenses
    - net_profit
    - profit_margin
  export:
    - Excel (.xlsx)
    - PDF
```

---

## 🎯 US-FIN-006: 应收应付管理

**作为** 财务人员  
**我希望** 能够管理应收应付款项  
**以便** 及时催收应收款，安排应付款

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "查看应收账款"
    given: "财务人员进入应收管理页面"
    when: "查看应收账款列表"
    then: "显示所有未结清应收账款"

  - scenario: "应收账款到期提醒"
    given: "应收账款即将到期"
    when: "到达提醒时间"
    then: "发送到期提醒通知"

  - scenario: "应收转已收"
    given: "应收账款状态为 pending"
    when: "确认收款"
    then: "应收账款状态变为 paid，增加账户余额"

  - scenario: "应付账款管理"
    given: "财务人员进入应付管理页面"
    when: "查看应付账款列表"
    then: "显示所有未结清应付账款"

  - scenario: "应付账款付款"
    given: "应付账款状态为 pending"
    when: "执行付款"
    then: "应付账款状态变为 paid，扣减账户余额"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Receivable
    - Payable
  tables:
    - receivables
    - payables
  states:
    - pending
    - partial_paid
    - paid
    - overdue
  notifications:
    - ReceivableDueNotification
    - PayableDueNotification
  aging_buckets:
    - current
    - days_30
    - days_60
    - days_90
    - over_90
```

---

## 📊 用户故事汇总

| 故事ID | 优先级 | 复杂度 | 关联实体 |
|--------|--------|--------|---------|
| US-FIN-001 | P0 | 高 | PaymentOrder, Account |
| US-FIN-002 | P0 | 高 | Invoice, InvoiceItem |
| US-FIN-003 | P0 | 中 | Account, Transaction |
| US-FIN-004 | P0 | 中 | Expense, ExpenseCategory |
| US-FIN-005 | P1 | 高 | FinancialReport |
| US-FIN-006 | P1 | 中 | Receivable, Payable |

---

**版本**: v1.0 | **更新日期**: 2026-04-24
