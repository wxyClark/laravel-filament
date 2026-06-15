# 商品域业务卡片

> **卡片 ID**: `biz-product`
> **优先级**: L2
> **依赖**: `base-domain`

---

## 商品域核心实体

### SPU (Standard Product Unit)
- 表名: `products`
- 主字段: `sku`, `name`, `description`, `status`, `category_id`
- 枚举: `ProductStatus` (enabled/disabled/archived)
- 类型: `ProductType` (simple/configurable/downloadable/virtual)

### SKU (Stock Keeping Unit)
- 表名: `product_variants`
- 主字段: `sku`, `price`, `stock`, `weight`, `spu_id`
- 属性: 颜色、尺寸、型号等

### 商品分类
- 表名: `categories`
- 结构: 支持无限级树形结构（closure table 或 materialized path）

### 商品属性
- 表名: `attributes`, `attribute_options`, `product_attribute_values`
- 类型: 文本、数字、选择、颜色、图片

## 核心 Service

| Service | 职责 |
|---------|------|
| `ProductService` | 商品 CRUD |
| `ProductVariantService` | SKU 管理 |
| `CategoryService` | 分类管理 |
| `AttributeService` | 属性管理 |

## Filament 资源

| Resource | 页面 |
|----------|------|
| `CategoryResource` | ListCategories, CreateCategory, EditCategory |
| `ProductResource` | ListProducts, CreateProduct, EditProduct, ViewProduct |
| `BrandResource` | ListBrands, CreateBrand, EditBrand |
| `AttributeResource` | ListAttributes, CreateAttribute, EditAttribute |

## 关键字段

```yaml
fields:
  - name: sku
    type: text
    required: true
    unique: true
    label: SKU
  - name: name
    type: text
    required: true
    label: 商品名称
  - name: description
    type: richeditor
    label: 描述
    columnSpan: full
  - name: category_id
    type: relationship
    relationship: category
    label: 分类
    required: true
  - name: brand_id
    type: relationship
    relationship: brand
    label: 品牌
  - name: status
    type: select
    enum: ProductStatus
    required: true
  - name: price
    type: money
    currency: CNY
    required: true
  - name: stock
    type: integer
    required: true
    default: 0
```
