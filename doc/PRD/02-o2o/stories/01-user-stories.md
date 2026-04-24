# 📖 O2O 预约核销模块 - 用户故事

> **L3: 用户故事层级** | **RAG 友好格式** | **可组装到提示词**

---

## 📋 元数据

```yaml
module: "o2o"
document_type: "user_stories"
version: "1.0"
total_stories: 6
```

---

## 🏪 服务浏览

### US-O2O-001: 浏览服务列表

```yaml
story_id: "US-O2O-001"
title: "浏览服务列表"
priority: "P0"
actor: "用户"
module: "o2o"
tags: ["#服务", "#列表", "#门店"]

user_story: |
  作为一名 用户，
  我想要 浏览门店可预约的服务列表，
  以便 我可以选择需要的服务。

acceptance_criteria:
  - scenario: "按门店查看服务"
    given: "用户选择某个门店"
    when: "页面加载"
    then: |
      - 显示该门店的所有可预约服务
      - 每个服务显示：名称、价格、时长、封面图
      - 状态为 active 的服务才显示

  - scenario: "服务详情"
    given: "用户点击某个服务"
    when: "详情页加载"
    then: |
      - 显示服务完整描述
      - 显示价格和时长
      - 显示可预约日期范围
      - 显示"立即预约"按钮

business_rules:
  - "只显示 status=active 的服务"
  - "服务关联到具体门店"

prompt_fragments:
  - "@ProductArchitect"
  - "@FilamentUIDesigner"
```

---

### US-O2O-002: 查看可用时间片

```yaml
story_id: "US-O2O-002"
title: "查看可用时间片"
priority: "P0"
actor: "用户"
module: "o2o"
tags: ["#时间片", "#可用性", "#并发"]

user_story: |
  作为一名 用户，
  我想要 查看某天的可用预约时间段，
  以便 我可以选择合适的时间。

acceptance_criteria:
  - scenario: "查看日期的可用时间片"
    given: "用户选择某个日期"
    when: "页面加载"
    then: |
      - 显示该日期所有时间片
      - 每个时间片显示：开始时间、结束时间、剩余名额
      - 已满的时间片显示"已约满"并禁用
      - 过去的时间片不显示

  - scenario: "时间片已满"
    given: "某时间片 booked_count >= max_capacity"
    when: "页面渲染"
    then: "显示"已约满"，按钮禁用"

business_rules:
  - "只能预约未来 7 天内的时间片"
  - "已过期的时间片不显示"
  - "剩余名额 = max_capacity - booked_count"

prompt_fragments:
  - "@TradeEngineer"
  - "@constraint-o2o-timeslot-locking"
```

---

## 📅 预约流程

### US-O2O-003: 创建预约

```yaml
story_id: "US-O2O-003"
title: "创建预约"
priority: "P0"
actor: "用户"
module: "o2o"
tags: ["#预约", "#创建", "#并发控制"]

user_story: |
  作为一名 用户，
  我想要 预约某个服务的时间片，
  以便 我可以在指定时间到店消费。

acceptance_criteria:
  - scenario: "正常预约"
    given: "用户选择服务和时间片"
    when: "点击确认预约"
    then: |
      - 生成预约单号
      - 创建预约记录（status=pending）
      - 增加时间片 booked_count
      - 生成核销二维码
      - 返回预约详情

  - scenario: "并发预约（时间片已满）"
    given: "多个用户同时预约同一时间片"
    when: "后一个用户提交"
    then: |
      - 提示"该时间段已约满"
      - 使用数据库锁防止超卖

business_rules:
  - "使用 lockForUpdate() 防止并发超卖"
  - "预约单号格式: APT + 年月日 + 6位序列号"
  - "预约成功后需要支付确认"

prompt_fragments:
  - "@TradeEngineer"
  - "@constraint-o2o-timeslot-locking"
  - "@error-handling"
  - "@template-service-layer"
```

---

### US-O2O-004: 取消预约

