# 📖 电商核心模块 - 用户故事

> **L3: 用户故事层级** | **RAG 友好格式** | **可组装到提示词**

---

## 📋 元数据

```yaml
module: "ecommerce"
document_type: "user_stories"
version: "1.0"
total_stories: 8
priority_distribution: { P0: 6, P1: 2 }
```

---

## 🛒 商品浏览

### US-EC-001: 浏览商品列表

```yaml
story_id: "US-EC-001"
title: "浏览商品列表"
priority: "P0"
actor: "游客/用户"
module: "ecommerce"
tags: ["#商品", "#列表", "#分页"]

user_story: |
  作为一名 游客/用户，
  我想要 浏览商品列表，
  以便 我可以找到感兴趣的商品。

acceptance_criteria:
  - scenario: "正常浏览商品列表"
    given: "用户访问商品列表页"
    when: "页面加载完成"
    then: |
      - 显示商品列表，每页默认15条
      - 每个商品显示：主图、名称、价格、销量
      - 支持按分类筛选
      - 支持按价格排序
      - 支持关键词搜索

  - scenario: "空数据处理"
    given: "当前分类下无商品"
    when: "用户访问列表页"
    then: "显示空状态提示"

  - scenario: "分页加载"
    given: "商品总数超过一页"
    when: "用户滚动到底部或点击下一页"
    then: "加载下一页数据"

business_rules:
  - "商品状态为 active 才可显示"
  - "价格显示为促销价（如有）"
  - "销量按30天内订单统计"

technical_notes:
  - "使用 Eloquent 的 paginate()"
  - "分类筛选使用 whereHas"
  - "搜索使用 LIKE 或全文索引"

prompt_fragments:
  - "@ProductArchitect"
  - "@type-safety-immutability"
  - "@template-filament-resource"
```

---

### US-EC-002: 查看商品详情

```yaml
story_id: "US-EC-002"
title: "查看商品详情"
priority: "P0"
actor: "游客/用户"
module: "ecommerce"
tags: ["#商品", "#详情", "#SKU"]

user_story: |
  作为一名 游客/用户，
  我想要 查看商品详情，
  以便 我可以了解商品的完整信息并选择规格。

acceptance_criteria:
  - scenario: "查看商品详情"
    given: "用户点击商品"
    when: "详情页加载完成"
    then: |
      - 显示商品图片轮播
      - 显示商品名称、价格区间
      - 显示商品描述
      - 显示SKU选择器
      - 显示库存状态

  - scenario: "选择SKU"
    given: "用户选择规格组合"
    when: "选择完成"
    then: |
      - 更新显示价格
      - 更新显示库存
      - 更新加入购物车按钮状态

  - scenario: "库存不足"
    given: "选中SKU库存为0"
    when: "页面渲染"
    then: "显示"已售罄"，禁用加入购物车按钮"

business_rules:
  - "价格显示最低SKU价格到最高SKU价格区间"
  - "库存为各SKU库存之和"
  - "商品浏览量自增"

technical_notes:
  - "使用 with(['skus', 'category', 'brand']) 预加载"
  - "图片使用 JSON 字段存储"
  - "SKU规格使用 JSON 字段存储"

prompt_fragments:
  - "@ProductArchitect"
  - "@template-filament-resource"
```

---

## 🛒 购物车

### US-EC-003: 添加商品到购物车

```yaml
story_id: "US-EC-003"
title: "添加商品到购物车"
priority: "P0"
actor: "登录用户"
module: "ecommerce"
tags: ["#购物车", "#添加", "#库存校验"]

user_story: |
  作为一名 登录用户，
  我想要 将商品添加到购物车，
  以便 我可以稍后统一结算。

acceptance_criteria:
  - scenario: "首次添加商品"
    given: "用户选择SKU和数量"
    when: "点击加入购物车"
    then: |
      - 创建购物车记录
      - 数量为用户选择的数量
      - 默认选中状态
      - 返回成功提示

  - scenario: "重复添加相同商品"
    given: "购物车已有该SKU"
    when: "再次添加"
    then: |
      - 增加数量（合并）
      - 返回成功提示

  - scenario: "库存不足"
    given: "添加数量超过可用库存"
    when: "点击加入购物车"
    then: "提示库存不足，显示最大可添加数量"

  - scenario: "购物车数量上限"
    given: "购物车商品总数超过100件"
    when: "继续添加"
    then: "提示购物车已满"

business_rules:
  - "每个用户最多100个购物车条目"
  - "同一SKU自动合并数量"
  - "库存检查使用 available_quantity"

technical_notes:
  - "使用 unique 约束防止重复"
  - "库存检查使用事务"
  - "返回更新后的购物车总数"

prompt_fragments:
  - "@TradeEngineer"
  - "@type-safety-immutability"
  - "@error-handling"
```

