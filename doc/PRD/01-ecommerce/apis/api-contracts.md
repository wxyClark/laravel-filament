# 🔌 电商核心模块 - API 接口契约

> **L4: 需求碎片层级** | **RAG 友好格式** | **可直接组装到提示词**

---

## 📋 元数据

```yaml
module: "ecommerce"
document_type: "api_contracts"
version: "1.0"
api_count: 14
auth_required: 8
public_api: 6
```

---

## 📦 商品接口

### GET /api/v1/spus - 获取商品列表

```yaml
endpoint: "GET /api/v1/spus"
description: "获取商品列表（分页、筛选、排序）"
auth: false
rate_limit: "60/minute"

request:
  query_params:
    - name: page
      type: integer
      required: false
      default: 1
      description: "页码"
    - name: per_page
      type: integer
      required: false
      default: 15
      description: "每页数量"
    - name: category_id
      type: integer
      required: false
      description: "分类ID筛选"
    - name: brand_id
      type: integer
      required: false
      description: "品牌ID筛选"
    - name: keyword
      type: string
      required: false
      description: "搜索关键词（名称）"
    - name: sort
      type: string
      required: false
      default: "-created_at"
      description: "排序字段（-前缀表示降序）"
    - name: price_min
      type: decimal
      required: false
      description: "最低价格"
    - name: price_max
      type: decimal
      required: false
      description: "最高价格"

response:
  success:
    code: 200
    schema:
      data:
        type: array
        items:
          id: integer
          code: string
          name: string
          image: string
          price_min: decimal
          price_max: decimal
          sales: integer
          status: string
      meta:
        current_page: integer
        per_page: integer
        total: integer
        last_page: integer
  errors:
    - code: 422
      description: "参数验证失败"

prompt_fragment: |
  # API: 获取商品列表
  @TradeEngineer @FilamentUIDesigner
  
  创建 GET /api/v1/spus 接口，支持分页、筛选、排序。
  返回商品列表，包含最低/最高价格区间。
```

---

### GET /api/v1/spus/{id} - 获取商品详情

```yaml
endpoint: "GET /api/v1/spus/{id}"
description: "获取商品详情"
auth: false

request:
  path_params:
    - name: id
      type: integer
      required: true
      description: "SPU ID"

response:
  success:
    code: 200
    schema:
      data:
        id: integer
        code: string
        name: string
        description: string
        images: array
        category:
          id: integer
          name: string
        brand:
          id: integer
          name: string
        skus:
          - id: integer
            code: string
            specs: object
            price: decimal
            stock: integer
            status: string
  errors:
    - code: 404
      description: "商品不存在"

side_effects:
  - "view_count 自增"

prompt_fragment: |
  # API: 获取商品详情
  @ProductArchitect
  
  创建 GET /api/v1/spus/{id} 接口，返回商品详情和SKU列表。
```

---

### GET /api/v1/categories - 获取分类树

```yaml
endpoint: "GET /api/v1/categories"
description: "获取商品分类树"
auth: false

response:
  success:
    code: 200
    schema:
      data:
        - id: integer
          name: string
          parent_id: integer|null
          children: array
          spu_count: integer

prompt_fragment: |
  # API: 获取分类树
  @ProductArchitect
  
  创建分类树接口，支持多级分类。
```

---

## 🛒 购物车接口

### GET /api/v1/cart - 获取购物车列表

```yaml
endpoint: "GET /api/v1/cart"
description: "获取当前用户的购物车列表"
auth: true

response:
  success:
    code: 200
    schema:
      data:
        - id: integer
          sku:
            id: integer
            code: string
            specs: object
            price: decimal
            image: string
            available_stock: integer
          quantity: integer
          selected: boolean
          total_price: decimal
      summary:
        total_count: integer
        selected_count: integer
        selected_amount: decimal

prompt_fragment: |
  # API: 获取购物车
  @TradeEngineer
  
  获取用户购物车，返回商品详情和汇总金额。
```

---