```yaml
story_id: "US-O2O-004"
title: "取消预约"
priority: "P1"
actor: "用户"
module: "o2o"
tags: ["#预约", "#取消", "#退款"]

user_story: |
  作为一名 用户，
  我想要 取消我的预约，
  以便 在无法到店时释放资源。

acceptance_criteria:
  - scenario: "取消待确认预约"
    given: "预约状态为 pending"
    when: "用户点击取消"
    then: |
      - 更新预约状态为 cancelled
      - 减少时间片 booked_count
      - 如已支付则触发退款

  - scenario: "取消已确认预约"
    given: "预约状态为 confirmed"
    when: "用户在到店前取消"
    then: |
      - 更新预约状态为 cancelled
      - 减少时间片 booked_count
      - 触发退款流程

  - scenario: "已过期无法取消"
    given: "预约时间已过"
    when: "用户尝试取消"
    then: "提示已过期，无法取消"

business_rules:
  - "预约开始前 2 小时可免费取消"
  - "取消后自动减少 booked_count"

prompt_fragments:
  - "@TradeEngineer"
  - "@event-driven"
```

---

## ✅ 核销流程

### US-O2O-005: 扫码核销

```yaml
story_id: "US-O2O-005"
title: "扫码核销"
priority: "P0"
actor: "门店员工"
module: "o2o"
tags: ["#核销", "#二维码", "#门店"]

user_story: |
  作为一名 门店员工，
  我想要 扫描用户的核销二维码，
  以便 确认用户到店并完成服务。

acceptance_criteria:
  - scenario: "正常核销"
    given: "员工扫描有效二维码"
    when: "系统解析预约单号"
    then: |
      - 显示预约详情（服务、时间、用户）
      - 验证预约状态为 confirmed
      - 验证预约时间在有效范围内
      - 更新预约状态为 completed
      - 记录核销时间和核销人
      - 显示核销成功

  - scenario: "预约已核销"
    given: "预约状态已为 completed"
    when: "再次扫描"
    then: "提示已核销，显示核销时间"

  - scenario: "预约已取消"
    given: "预约状态为 cancelled"
    when: "扫描二维码"
    then: "提示预约已取消"

  - scenario: "预约未到时间"
    given: "当前时间早于预约开始时间"
    when: "员工尝试核销"
    then: "提示未到预约时间"

business_rules:
  - "核销需要门店员工权限"
  - "核销后触发 AppointmentCompleted 事件"
  - "二维码包含签名验证防伪"

prompt_fragments:
  - "@TradeEngineer"
  - "@SecurityExpert"
  - "@event-driven"
```

---

### US-O2O-006: 查看预约记录

```yaml
story_id: "US-O2O-006"
title: "查看预约记录"
priority: "P1"
actor: "用户"
module: "o2o"
tags: ["#预约", "#列表", "#状态"]

user_story: |
  作为一名 用户，
  我想要 查看我的预约记录，
  以便 跟踪预约状态。

acceptance_criteria:
  - scenario: "查看预约列表"
    given: "用户进入预约记录页"
    when: "页面加载"
    then: |
      - 显示预约列表（分页）
      - 每条显示：服务名称、门店、时间、状态
      - 按创建时间倒序

  - scenario: "按状态筛选"
    given: "用户点击状态Tab"
    when: "筛选完成"
    then: |
      - 待确认: pending
      - 已确认: confirmed（显示二维码）
      - 已完成: completed
      - 已取消: cancelled

business_rules:
  - "只显示当前用户的预约"
  - "已确认的预约显示核销二维码"

prompt_fragments:
  - "@FilamentUIDesigner"
```

---

## 📊 验收标准汇总

| 故事ID | 标题 | 优先级 | 验收场景数 | 关键约束 |
|--------|------|--------|-----------|---------|
| US-O2O-001 | 浏览服务列表 | P0 | 2 | - |
| US-O2O-002 | 查看可用时间片 | P0 | 2 | 时间片并发 |
| US-O2O-003 | 创建预约 | P0 | 2 | SQL锁防超卖 |
| US-O2O-004 | 取消预约 | P1 | 3 | 退款联动 |
| US-O2O-005 | 扫码核销 | P0 | 4 | 权限+签名 |
| US-O2O-006 | 查看预约记录 | P1 | 2 | - |

---

**版本**: v1.0 | **更新日期**: 2026-04-24