---

## 📦 订单流程

### US-EC-004: 提交订单

```yaml
story_id: "US-EC-004"
title: "提交订单"
priority: "P0"
actor: "登录用户"
module: "ecommerce"
tags: ["#订单", "#创建", "#核心流程"]

user_story: |
  作为一名 登录用户，
  我想要 从购物车提交订单，
  以便 我可以购买选中的商品。

acceptance_criteria:
  - scenario: "正常提交订单"
    given: "用户购物车有选中商品"
    when: "点击提交订单"
    then: |
      - 生成唯一订单号
      - 创建订单记录（status=pending）
      - 创建订单明细
      - 锁定库存（locked_quantity += quantity）
      - 清空已选中的购物车商品
      - 返回订单信息和支付参数

  - scenario: "库存不足"
    given: "某商品库存不足"
    when: "提交订单"
    then: "提示哪个商品库存不足，阻止提交"

  - scenario: "商品价格变更"
    given: "商品价格在加入购物车后发生变化"
    when: "提交订单"
    then: "提示价格已更新，显示最新价格"

business_rules:
  - "订单金额 = 商品金额 - 优惠金额 + 运费"
  - "商品金额 = Σ(SKU价格 × 数量)"
  - "订单号格式: ORD + 年月日 + 6位序列号"
  - "库存锁定使用悲观锁"

technical_notes:
  - "使用 DB::transaction 保证原子性"
  - "订单号使用 Redis 原子递增"
  - "触发 OrderCreated 事件"

prompt_fragments:
  - "@TradeEngineer"
  - "@type-safety-immutability"
  - "@dependency-injection"
  - "@error-handling"
  - "@event-driven"
  - "@constraint-inventory-concurrency"
  - "@template-service-layer"
```

---

### US-EC-005: 支付订单

```yaml
story_id: "US-EC-005"
title: "支付订单"
priority: "P0"
actor: "登录用户"
module: "ecommerce"
tags: ["#支付", "#微信", "#支付宝", "#回调"]

user_story: |
  作为一名 登录用户，
  我想要 选择支付方式完成支付，
  以便 我可以完成购买。

acceptance_criteria:
  - scenario: "发起微信支付"
    given: "用户选择微信支付"
    when: "点击确认支付"
    then: |
      - 调用微信支付API
      - 返回支付参数（appId, timeStamp, nonceStr, package, signType, paySign）
      - 前端唤起微信支付

  - scenario: "发起支付宝支付"
    given: "用户选择支付宝支付"
    when: "点击确认支付"
    then: |
      - 调用支付宝API
      - 返回支付表单HTML或支付URL
      - 前端跳转到支付宝页面

  - scenario: "支付成功回调"
    given: "支付网关发送回调"
    when: "系统收到回调"
    then: |
      - 验证回调签名
      - 更新订单状态为 paid
      - 记录支付时间 paid_at
      - 扣减库存（quantity -= locked_quantity）
      - 解锁剩余锁定（locked_quantity = 0）
      - 触发 OrderPaid 事件
      - 返回成功响应

  - scenario: "支付失败"
    given: "支付被用户取消或失败"
    when: "收到失败通知"
    then: |
      - 订单状态保持 pending
      - 不扣减库存
      - 提示用户重新支付

business_rules:
  - "支付金额必须与订单金额一致"
  - "回调必须验证签名"
  - "回调需要幂等处理"
  - "支付超时时间30分钟"

technical_notes:
  - "使用 overtrue/laravel-wechat 或 similar 包"
  - "回调接口不需认证但需验证签名"
  - "使用 idempotency key 防止重复处理"

prompt_fragments:
  - "@TradeEngineer"
  - "@dependency-injection"
  - "@error-handling"
  - "@event-driven"
```

---

### US-EC-006: 查看订单列表

