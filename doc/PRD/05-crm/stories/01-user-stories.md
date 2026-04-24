# 📖 CRM 客户模块 - 用户故事

> **L3: 用户故事层级** | **RAG 友好格式** | **可直接组装到提示词**

---

## 📋 元数据

```yaml
module: "crm"
document_type: "user_stories"
version: "1.0"
total_stories: 6
priority_distribution:
  P0: 4
  P1: 2
```

---

## 🎯 US-CRM-001: 客户信息管理

**作为** 销售人员  
**我希望** 能够录入和管理客户信息  
**以便** 全面了解客户，提供个性化服务

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "创建客户"
    given: "销售人员进入客户管理页面"
    when: "填写客户基本信息"
    then: "客户创建成功，显示在客户列表"

  - scenario: "编辑客户信息"
    given: "客户已存在"
    when: "修改客户信息"
    then: "客户信息更新成功"

  - scenario: "客户标签管理"
    given: "客户已存在"
    when: "添加/移除标签"
    then: "客户标签更新成功"

  - scenario: "客户合并"
    given: "发现重复客户"
    when: "合并两个客户"
    then: "数据合并，删除重复记录"

  - scenario: "客户搜索"
    given: "客户列表中有多个客户"
    when: "搜索客户名称/电话/公司"
    then: "显示匹配的客户列表"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Customer
    - CustomerTag
  tables:
    - customers
    - customer_taggables
  fields:
    - name
    - company
    - phone
    - email
    - address
    - source
    - level
    - assigned_to (销售员)
  filament_resource: "CustomerResource"
```

---

## 🎯 US-CRM-002: 客户跟进记录

**作为** 销售人员  
**我希望** 能够记录客户跟进情况  
**以便** 追踪销售进度，避免遗漏

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "创建跟进记录"
    given: "销售人员查看客户详情"
    when: "记录跟进内容和下次跟进时间"
    then: "跟进记录保存成功"

  - scenario: "查看跟进历史"
    given: "客户有多条跟进记录"
    when: "查看跟进历史"
    then: "按时间倒序显示所有跟进记录"

  - scenario: "跟进提醒"
    given: "设置了下次跟进时间"
    when: "到达跟进时间"
    then: "销售人员收到跟进提醒"

  - scenario: "跟进类型记录"
    given: "记录跟进"
    when: "选择跟进类型（电话、拜访、邮件等）"
    then: "跟进类型正确记录"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Customer
    - FollowUp
  tables:
    - follow_ups
  fields:
    - customer_id
    - type (call, visit, email, wechat, other)
    - content
    - next_follow_up_at
    - created_by
  notifications:
    - FollowUpReminderNotification
  scheduling:
    - "检查下次跟进时间的定时任务"
```

---

## 🎯 US-CRM-003: 商机管理

**作为** 销售人员  
**我希望** 能够管理销售商机  
**以便** 跟踪潜在订单，提高转化率

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "创建商机"
    given: "销售人员发现潜在客户"
    when: "创建商机并填写信息"
    then: "商机创建成功，关联客户"

  - scenario: "商机阶段推进"
    given: "商机处于某个阶段"
    when: "推进到下一阶段"
    then: "商机阶段更新，记录变更时间"

  - scenario: "商机转订单"
    given: "商机处于成交阶段"
    when: "将商机转为订单"
    then: "创建订单，关联商机"

  - scenario: "商机丢失"
    given: "商机未能成交"
    when: "标记为丢失并填写原因"
    then: "商机状态更新为 lost"

  - scenario: "商机预测"
    given: "查看商机列表"
    when: "查看销售预测"
    then: "显示预计成交金额和概率"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Opportunity
    - OpportunityStage
  tables:
    - opportunities
    - opportunity_stages
  states:
    - new
    - qualified
    - proposal
    - negotiation
    - won
    - lost
  fields:
    - customer_id
    - name
    - value
    - probability
    - stage
    - expected_close_date
    - assigned_to
