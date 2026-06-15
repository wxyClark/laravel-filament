# 提示词卡片系统设计方案

> 为 Laravel-Filament 项目构建的提示词卡片系统，通过模块化卡片组装实现结构化、可复用的 AI 提示词生成。

---

## 一、卡片分类体系

### 1.1 卡片层级架构

```
卡片系统
├── L0 - 基础卡片（底层元卡片，被所有其他卡片引用）
├── L1 - 资源卡片（Filament 资源类专用）
├── L2 - 业务卡片（按业务域分类的领域提示词）
└── L3 - 组合卡片（多卡片组装的完整工作流提示词）
```

### 1.2 L0 基础卡片

| 卡片 ID | 卡片名称 | 文件路径 | 内容 |
|---------|---------|---------|------|
| `base-naming` | 命名规范 | `prompts/cards/base/base-naming.md` | 类/方法/文件/表/字段命名规则 |
| `base-structure` | 文件结构 | `prompts/cards/base/base-structure.md` | 标准 DDD 目录结构 |
| `base-config` | 基础配置 | `prompts/cards/base/base-config.md` | Filament Resource 基础配置模板 |
| `base-permission` | 权限控制 | `prompts/cards/base/base-permission.md` | Policy/Permission 配置 |
| `base-form` | 表单通用 | `prompts/cards/base/base-form.md` | 表单字段通用配置规则 |
| `base-table` | 表格通用 | `prompts/cards/base/base-table.md` | 表格列通用配置规则 |
| `base-domain` | 领域层规范 | `prompts/cards/base/base-domain.md` | DTO/Service/Event/Enum 规范 |
| `base-validation` | 验证规范 | `prompts/cards/base/base-validation.md` | FormRequest/Rule 验证规则 |

### 1.3 L1 资源卡片

| 卡片 ID | 资源类型 | 文件路径 | 内容 |
|---------|---------|---------|------|
| `res-resource` | Resource 类 | `prompts/cards/res/res-resource.md` | Resource 类完整模板 |
| `res-list-page` | 列表页 | `prompts/cards/res/res-list-page.md` | List 页面模板 |
| `res-create-page` | 创建页 | `prompts/cards/res/res-create-page.md` | Create 页面模板 |
| `res-edit-page` | 编辑页 | `prompts/cards/res/res-edit-page.md` | Edit 页面模板 |
| `res-view-page` | 详情页 | `prompts/cards/res/res-view-page.md` | View 页面模板 |
| `res-form` | 表单组件 | `prompts/cards/res/res-form.md` | Form 组件模板 |
| `res-table` | 表格组件 | `prompts/cards/res/res-table.md` | Table 组件模板 |
| `res-relation` | 关系管理器 | `prompts/cards/res/res-relation.md` | RelationManager 模板 |

### 1.4 L2 业务卡片

| 卡片 ID | 业务域 | 文件路径 | 内容 |
|---------|--------|---------|------|
| `biz-user` | 用户域 | `prompts/cards/biz/biz-user.md` | 用户/角色/权限领域 |
| `biz-product` | 商品域 | `prompts/cards/biz/biz-product.md` | SPU/SKU/分类/属性 |
| `biz-trade` | 交易域 | `prompts/cards/biz/biz-trade.md` | 订单/购物车/支付 |
| `biz-o2o` | O2O域 | `prompts/cards/biz/biz-o2o.md` | 预约/门店/服务 |
| `biz-distribution` | 分销域 | `prompts/cards/biz/biz-distribution.md` | 分销商/佣金/关系 |
| `biz-crm` | CRM域 | `prompts/cards/biz/biz-crm.md` | 客户/商机/跟进 |
| `biz-inventory` | 库存域 | `prompts/cards/biz/biz-inventory.md` | 库存/采购/盘点 |
| `biz-finance` | 财务域 | `prompts/cards/biz/biz-finance.md` | 账户/账单/对账 |

### 1.5 L3 组合卡片

| 卡片 ID | 组合名称 | 引用卡片 | 用途 |
|---------|---------|---------|------|
| `combo-crud` | 完整 CRUD | `res-resource` + `res-list-page` + `res-create-page` + `res-edit-page` + `res-form` + `res-table` | 生成完整 CRUD |
| `combo-read-only` | 只读列表 | `res-resource` + `res-list-page` + `res-table` | 生成只读列表 |
| `combo-form-only` | 表单页 | `res-resource` + `res-form` | 生成表单页 |
| `combo-with-relations` | 带关系管理 | `res-resource` + `res-relation` + 对应 biz 卡片 | 生成带关联管理 |

---

## 二、卡片组装规则

### 2.1 卡片依赖关系