### POST /api/v1/cart - 添加商品到购物车

```yaml
endpoint: "POST /api/v1/cart"
description: "添加商品到购物车"
auth: true

request:
  body:
    - name: sku_id
      type: integer
      required: true
      rules: ["exists:skus,id"]
      description: "SKU ID"
    - name: quantity
      type: integer
      required: true
      rules: ["integer", "min:1"]
      description: "数量"

response:
  success:
    code: 201
    schema:
      message: "添加成功"
      data:
        id: integer
        sku_id: integer
        quantity: integer
      cart_summary:
        total_count: integer
  errors:
    - code: 422
      description: "验证失败或库存不足"

prompt_fragment: |
  # API: 添加购物车
  @TradeEngineer
  
  创建添加购物车接口，需校验库存。
```

---

### PUT /api/v1/cart/{id} - 更新购物车数量

```yaml
endpoint: "PUT /api/v1/cart/{id}"
description: "更新购物车商品数量"
auth: true

request:
  path_params:
    - name: id
      type: integer
      required: true
  body:
    - name: quantity
      type: integer
      required: true
      rules: ["integer", "min:1", "max:99"]

response:
  success:
    code: 200
    schema:
      message: "更新成功"
      data:
        id: integer
        quantity: integer
        total_price: decimal

prompt_fragment: |
  # API: 更新购物车
  @TradeEngineer
  
  更新购物车数量，校验库存。
```

---

### DELETE /api/v1/cart/{id} - 删除购物车商品

```yaml
endpoint: "DELETE /api/v1/cart/{id}"
description: "从购物车删除商品"
auth: true

response:
  success:
    code: 200
    schema:
      message: "删除成功"

prompt_fragment: |
  # API: 删除购物车
  @TradeEngineer
  
  删除购物车商品。
```

---

## 📦 订单接口

### POST /api/v1/orders - 创建订单

```yaml
endpoint: "POST /api/v1/orders"
description: "从购物车创建订单"
auth: true

request:
  body:
    - name: cart_ids
      type: array
      required: true
      items: integer
      description: "购物车ID列表"
    - name: shipping_info
      type: object
      required: true
      description: "收货信息"
      properties:
        name: { type: string, required: true }
        phone: { type: string, required: true }
        province: { type: string, required: true }
        city: { type: string, required: true }
        district: { type: string, required: true }
        address: { type: string, required: true }
    - name: remark
      type: string
      required: false
      description: "订单备注"

response:
  success:
    code: 201
    schema:
      data:
        order_sn: string
        status: string
        total_amount: decimal
        pay_amount: decimal
        pay_params: object
  errors:
    - code: 422
      description: "库存不足或参数错误"

prompt_fragment: |
  # API: 创建订单
  @TradeEngineer @AssetManager
  
  创建订单接口，包含库存锁定、金额计算、支付参数生成。
```

---

### GET /api/v1/orders - 获取订单列表

```yaml
endpoint: "GET /api/v1/orders"
description: "获取当前用户的订单列表"
auth: true

request:
  query_params:
    - name: page
      type: integer
      required: false
      default: 1
    - name: per_page
      type: integer
      required: false
      default: 10
    - name: status
      type: string
      required: false
      description: "订单状态筛选"

response:
  success:
    code: 200
    schema:
      data:
        - order_sn: string
          status: string
          status_text: string
          total_amount: decimal
          pay_amount: decimal
          items:
            - image: string
              name: string
              specs: string
              quantity: integer
          created_at: datetime
      meta:
        current_page: integer
        total: integer
      status_counts:
        pending: integer
        paid: integer
        shipped: integer
        completed: integer

prompt_fragment: |
  # API: 订单列表
  @FilamentUIDesigner
  
  获取订单列表，支持状态筛选和分页。
```

---

### POST /api/v1/orders/{id}/cancel - 取消订单