```

---

## 🎯 US-CRM-004: 客户分群与标签

**作为** 营销人员  
**我希望** 能够对客户进行分群和打标签  
**以便** 实现精准营销

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "创建客户标签"
    given: "管理员进入标签管理"
    when: "创建标签并设置颜色"
    then: "标签创建成功"

  - scenario: "批量打标签"
    given: "选择多个客户"
    when: "批量添加标签"
    then: "所有选中客户添加标签"

  - scenario: "按标签筛选客户"
    given: "客户有不同的标签"
    when: "按标签筛选"
    then: "显示符合条件的客户列表"

  - scenario: "客户分群规则"
    given: "设置分群规则（如消费金额 > 10000）"
    when: "执行分群"
    then: "自动将符合条件的客户分入群组"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Customer
    - Tag
    - CustomerGroup
  tables:
    - tags
    - taggables
    - customer_groups
    - customer_group_customers
  rules:
    - "消费金额阈值"
    - "最近购买时间"
    - "购买频次"
    - "客户等级"
```

---

## 🎯 US-CRM-005: 客户积分管理

**作为** 运营人员  
**我希望** 能够管理客户积分  
**以便** 提高客户忠诚度和复购率

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "消费获得积分"
    given: "客户完成订单"
    when: "订单支付成功"
    then: "自动增加积分（订单金额 × 积分比例）"

  - scenario: "积分兑换"
    given: "客户有可用积分"
    when: "兑换商品或优惠券"
    then: "扣减积分，发放奖励"

  - scenario: "积分过期"
    given: "积分超过有效期"
    when: "到达过期时间"
    then: "自动过期积分"

  - scenario: "积分明细"
    given: "客户查看积分"
    when: "查看积分明细"
    then: "显示积分获取和消费记录"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Customer
    - PointTransaction
  tables:
    - point_transactions
  fields:
    - customer_id
    - type (earn, spend, expire, adjust)
    - points
    - balance_before
    - balance_after
    - reference_type
    - reference_id
    - description
  configuration:
    earn_rate: "1 元 = 1 积分"
    expire_days: 365
```

---

## 🎯 US-CRM-006: 客户沟通记录

**作为** 客服人员  
**我希望** 能够记录与客户的沟通内容  
**以便** 保持沟通连续性，提升服务质量

### 验收标准

```yaml
acceptance_criteria:
  - scenario: "记录沟通内容"
    given: "客服与客户沟通后"
    when: "记录沟通摘要"
    then: "沟通记录保存成功"

  - scenario: "查看沟通历史"
    given: "客户有多次沟通记录"
    when: "查看沟通历史"
    then: "按时间倒序显示所有沟通记录"

  - scenario: "沟通记录关联"
    given: "沟通涉及订单"
    when: "记录沟通时关联订单"
    then: "沟通记录关联订单，可从订单查看"

  - scenario: "内部备注"
    given: "客服需要记录内部备注"
    when: "添加内部备注"
    then: "备注仅内部可见，客户不可见"
```

### 技术实现要点

```yaml
technical_notes:
  entities:
    - Customer
    - Communication
  tables:
    - communications
  fields:
    - customer_id
    - type (phone, email, wechat, meeting, other)
    - subject
    - content
    - is_internal_note
    - related_type (订单、工单等)
    - related_id
    - created_by
```

---

## 📊 用户故事汇总

| 故事ID | 优先级 | 复杂度 | 关联实体 |
|--------|--------|--------|---------|
| US-CRM-001 | P0 | 中 | Customer, CustomerTag |
| US-CRM-002 | P0 | 中 | Customer, FollowUp |
| US-CRM-003 | P0 | 高 | Opportunity, Customer |
| US-CRM-004 | P1 | 中 | Customer, Tag, CustomerGroup |
| US-CRM-005 | P1 | 高 | Customer, PointTransaction |
| US-CRM-006 | P0 | 低 | Customer, Communication |

---

**版本**: v1.0 | **更新日期**: 2026-04-24