```yaml
story_id: "US-EC-006"
title: "查看订单列表"
priority: "P0"
actor: "登录用户"
module: "ecommerce"
tags: ["#订单", "#列表", "#状态筛选"]

user_story: |
  作为一名 登录用户，
  我想要 查看我的订单列表，
  以便 我可以跟踪订单状态。

acceptance_criteria:
  - scenario: "查看全部订单"
    given: "用户进入订单列表页"
    when: "页面加载"
    then: |
      - 显示订单列表（分页）
      - 每个订单显示：订单号、商品缩略图、总金额、状态
      - 按创建时间倒序排列

  - scenario: "按状态筛选"
    given: "用户点击状态Tab"
    when: "筛选完成"
    then: |
      - 显示对应状态的订单
      - Tab显示各状态的数量徽标

  - scenario: "订单状态Tab"
    given: "页面加载完成"
    when: "显示Tab栏"
    then: |
      - 全部: 显示所有订单
      - 待支付: 显示 pending 状态订单
      - 待发货: 显示 paid 状态订单
      - 待收货: 显示 shipped 状态订单
      - 已完成: 显示 completed 状态订单

business_rules:
  - "只显示当前用户的订单"
  - "软删除的订单不显示"
  - "订单状态Tab数量实时更新"

technical_notes:
  - "使用 where('user_id', auth()->id())"
  - "状态筛选使用 where('status', $status)"
  - "使用 withCount 统计各状态数量"

prompt_fragments:
  - "@FilamentUIDesigner"
  - "@type-safety-immutability"
```

---

### US-EC-007: 确认收货

```yaml
story_id: "US-EC-007"
title: "确认收货"
priority: "P1"
actor: "登录用户"
module: "ecommerce"
tags: ["#订单", "#收货", "#完成"]

user_story: |
  作为一名 登录用户，
  我想要 确认收货，
  以便 完成订单流程。

acceptance_criteria:
  - scenario: "正常确认收货"
    given: "订单状态为 shipped"
    when: "用户点击确认收货"
    then: |
      - 弹出确认对话框
      - 确认后更新订单状态为 completed
      - 记录完成时间 completed_at
      - 触发 OrderCompleted 事件

  - scenario: "自动确认收货"
    given: "订单发货超过15天未确认"
    when: "定时任务执行"
    then: |
      - 自动更新状态为 completed
      - 记录完成时间
      - 触发 OrderCompleted 事件

business_rules:
  - "只有 shipped 状态的订单可确认收货"
  - "自动收货时间可配置（默认15天）"
  - "确认收货后触发分销佣金计算"

prompt_fragments:
  - "@TradeEngineer"
  - "@event-driven"
```

---

### US-EC-008: 申请退款

```yaml
story_id: "US-EC-008"
title: "申请退款"
priority: "P1"
actor: "登录用户"
module: "ecommerce"
tags: ["#订单", "#退款", "#售后"]

user_story: |
  作为一名 登录用户，
  我想要 申请退款，
  以便 处理订单售后问题。

acceptance_criteria:
  - scenario: "待支付订单取消"
    given: "订单状态为 pending"
    when: "用户点击取消订单"
    then: |
      - 更新订单状态为 cancelled
      - 记录取消时间 cancelled_at
      - 释放锁定库存

  - scenario: "已支付订单退款"
    given: "订单状态为 paid 或 completed"
    when: "用户申请退款"
    then: |
      - 创建退款申请记录
      - 更新订单状态为 refunded
      - 恢复库存（quantity += locked_quantity）
      - 触发 OrderRefunded 事件
      - 通知财务模块处理退款

business_rules:
  - "已发货订单需联系客服处理"
  - "退款金额 = 实付金额 - 已使用优惠"
  - "退款后需要恢复对应佣金状态"

prompt_fragments:
  - "@TradeEngineer"
  - "@event-driven"
  - "@error-handling"
```

---

## 📊 验收标准汇总

| 故事ID | 标题 | 优先级 | 验收场景数 | 提示词碎片数 |
|--------|------|--------|-----------|-------------|
| US-EC-001 | 浏览商品列表 | P0 | 3 | 3 |
| US-EC-002 | 查看商品详情 | P0 | 3 | 2 |
| US-EC-003 | 添加购物车 | P0 | 4 | 3 |
| US-EC-004 | 提交订单 | P0 | 3 | 6 |
| US-EC-005 | 支付订单 | P0 | 4 | 4 |
| US-EC-006 | 查看订单列表 | P0 | 3 | 2 |
| US-EC-007 | 确认收货 | P1 | 2 | 2 |
| US-EC-008 | 申请退款 | P1 | 2 | 3 |

---

**版本**: v1.0 | **更新日期**: 2026-04-24