```
基础卡片 (L0)
  ├── base-naming          ← 无依赖（最底层）
  ├── base-structure       ← 无依赖
  ├── base-config          ← 依赖: base-naming, base-structure
  ├── base-permission      ← 依赖: base-naming
  ├── base-form            ← 依赖: base-naming
  ├── base-table           ← 依赖: base-naming
  ├── base-domain          ← 依赖: base-naming, base-structure
  └── base-validation      ← 依赖: base-naming

资源卡片 (L1)
  ├── res-resource         ← 依赖: base-config, base-permission, base-form, base-table
  ├── res-list-page        ← 依赖: base-config
  ├── res-create-page      ← 依赖: base-config, base-form
  ├── res-edit-page        ← 依赖: base-config, base-form
  ├── res-view-page        ← 依赖: base-config, base-form
  ├── res-form             ← 依赖: base-form
  ├── res-table            ← 依赖: base-table
  └── res-relation         ← 依赖: base-config

业务卡片 (L2)
  ├── biz-user             ← 依赖: base-domain
  ├── biz-product          ← 依赖: base-domain
  ├── biz-trade            ← 依赖: base-domain
  └── ...

组合卡片 (L3)
  ├── combo-crud           ← 依赖: res-resource, res-list-page, res-create-page, res-edit-page, res-form, res-table
  ├── combo-read-only      ← 依赖: res-resource, res-list-page, res-table
  ├── combo-form-only      ← 依赖: res-resource, res-form
  └── combo-with-relations ← 依赖: res-resource, res-relation
```

### 2.2 卡片模板语法

**2.2.1 变量替换规则**

```markdown
# 卡片模板中使用的变量

| 变量语法 | 说明 | 示例 |
|---------|------|------|
| `{{entity}}` | 实体名（PascalCase） | `Order`, `Product` |
| `{{entity_snake}}` | 实体名（snake_case） | `order`, `product` |
| `{{entity_plural}}` | 实体名复数 | `orders`, `products` |
| `{{domain}}` | 业务域 | `Trade`, `Product` |
| `{{resource_path}}` | 资源完整路径 | `App\Infrastructure\Filament\Resources\OrderResource` |
| `{{table_name}}` | 表名 | `orders` |
| `{{primary_key}}` | 主键字段 | `id` |
| `{{model_class}}` | 模型类名 | `Order` |
| `{{model_path}}` | 模型完整路径 | `App\Domains\Order\Models\Order` |
| `{{fields}}` | 字段定义（从业务卡片注入） | 见下方 |
```

**2.2.2 字段定义语法**

```markdown
# fields 变量的格式

fields = [
    { name: "order_number", type: "text", required: true, unique: true, label: "订单号", maxLength: 50 },
    { name: "customer_id", type: "relationship", relationship: "customer", label: "客户" },
    { name: "total_amount", type: "money", currency: "CNY", required: true },
    { name: "status", type: "select", enum: "OrderStatus", required: true },
    { name: "notes", type: "textarea", maxLength: 2000, columnSpan: "full" },
]
```

### 2.3 卡片组装示例

**场景：生成订单 CRUD**

```yaml
# 组装配置
card_sequence:
  - card: base-naming
    priority: 1
  - card: base-structure
    priority: 1
  - card: base-domain
    priority: 2
  - card: base-form
    priority: 2
  - card: base-table
    priority: 2
  - card: base-permission
    priority: 2
  - card: biz-trade
    priority: 3
    vars:
      entity: Order
      domain: Trade
  - card: combo-crud
    priority: 4
    vars:
      entity: Order
      entity_plural: Orders
      entity_snake: order
      domain: Trade
      resource_path: App\Infrastructure\Filament\Resources\OrderResource
      table_name: orders
      model_path: App\Domains\Order\Models\Order
      fields:
        - name: order_number
          type: text
          required: true
          unique: true
          label: 订单号
        - name: customer_id
          type: relationship
          relationship: customer
          label: 客户
        - name: total_amount
          type: money
          currency: CNY
          required: true
        - name: status
          type: select
          enum: OrderStatus
          required: true
        - name: paid_at
          type: datetime
          label: 支付时间
        - name: notes
          type: textarea
          maxLength: 2000
          columnSpan: full
```

---

## 三、卡片输出格式标准

### 3.1 输出结构

```
[生成结果]

📁 文件路径
├── 文件1.php (状态: 已生成)
├── 文件2.php (状态: 已生成)
└── 目录/
    └── 文件3.php (状态: 已生成)

---

📝 代码内容

### 文件1: [完整文件名]
```php
<?php
// 完整代码...
```

### 文件2: [完整文件名]
```php
<?php
// 完整代码...
```
```

### 3.2 状态标记

| 标记 | 含义 |
|------|------|
| `✅ 已生成` | 文件已成功生成 |
| `⚠️ 已存在` | 文件已存在，需要确认是否覆盖 |
| `❌ 生成失败` | 生成过程中出错 |
| `📋 待确认` | 需要用户确认的配置项 |

---

*本文档定义了提示词卡片系统的完整设计规范，所有卡片应遵循此规范进行创建和维护。*