```yaml
endpoint: "POST /api/v1/orders/{id}/cancel"
description: "取消订单"
auth: true

response:
  success:
    code: 200
    schema:
      message: "取消成功"
  errors:
    - code: 422
      description: "订单状态不允许取消"
    - code: 403
      description: "无权操作此订单"

prompt_fragment: |
  # API: 取消订单
  @TradeEngineer
  
  取消订单，释放锁定库存。
```

---

### POST /api/v1/orders/{id}/confirm - 确认收货

```yaml
endpoint: "POST /api/v1/orders/{id}/confirm"
description: "确认收货"
auth: true

response:
  success:
    code: 200
    schema:
      message: "确认成功"
  errors:
    - code: 422
      description: "订单状态不允许确认收货"

prompt_fragment: |
  # API: 确认收货
  @TradeEngineer
  
  确认收货，触发 OrderCompleted 事件。
```

---

## 💳 支付接口

### POST /api/v1/payments/wechat - 微信支付

```yaml
endpoint: "POST /api/v1/payments/wechat"
description: "发起微信支付"
auth: true

request:
  body:
    - name: order_sn
      type: string
      required: true
      description: "订单号"

response:
  success:
    code: 200
    schema:
      app_id: string
      time_stamp: string
      nonce_str: string
      package: string
      sign_type: string
      pay_sign: string

prompt_fragment: |
  # API: 微信支付
  @TradeEngineer
  
  创建微信支付接口，返回支付参数。
```

---

### POST /api/v1/payments/alipay - 支付宝支付

```yaml
endpoint: "POST /api/v1/payments/alipay"
description: "发起支付宝支付"
auth: true

request:
  body:
    - name: order_sn
      type: string
      required: true

response:
  success:
    code: 200
    schema:
      form_html: string
      description: "支付宝支付表单HTML"

prompt_fragment: |
  # API: 支付宝支付
  @TradeEngineer
  
  创建支付宝支付接口。
```

---

### POST /api/v1/payments/callback/wechat - 微信支付回调

```yaml
endpoint: "POST /api/v1/payments/callback/wechat"
description: "微信支付回调通知"
auth: false
idempotent: true

request:
  body:
    description: "微信支付回调数据（加密）"

response:
  success:
    code: 200
    schema:
      return_code: "SUCCESS"

prompt_fragment: |
  # API: 微信支付回调
  @TradeEngineer
  
  处理微信支付回调，需验证签名并幂等处理。
```

---

## 📊 接口汇总

| 分类 | 接口数 | 需认证 | 说明 |
|------|--------|--------|------|
| 商品 | 3 | 0 | SPU列表、详情、分类 |
| 购物车 | 4 | 4 | CRUD操作 |
| 订单 | 4 | 4 | 创建、列表、取消、确认 |
| 支付 | 3 | 2 | 微信、支付宝、回调 |
| **合计** | **14** | **8** | - |

---

## 🔧 API 生成提示词模板

```markdown
# 任务：生成电商核心 API 接口

## 角色
@TradeEngineer @FilamentUIDesigner

## 上下文
- 认证: Laravel Sanctum
- 路由前缀: /api/v1
- 响应格式: JSON

## 任务
请为以下接口创建 Controller 和 FormRequest：

1. 商品相关
   - GET /api/v1/spus
   - GET /api/v1/spus/{id}
   - GET /api/v1/categories

2. 购物车相关
   - GET /api/v1/cart
   - POST /api/v1/cart
   - PUT /api/v1/cart/{id}
   - DELETE /api/v1/cart/{id}

3. 订单相关
   - POST /api/v1/orders
   - GET /api/v1/orders
   - POST /api/v1/orders/{id}/cancel
   - POST /api/v1/orders/{id}/confirm

4. 支付相关
   - POST /api/v1/payments/wechat
   - POST /api/v1/payments/alipay
   - POST /api/v1/payments/callback/wechat

## 输出要求
- 使用 FormRequest 进行参数验证
- 使用 API Resource 格式化响应
- 所有方法必须有类型声明
- 敏感操作需要权限检查
```

---

**版本**: v1.0 | **更新日期**: 2026-04-24
